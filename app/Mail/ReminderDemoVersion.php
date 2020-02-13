<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 20-2-2019
 * Time: 23:46
 */

namespace App\Mail;


use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;

class ReminderDemoVersion extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The user that needs to become activate.
     */
    protected $user;

    /**
     * @var string $subject
     */
    public $subject;

    /**
     * @var string $message
     */
    public $message;

    /**
     * @var string $buttonName
     */
    public $buttonName;

    public function __construct($user, $subject, $message, $buttonName)
    {
        $this->user = $user;
        $this->subject = $subject;
        $this->message = $message;
        $this->buttonName = $buttonName;
    }

    public function build()
    {
        $username = $this->user->firstName . ' ';
        $username .= $this->user->insertion ? $this->user->insertion . ' ' . $this->user->lastName : $this->user->lastName;

        return $this->view('emails.reminderDemoVersionView')
            ->with([
                'userName' => $username,
                'link' => 'https://bimex-plan.com/producten',
                'contentMessage' => $this->message,
                'button' => $this->getButton()
            ])
            ->subject($this->subject);
    }

    private function getButton(): string {
        return '<button style="padding: 15px 30px; border-radius: 5px; background-color: #f7c033; color: #000; font-size: 16px; font-weight: 600; ">'.
            $this->buttonName . '
        </button>';
    }

}
