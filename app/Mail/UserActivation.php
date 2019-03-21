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

    public function __construct(User $user, Organisation $organisation)
    {
        $this->user = $user;
        $this->organisation = $organisation;
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
        return $this->organisation->getName() .'.'. env('APP_URL') . '/gebruikers/activate/' . $this->user->getToken();
    }

}