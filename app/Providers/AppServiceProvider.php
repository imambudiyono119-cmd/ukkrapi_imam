<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::toMailUsing(function (object $notifiable, string $token): MailMessage {
            $email = $notifiable->getEmailForPasswordReset();
            $record = DB::table('password_reset_tokens')->where('email', $email)->first();
            $otp = $record?->otp ?? substr(preg_replace('/\D/', '', md5($token)), 0, 6);

            $resetUrl = route('password.reset', [
                'token' => $token,
                'email' => $email,
            ]);

            return (new MailMessage)
                ->subject('Kode OTP & Tautan Reset Password - RAPI-Sekolah')
                ->view('emails.otp-reset-password', [
                    'user' => $notifiable,
                    'otp' => $otp,
                    'resetUrl' => $resetUrl,
                ]);
        });
    }
}
