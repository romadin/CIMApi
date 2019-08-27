<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 20-2-2019
 * Time: 23:46
 */

namespace App\Mail;


use App\Models\Project\Project;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\Organisation\Organisation;
use App\Models\User;

class UserAddedToProject extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The user that needs to become activate.
     *
     * @var User
     */
    protected $user;

    /**
     * @var Organisation
     */
    private $organisation;

    /**
     * @var Project
     */
    private $project;

    public function __construct(User $user, Organisation $organisation, Project $project)
    {
        $this->user = $user;
        $this->organisation = $organisation;
        $this->project = $project;
    }

    public function build()
    {
        $username = $this->user->getFirstName() . ' ';
        $username .= $this->user->getInsertion() ? $this->user->getInsertion() . ' ' . $this->user->getLastName() : $this->user->getLastName();
        return $this->view('emails.userAddedToProject')
            ->with([
                'userName' => $username,
                'projectName' => $this->project->getName(),
                'link' => $this->getLink()
            ])
            ->subject('Nieuw project');
    }

    private function getLink():string
    {
        return 'http://' . $this->organisation->getName() .'.'. env('APP_URL') . '/login';
    }

}