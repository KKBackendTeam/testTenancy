<?php

namespace App\Http\Middleware;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Mail\TransportManager;
use Tymon\JWTAuth\Facades\JWTAuth;
use Closure;
use Mail;
use Config;
use App;
use App\Models\MailServer;

class MailServerConfigration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    public function handle($request, Closure $next)
    {
        $mail = MailServer::where('agency_id', JWTAuth::parseToken()->authenticate()->agency_id)->first();
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

            return $next($request);
        }
        return response()->json(['saved' => false, 'statusCode' => 781, 'reason' => 'Mail Server settings are not done yet!']);
    }
}
