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

/**
 * @group Api\UserController
 *
 * APIs for managing users
 */
class UserController extends Controller
{

    /**
    api/authenticate 
    Authorize a user and login with an api_token. Used for persistent login in webapp.
    Header parameter with Bearer [api_token] from the user object. Example: Bearer 1snu2aRRiwQNl2Tul567hLF0XpKuZO8hqkgXU4GvjzZ3f3pOCiDPFbBDea7W
    @authenticated
    @response {
        "id": 1317,
        "name": "test@test.com",
        "email": "test@test.com",
        "avatar": "default.jpg",
        "api_token": "1snu2aRRiwQNl2Tul567hLF0XpKuZO8hqkgXU4GvjzZ3f3pOCiDPFbBDea7W",
        "created_at": "2018-12-30 23:57:35",
        "updated_at": "2020-01-09 16:31:32",
        "last_login": "2020-01-09 16:31:32",
        "policy_accepted": "beep_terms_2018_05_25_avg_v1",
        "email_verified_at": "2018-05-25 00:00:00"
    }
    */
    public function authenticate(Request $request) 
    {
        if ($request->user())
        {
            event( new ApiTokenLogin('api', $request->user(), false) );
            return $this->returnToken($request);
        }
        return $this->notAuthenticated($request);
    }

    /**
    api/login
    Login via login form
    @bodyParam email string required Email address of the user. Example: test@test.com
    @bodyParam password string required Password of the user. Example: testtest
    @response {
        "id": 1317,
        "name": "test@test.com",
        "email": "test@test.com",
        "avatar": "default.jpg",
        "api_token": "1snu2aRRiwQNl2Tul567hLF0XpKuZO8hqkgXU4GvjzZ3f3pOCiDPFbBDea7W",
        "created_at": "2018-12-30 23:57:35",
        "updated_at": "2020-01-09 16:31:32",
        "last_login": "2020-01-09 16:31:32",
        "policy_accepted": "beep_terms_2018_05_25_avg_v1",
        "email_verified_at": "2018-05-25 00:00:00"
    }
    */
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

    /**
    api/register
    Registers a new user and sends an e-mail verification request on succesful save
    @bodyParam email string required Email address of the user. Example: test@test.com
    @bodyParam password string required Password of the user. Example: testtest
    @bodyParam policy_accepted string required Name of the privacy policy that has been accepted by the user by ticking the accept terms box. Example: beep_terms_2018_05_25_avg_v1
    */
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
                'policy_accepted'=> $request->input('policy_accepted'),
                'locale'    => $request->input('locale')
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
                return Response::json(['email_verification_sent'], 201);
            } 
            else
            {
                return Response::json('could_not_create_user', 500);
            }
        }
    }


    /**
    api/user/reminder
    Send password reset link
    responses: invalid_user, reminder_sent, invalid_password, invalid_token, password_reset
    @bodyParam email string required Email address of the user. Example: test@test.com
    @response{
        "message": "reminder_sent"
    }
    */
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


    /**
    api/user/reset
    Reset the user passowrd with a reset link
    responses: INVALID_USER, RESET_LINK_SENT, INVALID_PASSWORD, INVALID_TOKEN, PASSWORD_RESET
    @bodyParam email string required Email address of the user. Example: test@test.com
    @bodyParam password string required Password of the user. Example: testtest
    @bodyParam password_confirm string required Password confirmation of the user. Example: testtest
    @bodyParam token string required Token sent in the reminder e-mail to the email address of the user. Example: z8iQafmgP1
    */
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

    /**
    api/user DELETE
    Destroy the logged in user and all its data in the database
    @authenticated
    */
    public function destroy(Request $request)
    {
        $del = $request->user()->delete();
        //$del = true;
        if ($del)
            return Response::json(['message' => 'user_deleted'], 200);

        return Response::json(['message' => 'user_not_deleted'], 400);
    }

    /**
    api/user PATCH
    Edit the user details
    @authenticated
    @bodyParam email string required Email address of the user. Example: test@test.com
    @bodyParam password string required Password of the user. Example: testtest
    @bodyParam password_confirm string required Password confirmation of the user. Example: testtest
    @bodyParam policy_accepted string Name of the privacy policy that has been accepted by the user by ticking the accept terms box. Example: beep_terms_2018_05_25_avg_v1
    */
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

            if($request->filled('locale'))
            {
                $user->locale = $request->input('locale');
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
