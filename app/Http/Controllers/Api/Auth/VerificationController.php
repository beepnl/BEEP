<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use App\User;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     *
     * @var string
     */
    protected $redirectTo = '/webapp#!/login?msg=email_verified';


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify');
    }


    /**
     * Show the email verification notice.
     *
     */
    public function show()
    {
        //
    }

    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request)
    {
        $user = null;

        // ->route('id') gets route user id and getKey() gets current user id() 
        // do not forget that you must send Authorization header to get the user from the request
        if ($request->user() && $request->route('id') == $request->user()->getKey())
        {
            $user = $request->user();
        }
        else
        {
            $user = User::find($request->route('id'));
        }

        $email = '';

        if ($user && $user->markEmailAsVerified()) 
        {
            $email = '&email='.$user->email;
            event(new Verified($user));
        }

        return redirect($this->redirectPath().$email);
    }

    /**
     * Resend the email verification notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resend(Request $request)
    {
        $user = null;
        //die(print_r($request->input()));
        if ($request->filled(['email']))
        {
            $email = $request->input('email');
            $user  = User::where('email', '=', $email)->first();
        }
        else 
        {
            $user = $request->user();
        }

        if($user === null)
            return response()->json('invalid_user', 400);

        //die(print_r($user));

        if ($user->hasVerifiedEmail()) {
            return response()->json('email_verified', 422);
//            return redirect($this->redirectPath());
        }

        $user->sendApiEmailVerificationNotification();

        return response()->json('email_verification_sent');
//        return back()->with('resent', true);
    }

}