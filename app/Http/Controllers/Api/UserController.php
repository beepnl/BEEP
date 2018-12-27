<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Auth\Events\Login as ApiTokenLogin;
use Illuminate\Validation\Rule;
use App\User;
use App\ChecklistFactory;
use Response;
use Validator;
use Hash;
use Auth;
use Password;
use Login;

class UserController extends Controller
{

    public function authenticate(Request $request) 
    {
        if ($request->user())
        {
            event( new ApiTokenLogin('api', $request->user(), false) );
            return $this->returnToken($request);
        }
        return $this->notAuthenticated($request);
    }

    public function login(Request $request) 
    {
        $credentials = array
        (
            'email' => $request->get('email'),
            'password' => $request->get('password')
        );

        if(Auth::attempt($credentials))
        {
            if ($request->user()->hasVerifiedEmail())
            {
                return $this->returnToken($request);
            }
            else
            {
                return $this->notVerified($request);
            }
        }
        else
        {
            return $this->notAuthenticated($request);
        }
    }

    public function notVerified(Request $request) 
    {
        return Response::json('email_not_verified', 400);
    }

    public function notAuthenticated(Request $request) 
    {
        return Response::json(["message" => "invalid_user"], 400);
    }
    
    private function returnToken($request)
    {
        return Response::json($request->user());
    }


    public function register(Request $request)
    {

        $validator = Validator::make
        (
            $request->all(),
            array
            (
                'email'         => 'bail|required|email|unique:users',
                'password'      => 'required|min:8',
                'policy_accepted'=>'required'
            ),
            array
            (
                'required'      => ':attribute_is_required',
                'unique'        => ':attribute_already_exists',
                'email'         => 'no_valid_email',
                'min'           => 'invalid_password',
            )
        );


        // check if the data is validated
        if($validator->fails())
        {
            return Response::json(["message" => $validator->errors()->first()], 400);
        }
        else // save 'm 
        {
            $user_data = [
                'name'      => $request->input('email'),
                'password'  => Hash::make($request->input('password')),
                'email'     => $request->input('email'),
                'api_token' => str_random(60),
                'remember_token' => str_random(10),
                'policy_accepted'=> $request->input('policy_accepted')
            ];

            // save the user
            $user             = User::create($user_data);

            // add the standard checklist 
            $checklistFactory = new ChecklistFactory;
            $check            = $checklistFactory->getStandardChecklist();
            $checklistFactory->createUserChecklist($user, $check);

            // set the response data
            if($user) 
            {
                $user->sendApiEmailVerificationNotification();
                return Response::json(['email_verification_sent'], 400);
            } 
            else
            {
                return Response::json('could_not_create_user', 500);
            }
        }
    }


    /* Send reset link */
    // responses: INVALID_USER, RESET_LINK_SENT, INVALID_PASSWORD, INVALID_TOKEN, PASSWORD_RESET
    public function reminder(Request $request)
    {

        $remind = Password::sendResetLink(['email' => $request->input('email')]);

        switch($remind)
        {
            case Password::INVALID_USER:
                $code     = 400;
                $response = array('message' => 'invalid_user');
              break;

            case Password::RESET_LINK_SENT:
                $code = 200;
                $response = array('message' => 'reminder_sent');
              break;
        }

        // return the response
        return Response::json($response, $code);
    }


    /* RESET PASSWORD */
    public function reset(Request $request)
    {
        // get the input
        $email            = $request->input('email');
        $password         = $request->input('password');
        $password_confirm = $request->input('password_confirm');
        $token            = $request->input('token');

        $credentials = array
        (
            'email'                 => $email,
            'password'              => $password,
            'password_confirmation' => $password_confirm,
            'token'                 => $token,
        );

        // generate the reset
        $reset = Password::reset($credentials, function($user, $password)
        {
            $user->password = Hash::make($password);
            $user->save();
        });

        // get the response
        switch ($reset)
        {
            case Password::INVALID_PASSWORD:
                $code     = 400;
                $response = array('message' => 'invalid_password');
              break;

            case Password::INVALID_TOKEN:
                $code     = 400;
                $response = array('message' => 'invalid_token');
              break;

            case Password::INVALID_USER:
                $code     = 400;
                $response = array('message' => 'invalid_user');
              break;

            case Password::PASSWORD_RESET:
                $code     = 200;
                $user     = User::where('email', $email)->first();
                $response = array
                (
                    'data'       => array('api_token' => $user->api_token),
                    'offset'     => 0,
                    'count'      => 1,
                    'total'      => 1, 
                );
              break;
        }

        // return the response
        return Response::json($response, $code);
    }


    public function destroy(Request $request)
    {
        $del = $request->user()->delete();
        //$del = true;
        if ($del)
            return Response::json(['message' => 'user_deleted'], 200);

        return Response::json(['message' => 'user_not_deleted'], 400);
    }

    public function edit(Request $request)
    {
        $user = $request->user();
        $save = false;

        $validator = Validator::make
        (
            $request->all(),
            array
            (
                'email'         =>  [
                                        'bail',
                                        'required',
                                        'email',
                                        Rule::unique('users')->ignore($user->id),
                                    ],
                'password'      => 'nullable|min:8|confirmed',
            ),
            array
            (
                'required'      => ':attribute_is_required',
                'unique'        => ':attribute_already_exists',
                'email'         => 'no_valid_email',
                'min'           => 'invalid_password',
                'confirmed'     => 'no_password_match',
            )
        );

        if($validator->fails())
        {
            return Response::json(["message" => $validator->errors()->first()], 400);
        }
        else // save 'm 
        {
            if($request->filled('name'))
            {
                $user->name = $request->input('name');
                $save = true;
            }

            if($request->filled('email'))
            {
                $user->email = $request->input('email');
                $save = true;
            }

            if($request->filled('policy_accepted'))
            {
                $user->policy_accepted = $request->input('policy_accepted');
                $save = true;
            }

            if($request->filled('password') && $request->filled('password_confirmation') && $request->input('password') == $request->input('password_confirmation'))
            {
                $user->password = Hash::make($request->input('password'));
                $save = true;
            }

            if ($save)
            {
                $saved = $user->save();
                if ($saved)
                    return Response::json($user, 200);
            }
        }
        return Response::json(['message' => 'user_not_edited'], 400);
    }
   
}
