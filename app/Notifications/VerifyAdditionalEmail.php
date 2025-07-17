<?php

namespace App\Notifications;

use App\Models\Email;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyAdditionalEmail extends Notification
{

    /**
     * The email that needs to be verified.
     *
     * @var \App\Models\Email
     */
    protected $email;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\Email  $email
     * @return void
     */
    public function __construct(Email $email)
    {
        $this->email = $email;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify Email Address')
            ->line('Please click the button below to verify your email address.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('If you did not add this email address to your account, no further action is required.');
    }

    /**
     * Get the recipients of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function routeNotificationForMail($notifiable)
    {
        return [$this->email->address];
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'emails.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'email_id' => $this->email->id,
                'hash' => sha1($this->email->address),
            ]
        );
    }

    /**
     * Get the email that needs to be verified.
     *
     * @return \App\Models\Email
     */
    public function getEmail()
    {
        return $this->email;
    }
}
