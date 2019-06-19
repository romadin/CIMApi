<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 19-6-2019
 * Time: 16:56
 */

namespace App\Http\Handlers;


use App\Models\Company\Company;
use App\Models\Project\Project;
use App\Models\WorkFunction\WorkFunction;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CompaniesHandler
{
    const TABLE_COMPANIES = 'companies';
    const TABLE_LINK_WORK_FUNCTION = WorkFunctionsHandler::MAIN_TABLE.'_has_'.self::TABLE_COMPANIES;

    /**
     * @var WorkFunctionsHandler
     */
    private $workFunctionsHandler;

    public function __construct(WorkFunctionsHandler $workFunctionsHandler)
    {
        $this->workFunctionsHandler = $workFunctionsHandler;
    }

    /**
     * @param WorkFunction $workFunction
     * @return array
     * @throws Exception
     */
    public function getCompaniesByWorkFunction(WorkFunction $workFunction)
    {
        try {
            $results = $this->baseQueryForGettingCompaniesFromUserAndWorkFunction()
                ->where(WorkFunctionsHandler::MAIN_TABLE.'.projectId', $workFunction->getProjectId())
                ->orWhere(UsersHandler::PROJECT_LINK_TABLE.'.projectId', $workFunction->getProjectId())
                ->groupBy(self::TABLE_COMPANIES.'.id')
                ->get();
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), 500);
        }

        $companies = [];
        foreach ($results as $result) {
            array_push($companies, $this->makeCompany($result));
        }

        return $companies;
    }

    /**
     * Get the companies from the given projects. This is uses to show all companies for an organisation
     * @param int[] $projectsId
     * @return Company[]|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function getCompaniesByProjects($projectsId)
    {
        try {
            $results = $this->baseQueryForGettingCompaniesFromUserAndWorkFunction()
                ->whereIn(WorkFunctionsHandler::MAIN_TABLE.'.projectId', $projectsId)
                ->orWhereIn(UsersHandler::PROJECT_LINK_TABLE.'.projectId', $projectsId)
                ->groupBy(self::TABLE_COMPANIES.'.id')
                ->get();
        } catch (Exception $e) {
            return response($e->getMessage(), 500);
        }

        $companies = [];
        foreach ($results as $result) {
            array_push($companies, $this->makeCompany($result));
        }
        return $companies;
    }

    /**
     * @param int $id
     * @return Company
     * @throws Exception
     */
    public function getCompanyById(int $id): Company{
        try {
            $result = DB::table(self::TABLE_COMPANIES)
                ->where('id', $id)
                ->first();
        }catch (Exception $e) {
            throw new Exception($e->getMessage(), 404);
        }

        return $this->makeCompany($result);
    }

    private function makeCompany($data): Company
    {
        $company = new Company();
        foreach ($data as $key => $value) {
            if ($value) {
                $method = 'set'. ucfirst($key);
                if(method_exists($company, $method)) {
                    $company->$method($value);
                }
            }
        }
        return $company;
    }

    private function baseQueryForGettingCompaniesFromUserAndWorkFunction(): Builder
    {
        return DB::table(WorkFunctionsHandler::MAIN_TABLE)
            ->select(self::TABLE_COMPANIES.'.id', self::TABLE_COMPANIES.'.name')
            ->join(self::TABLE_LINK_WORK_FUNCTION, WorkFunctionsHandler::MAIN_TABLE.'.id', '=', self::TABLE_LINK_WORK_FUNCTION.'.workFunctionId')
            ->join(UsersHandler::PROJECT_LINK_TABLE, WorkFunctionsHandler::MAIN_TABLE.'.projectId', '=', UsersHandler::PROJECT_LINK_TABLE.'.projectId')
            ->join(UsersHandler::USERS_TABLE, UsersHandler::PROJECT_LINK_TABLE.'.userId', '=', UsersHandler::USERS_TABLE.'.id')
            ->join(self::TABLE_COMPANIES, function($join) {
                $join->on(UsersHandler::USERS_TABLE.'.companyId', '=', self::TABLE_COMPANIES.'.id')
                    ->orOn(self::TABLE_LINK_WORK_FUNCTION.'.companyId', '=', self::TABLE_COMPANIES.'.id');
            });
    }

}