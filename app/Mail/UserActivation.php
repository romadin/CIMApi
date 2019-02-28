<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 20-2-2019
 * Time: 23:46
 */

namespace App\Mail;


use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserActivation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The user that needs to become activate.
     *
     * @var User
     */
    protected $user;

    /**
     * The hostname
     *
     * @var string
     */
    protected $hostName = 'http://ec2-35-176-156-216.eu-west-2.compute.amazonaws.com';
//    protected $hostName = 'http://localhost:4200';

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->view('emails.userActivationView')
            ->with([
                'userName' => $this->user->getFirstName() . ' ' . $this->user->getInsertion() ? $this->user->getInsertion() . ' ' . $this->user->getLastName() : $this->user->getLastName(),
                'email' => $this->user->getEmail(),
                'link' => $this->getLink() ])
            ->subject('Gebruiker activatie mail voor de BIM uitvoering app');
    }

    private function getLink():string
    {
        return $this->hostName . '/activate/' . $this->user->getToken();
    }

}