<?php

namespace App\Providers;

use App\Mail\Transport\AllyTransport;
use Illuminate\Mail\MailServiceProvider;

class AllyMailProvider extends MailServiceProvider
{
    /**
     * Register the Swift Mailer instance.
     *
     * @return void
     */
    function registerSwiftMailer()
    {
        if (config('mail.driver') == 'ally') {
            $this->registerAllyMailer();
        } else {
            parent::registerSwiftMailer();
        }
    }

    /**
     * Create the custom Ally Mail Transporter instance.
     *
     * @return void
     */
    private function registerAllyMailer()
    {
        $this->app->singleton('swift.mailer', function ($app) {
            // AllyTransport inherits the Swift_SendmailTransport so you can set it up
            // the same way the SMTP driver is setup.
            // Reference: Illuminate/Mail/TransportManager@createSmtpDrive

            $config = config('mail');
            $transport = new AllyTransport($config['host'], $config['port']);

            if (isset($config['encryption'])) {
                $transport->setEncryption($config['encryption']);
            }

            if (isset($config['username'])) {
                $transport->setUsername($config['username']);
                $transport->setPassword($config['password']);
            }

            if (isset($config['stream'])) {
                $transport->setStreamOptions($config['stream']);
            }

            return new \Swift_Mailer($transport);
       });
    }
}
