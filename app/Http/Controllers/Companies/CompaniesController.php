<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 19-6-2019
 * Time: 16:52
 */

namespace App\Http\Controllers\Companies;


use App\Http\Handlers\CompaniesHandler;
use App\Http\Handlers\OrganisationHandler;
use App\Http\Handlers\WorkFunctionsHandler;
use Exception;
use Illuminate\Http\Request;

class CompaniesController
{
    /**
     * @var CompaniesHandler
     */
    private $companiesHandler;

    /**
     * @var OrganisationHandler
     */
    private $organisationHandler;

    /**
     * @var WorkFunctionsHandler
     */
    private $workFunctionsHandler;


    public function __construct(CompaniesHandler $companiesHandler, OrganisationHandler $organisationHandler, WorkFunctionsHandler $workFunctionsHandler)
    {
        $this->companiesHandler = $companiesHandler;
        $this->organisationHandler = $organisationHandler;
        $this->workFunctionsHandler = $workFunctionsHandler;
    }

    public function getCompanies(Request $request)
    {
        if($request->input('workFunctionId')) {
            $workFunction = $this->workFunctionsHandler->getWorkFunction($request->input('workFunctionId'));
            try {
                return $this->companiesHandler->getCompaniesByWorkFunction($workFunction);
            } catch (Exception $e) {
                return response($e->getMessage(), 500);
            }
        } else if ($request->input('projectsId')) {
            return $this->companiesHandler->getCompaniesByProjects($request->input('projectsId'));
        }

        return response('no project ids or work function id given', 404);
    }

    public function getCompany(int $id)
    {
        try {
            return $this->companiesHandler->getCompanyById($id);
        } catch (Exception $e) {
            return response($e->getMessage(), 500);
        }
    }

    public function postCompany(Request $request)
    {
        if (!$request->input('name')) {
            return response('no name given', 404);
        }
        try {
            return $this->companiesHandler->createCompany($request->post());
        } catch (Exception $e) {
            return response($e->getMessage(), 500);
        }
    }

    public function editCompany(Request $request, int $id)
    {
        $company = $this->getCompany($id);
        $postData = $request->post();

        try {
            if (isset($postData['name'])) {
                $company->setName($postData['name']);
                return $this->companiesHandler->editCompany($company);
            }
        } catch (Exception $e) {
            return response($e->getMessage(), 500);
        }
        return response('Wrong data given', 400);
    }

    public function deleteCompany(Request $request, int $id)
    {
        if ($request->input('workFunctionId')) {
            return $this->companiesHandler->deleteCompanyLink(CompaniesHandler::TABLE_LINK_WORK_FUNCTION, 'workFunctionId', $request->input('workFunctionId'), $id);
        }

        return $this->companiesHandler->deleteCompany($id);
    }

}
