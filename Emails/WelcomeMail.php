<?php

namespace Modules\LaravelUser\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\User;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $modules;
    public $password;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $modules, $password)
    {
        $this->user = $user;
        $this->modules = $modules;
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
            ->subject('Welcome to eBusiness')
            ->view('user::email.welcome-mail')
            ->with('user', $this->user, $this->modules, $this->password);
    }
}
