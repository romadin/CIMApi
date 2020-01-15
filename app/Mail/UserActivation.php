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
use App\Http\Handlers\OrganisationHandler;
use App\Models\Organisation\Organisation;
use App\Models\User;

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
     * @var OrganisationHandler
     */
    private $organisation;

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

    public function __construct(User $user, Organisation $organisation, $subject, $message, $buttonTitle)
    {
        $this->user = $user;
        $this->organisation = $organisation;
        $this->subject = $subject;
        $this->message = $message;
        $this->buttonName = $buttonTitle;
    }

    public function build()
    {
        $username = $this->user->getFirstName() . ' ';
        $username .= $this->user->getInsertion() ? $this->user->getInsertion() . ' ' . $this->user->getLastName() : $this->user->getLastName();

        return $this->view('emails.userActivationView')
            ->with([
                'userName' => $username,
                'email' => $this->user->getEmail(),
                'link' => $this->getLink(),
                'contentMessage' => $this->message,
                'buttonTitle' => $this->buttonName
            ])
            ->subject($this->subject);
    }

    private function getLink():string
    {
        return 'http://' . $this->organisation->getName() .'.'. env('APP_URL') . '/gebruikers/activate/' . $this->user->getToken();
    }

}
