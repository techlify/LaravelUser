<?php

namespace Modules\LaravelUser\Emails;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPassword extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $password;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $subject, $password)
    {
        $this->user = $user;
        $this->subject = $subject;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('no-reply@techlify.com', 'eBusiness.gy')
            ->subject($this->subject)
            ->view('user::email.forgot-password-mail')
            ->with('user', $this->user, $this->password);
    }
}
