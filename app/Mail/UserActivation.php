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
    private $organisationHandler;

    public function __construct(User $user, OrganisationHandler $organisationHandler)
    {
        $this->user = $user;
        $this->organisationHandler = $organisationHandler;
    }

    public function build()
    {
        $organisation = $this->organisationHandler->getOrganisationById($this->user->getOrganisationId());

        return $this->view('emails.userActivationView')
            ->with([
                'userName' => $this->user->getFirstName() . ' ' . $this->user->getInsertion() ? $this->user->getInsertion() . ' ' . $this->user->getLastName() : $this->user->getLastName(),
                'email' => $this->user->getEmail(),
                'link' => $this->getLink($organisation) ])
            ->subject('Gebruiker activatie mail voor de BIM uitvoering app');
    }

    private function getLink(Organisation $organisation):string
    {
        return $organisation->getName() .'.'. env('APP_URL') . '/gebruikers/activate/' . $this->user->getToken();
    }

}