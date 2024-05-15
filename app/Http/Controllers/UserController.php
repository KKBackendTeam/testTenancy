<?php

namespace App\Http\Controllers;

use App\Models\MailServer;
use Illuminate\Support\Facades\Mail;
use App\Notifications\SuperAdmin\NewAgencyRegisterNotification;
use App\Models\User;
use App\Models\Applicantbasic;
use App\Models\Agency;
use Carbon\Carbon;
use App\Mail\AgencyEmail;
use App\Mail\OTPVerificationMail;
use App\Mail\PasswordReset;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\Basic\LoginRequest;
use App\Http\Requests\Basic\RegisterRequest;
use App\Traits\AllPermissions;
use Illuminate\Support\Str;
use App\Http\Requests\Basic\DefaultPasswordRequest;
use App\Traits\RunTimeEmailConfigrationTrait, App\Traits\TextForSpecificAreaTrait;
use App\Http\Requests\Basic\ForgotPasswordRequest;
use App\Http\Requests\Basic\ResetPasswordRequest;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    use AllPermissions, RunTimeEmailConfigrationTrait, TextForSpecificAreaTrait;

    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Create a new agency and associated user.
     *
     * @param RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createNewAgency(RegisterRequest $request)
    {
        $agency = $request->all();

        $agency['agency_confirm_link'] = config('global.frontSiteUrl') . ("/agency_link/" . Str::random(20));
        $agency['last_login'] = now()->toDateTimeString();
        $agency['total_credit'] = 10;
        $agencyData = Agency::create($agency);

        $user = $request->all();
        $user['password'] = bcrypt($request->get('password'));
        $user['agency_id'] = $agencyData['id'];
        $user['roleStatus'] = 1;
        User::create($user);

        $superAdmin = Agency::where('status', 2)->firstOrFail();
        $this->runTimeEmailConfiguration($superAdmin->id);

        $data = $this->emailTemplateData('SA_ARE', null, null, $agencyData, null, null, null, null, null, $superAdmin, null);
        Mail::to($request->get('email'))->send(new AgencyEmail($data, $superAdmin, $agencyData));

        $superAdminUser = User::where('roleStatus', 2)->firstOrFail();
        $superAdminUser->notify(new NewAgencyRegisterNotification($agencyData));

        return response()->json(['content' => 'done', 'status' => 200]);
    }

    /**
     * Handle user login with OTP.
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $token = null;

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['content' => 'invalid_email_or_password', 'status' => 422]);
            }

            $user = User::where('email', $credentials['email'])->first();
            if ($user->roleStatus == 2) {
                $token = JWTAuth::fromUser($user);
                $defaultPassword = 1;
                $user->agencies->update(['last_login' => now()->toDateTimeString()]);
                return response()->json(['content' => 'done', 'status' => 200, 'role' => $user->roleStatus, 'token' => $token, 'defaultPassword' => $defaultPassword]);
            }
            if ($user->roleStatus == 0 && $user->is_active == 0) {
                return response()->json(['content' => 'Deactivate staff member', 'status' => 453]);
            }

            if ($user->roleStatus < 2 && $user->agencies->status == 0) {
                ($user->roleStatus == 1) ? $status = 450 : $status = 451;   //450 ->agency unauthorized //451 ->staff unauthorized
                return response()->json(['content' => 'unauthorized', 'status' => $status]);
            }
            $otp = rand(100000, 999999);
            $user->update([
                'otp' => $otp,
                'otp_created_at' => now()
            ]);
            $mailCode = 'OTPE';
            $agencyData = Agency::where('id', $user->agency_id)->first();
            $data = $this->emailTemplateData($mailCode, null, null, $agencyData, $user, null, null, null, null, $agencyData, null);
            Mail::to($user->email)->send(new OTPVerificationMail($data, $agencyData, $user));
            return response()->json(['content' => 'otp_sent', 'status' => 200]);
        } catch (JWTAuthException $e) {
            return response()->json(['content' => 'failed_to_create_token', 'status' => 500]);
        }
    }

    /**
     * Verify OTP and log in user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyOTP(Request $request)
    {
        $otp = $request->input('otp');
        $email = $request->input('email');

        $user = User::where('email', $email)->first();

        if (!$user || $user->otp !== $otp) {
            return response()->json(['content' => 'invalid_otp', 'status' => 422]);
        }

        $otpCreatedAt = Carbon::parse($user->otp_created_at);
        if ($otpCreatedAt->diffInMinutes(now()) > 2) {
            $user->update(['otp' => null, 'otp_created_at' => null]);
            return response()->json(['content' => 'expired_otp', 'status' => 422]);
        }

        $user->update(['otp' => null, 'otp_created_at' => null]);

        $token = JWTAuth::fromUser($user);

        $defaultPassword = ($user->defaltPassword == 0 && $user->roleStatus == 0) ? 0 : 1;

        $user->agencies->update(['last_login' => now()->toDateTimeString()]);
        return response()->json(['content' => 'done', 'status' => 200, 'role' => $user->roleStatus, 'token' => $token, 'defaultPassword' => $defaultPassword]);
    }

    /**
     * Handle user logout.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $token = request()->token;
        $loginUser = JWTAuth::setToken($token)->toUser();
        $loginUser->update(['defaltPassword' => 1]);  //default password set
        JWTAuth::invalidate(request()->token);

        return response()->json(['content' => 'Logout done!.', 'status' => 200]);
    }

    /**
     * Handle user password change after first login.
     *
     * @param DefaultPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function defaultPasswordChange(DefaultPasswordRequest $request)
    {
        JWTAuth::toUser(request()->token)->update(['password' => bcrypt($request['password']), 'defaltPassword' => 1]);
        return response()->json(['saved' => true, 'content' => 'Password Changed successfully.', 'status' => 200]);
    }

    /**
     * Handle forgot password request.
     *
     * @param ForgotPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        if ($user = User::where('email', $request->get('email'))->firstOrFail()) {
            $password_link = config('global.frontSiteUrl') . ("/forgot_password/" . Str::random(12));
            $user->password_link = $password_link;
            $user->save();

            if ($user->roleStatus == 2) {
                $this->agencyMiddlewareHelper('admin_mailServer', 0);
                $mailCode = 'SA_PRE';
            } else {
                if (!$this->agencyMiddlewareHelper('agency_mailServer', $user->agency_id)) {
                    return response()->json(['saved' => false, 'statusCode' => '2324', 'reason' => 'Your mail server setting is not complete, please contact the agency administrator']);
                }
                $mailCode = 'PRE';
            }

            $agencyData = Agency::where('id', $user->agency_id)->first();
            $data = $this->emailTemplateData($mailCode, null, null, $agencyData, $user, null, null, null, null, $agencyData, null);
            Mail::to($user->email)->send(new PasswordReset($data, $agencyData, $user));

            return response()->json(['saved' => true]);
        } else {
            return response()->json(['saved' => false]);
        }
    }

    /**
     * Helper function to configure mail server settings for agency.
     *
     * @param string $settingFor
     * @param int $agency_id
     * @return bool
     */
    public function agencyMiddlewareHelper($settingFor, $agency_id)
    {
        if ($settingFor == 'admin_mailServer') {
            $mail = MailServer::where('agency_id', Agency::where('status', 2)->firstOrFail()->id)->first();
        } else {
            $mail = MailServer::where('agency_id', $agency_id)->first();
        }

        if ($mail) {
            $conf = array(
                'driver' => $mail->driver,
                'host' => $mail->host,
                'port' => $mail->port,
                'from' => array('address' => $mail->from_address, 'name' => $mail->from_name),
                'encryption' => $mail->encryption,
                'username' => $mail->username,
                'password' => $mail->password,
                'sendmail' => '/usr/sbin/sendmail -bs',
                'pretend' => false,
            );
            config()->set('mail', $conf);

            $app = app()->getInstance();
            $app->register('Illuminate\Mail\MailServiceProvider');
            return true;
        } else {
            return false;
        }
    }

    /**
     * Handle forgot password form submission.
     *
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPasswordForm($token)
    {
        if (User::where('password_link', config('global.frontSiteUrl') . ("/forgot_password/" . $token))->first()) {
            return response()->json(['saved' => true]);
        } else {
            return response()->json(['saved' => false]);
        }
    }

    /**
     * Reset user password after forgot password request.
     *
     * @param ResetPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetYourPassword(ResetPasswordRequest $request)
    {
        if ($user = User::where('password_link', config('global.frontSiteUrl') . ("/forgot_password/" . $request->get('code')))->first()) {
            $user->password = bcrypt($request->get('password'));
            $user->password_link = null;
            $user->save();

            return response()->json(['saved' => true]);
        } else {
            return response()->json(['saved' => false]);
        }
    }

    /**
     * Handle app login request.
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function appLogin(LoginRequest $request)
    {

        $credentials = $request->only('email', 'password');
        $token = null;

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['content' => 'invalid_email_or_password', 'status' => 422]);
            }

            $applicant = Applicantbasic::where('email', $credentials['email'])->first();
            $updatedAt = Carbon::parse($applicant->updated_at);
            $today = Carbon::now();
            $daysSinceUpdate = $updatedAt->diffInDays($today);
            if ($applicant->status == 9 && $daysSinceUpdate >= 90) {
                return response()->json(['content' => 'This account has been deactivated.', 'status' => 401]);
            }
            $otp = rand(100000, 999999);
            $applicant->update([
                'otp' => $otp,
                'otp_created_at' => now()
            ]);
            $mailCode = 'OTPE';
            $agencyData = Agency::where('id', $applicant->agency_id)->first();
            $data = $this->emailTemplateData($mailCode, null, null, $agencyData, $applicant, null, null, null, null, $agencyData, null);
            Mail::to($applicant->email)->send(new OTPVerificationMail($data, $agencyData, $applicant));

            return response()->json(['content' => 'otp_sent', 'status' => 200]);
        } catch (JWTException $e) {
            return response()->json(['content' => 'failed_to_create_token', 'status' => 500]);
        }
    }

    /**
     * Verify OTP for app login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function appVerifyOTP(Request $request)
    {
        try {
            $applicant = Applicantbasic::where('email', $request->email)->first();

            if (!$applicant || $applicant->otp !== $request->otp) {
                return response()->json(['content' => 'invalid_otp', 'status' => 422]);
            }

            $otpCreatedAt = Carbon::parse($applicant->otp_created_at);
            if ($otpCreatedAt->diffInMinutes(now()) > 2) {
                $applicant->update(['otp' => null, 'otp_created_at' => null]);
                return response()->json(['content' => 'expired_otp', 'status' => 422]);
            }
            $applicant->update(['otp' => null, 'otp_created_at' => null]);

            $token = JWTAuth::fromUser($applicant);

            return response()->json(['content' => 'done', 'status' => 200, 'user' => $applicant, 'token' => $token]);
        } catch (\Exception $e) {
            return response()->json(['content' => 'failed_to_verify_otp', 'status' => 500]);
        }
    }
}
