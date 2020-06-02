<?php


namespace App\Http\Controllers\PublicController;


use App\Http\Handlers\DocumentsHandler;
use App\Http\Handlers\OrganisationHandler;
use App\Http\Handlers\WorkFunctionsHandler;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicController
{
    /**
     * @var DocumentsHandler
     */
    private $documentsHandler;

    /**
     * @var WorkFunctionsHandler
     */
    private $workFunctionsHandler;


    /**
     * @var OrganisationHandler
     */
    private $organisationHandler;

    public function __construct(
        DocumentsHandler $documentsHandler,
        WorkFunctionsHandler $workFunctionsHandler,
        OrganisationHandler $organisationHandler)
    {
        $this->documentsHandler = $documentsHandler;
        $this->workFunctionsHandler = $workFunctionsHandler;
        $this->organisationHandler = $organisationHandler;
    }
    public function getProjectsAndUsersNumbers(Request $request) {
        try {

            if ($request->input('organisationName')) {
                $organisation = $this->organisationHandler->getOrganisationByName($request->input('organisationName'));

                $projectNumber = DB::table('projects')
                    ->where('organisationId', $organisation->getId())
                    ->count();

                $usersNumber = DB::table('users')
                    ->where('organisationId', $organisation->getId())
                    ->count();

                return ['users' => $usersNumber, 'projects' => $projectNumber];
            }
            $projectNumber = DB::table('projects')->count();
            $usersNumber = DB::table('users')->count();
            return ['users' => $usersNumber, 'projects' => $projectNumber];
        } catch (Exception $e) {
            return response('There is something wrong with the database', 400);
        }

    }

}
