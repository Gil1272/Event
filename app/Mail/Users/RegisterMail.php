<?php

namespace App\Mail\Users;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegisterMail extends Mailable
{
    use Queueable, SerializesModels;

    public $header;
    public $message;
    public $link;
    public $linkText;
    public $data;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->header = $data['header'];
        $this->message = $data['message'];
        $this->link = $data['link'];
        $this->linkText = $data['linkText'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view("mails.register.template")->from(env("MAIL_FROM_ADDRESS"),env("APP_NAME"));
    }
}
