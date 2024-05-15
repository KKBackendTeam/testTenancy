<?php

namespace App\Traits;

use App\Models\MailServer;

trait RunTimeEmailConfigrationTrait
{
    public function runTimeEmailConfiguration($agencyId)
    {
        $mail = MailServer::where('agency_id', $agencyId)->firstOrFail();
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
        }
        return true;
    }
}
