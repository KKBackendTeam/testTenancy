<?php

namespace App\Http\Controllers\ApplicantAuth;

use App\Models\Applicant;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Auth;
use Illuminate\Http\Request;

class ApplicantLoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */

//    protected function authenticated(Request $request, $user)
//    {
//        if ($user->log_status > 0) {
//            return redirect('/applicant/dashboard');
//        }
//
//        return redirect('/applicant/privacy');
//
//    }

    protected $redirectTo = '/applicant/dashboard';

//    protected $guard = 'applicant';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:applicant')->except('logout');
    }

    protected function guard()
    {
        return Auth::guard('applicant');
    }

    public function showLoginForm()
    {
        return view('Applicant.applicant_login_form');
    }

    public function logout(Request $request)
    {

        $this->guard('applicant')->logout();

        $request->session()->invalidate();

        return redirect('/applicant/login');
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);
        $applicant = Applicant::where('email', $request->email)->first();

        if (!empty($applicant) && $applicant->log_status == 1) {

            if ($this->hasTooManyLoginAttempts($request)) {
                $this->fireLockoutEvent($request);

                return $this->sendLockoutResponse($request);
            }

            if ($this->attemptLogin($request)) {
                return $this->sendLoginResponse($request);
            }

            $this->incrementLoginAttempts($request);

            return $this->sendFailedLoginResponse($request);

        } else {
            return back()->with('noApplicant', 'These credentials do not match our records!');
        }
    }
}
