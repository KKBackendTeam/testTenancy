<?php

namespace App\Http\Middleware;

use App\Models\Agency;
use App\Models\MailServer;
use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;

class MailServerSuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        $mail = MailServer::where('agency_id', Agency::where('status', 2)->firstOrFail()->id)->firstOrFail();
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
        return response()->json(['saved' => false, 'reason' => 'Mail Server settings are not done yet!']);
    }
}
