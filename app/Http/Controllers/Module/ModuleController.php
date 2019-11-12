<?php


namespace App\Http\Controllers\Module;

use App\Http\Handlers\ModulesHandler;
use App\Http\Handlers\OrganisationHandler;
use Exception;
use Illuminate\Http\Request;

class ModuleController
{
    /**
     * @var ModulesHandler
     */
    private $modulesHandler;

    /**
     * @var OrganisationHandler
     */
    private $organisationHandler;

    public function __construct(ModulesHandler $modulesHandler, OrganisationHandler $organisationHandler)
    {
        $this->modulesHandler = $modulesHandler;
        $this->organisationHandler = $organisationHandler;
    }

    public function joinModule(Request $request)
    {
        if (!$request->input('modulesId') || empty($request->input('modulesId'))) {
            return response('Module id is required', 200);
        }
        if ($request->input('organisationId')) {
            try {
                $organisation = $this->organisationHandler->getOrganisationById($request->input('organisationId'));
                return $this->modulesHandler->joinModulesToOrganisation($organisation, $request->input('modulesId'));
            } catch (Exception $e) {
                return response($e->getMessage(), 500);
            }
        }

        return response('Module id is required', 404);
    }
}
