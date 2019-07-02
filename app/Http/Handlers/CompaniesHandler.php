<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 19-6-2019
 * Time: 16:56
 */

namespace App\Http\Handlers;


use App\Models\Company\Company;
use App\Models\WorkFunction\WorkFunction;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CompaniesHandler
{
    const TABLE_COMPANIES = 'companies';
    const TABLE_LINK_DOCUMENT = self::TABLE_COMPANIES . '_has_' . DocumentsHandler::DOCUMENT_TABLE;
    const TABLE_LINK_FOLDER = self::TABLE_COMPANIES . '_has_' . FoldersHandler::FOLDERS_TABLE;
    const TABLE_LINK_WORK_FUNCTION = WorkFunctionsHandler::MAIN_TABLE.'_has_'.self::TABLE_COMPANIES;

    /**
     * @param WorkFunction $workFunction
     * @return array
     * @throws Exception
     */
    public function getCompaniesByWorkFunction(WorkFunction $workFunction)
    {
        try {
            $results = DB::table(self::TABLE_COMPANIES)
                ->select(self::TABLE_COMPANIES.'.id', self::TABLE_COMPANIES.'.name')
                ->join(self::TABLE_LINK_WORK_FUNCTION, self::TABLE_COMPANIES.'.id', '=', self::TABLE_LINK_WORK_FUNCTION.'.companyId')
                ->where(self::TABLE_LINK_WORK_FUNCTION.'.workFunctionId', $workFunction->getId())
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
    public function getCompanyById(int $id): Company {
        try {
            $result = DB::table(self::TABLE_COMPANIES)
                ->where('id', $id)
                ->first();
        }catch (Exception $e) {
            throw new Exception($e->getMessage(), 404);
        }

        return $this->makeCompany($result);
    }

    /**
     * @param array $postData
     * @return Company
     * @throws Exception
     */
    public function createCompany($postData): Company
    {
        try {
            $id = DB::table(self::TABLE_COMPANIES)
                ->insertGetId($postData);
            $company = $this->getCompanyById($id);
        }catch (Exception $e) {
            throw new Exception($e->getMessage(), 404);
        }

        return $company;
    }

    /**
     * @param Company $company
     * @return Company
     * @throws Exception
     */
    public function editCompany(Company $company): Company
    {
        try {
            DB::table(self::TABLE_COMPANIES)
                ->where('id', $company->getId())
                ->update($company->jsonSerialize());
        }catch (Exception $e) {
            throw new Exception($e->getMessage(), 404);
        }

        return $company;
    }

    public function deleteCompanyLink(string $linkTable, string $linkIdName, int $linkId, int $companyId)
    {
        try {
            DB::table($linkTable)
                ->where($linkIdName, $linkId)
                ->where('companyId', $companyId)
                ->delete();
            if ($this->checkForNoConnections($companyId)) {
                return $this->deleteCompany($companyId);
            }
        } catch (\Exception $e) {
            return response('CompaniesHandler: There is something wrong with the database connection', 500);
        }

        return json_decode('Company link deleted');
    }

    public function deleteCompany(int $id)
    {
        try {
            if($this->checkForNoConnections($id)) {
                DB::table(self::TABLE_COMPANIES)
                    ->where('id', $id)
                    ->delete();
            }else {
                return response('Can not delete company there are still connections', 200);
            }
        } catch (Exception $e) {
            return response($e->getMessage(), 200);
        }
        return json_decode('Company deleted');
    }

    /**
     * Set the link between company and folder or document.
     * @param Company $company
     * @param int[] $itemsId
     * @param string $itemIdName
     * @param string $linkTable
     * @param bool $noOrder
     * @throws Exception
     */
    public function addChildItems(Company $company, $itemsId, string $itemIdName, string $linkTable, $noOrder = false): void
    {
        foreach ($itemsId as $itemId) {
            $row = [
                $itemIdName => $itemId,
                'companyId' => $company->getId(),
            ];

            try {
                if (!$noOrder) {
                    $row['order'] = $this->getHighestOrderOfChildItems($company->getId(), $linkTable, $this->getLinkTableSibling($linkTable)) + 1;
                }

                $isEmpty = DB::table($linkTable)
                    ->where($itemIdName, $itemId)
                    ->where('companyId', $company->getId())
                    ->get()->isEmpty();

                if($isEmpty) {
                    DB::table($linkTable)
                        ->insert($row);
                }
            } catch (Exception $e) {
                throw new Exception($e->getMessage(),500);
            }
        }
    }

    /**
     * @param int $companyId
     * @param string $linkTable
     * @param string $linkTableSibling
     * @return int
     * @throws Exception
     */
    private function getHighestOrderOfChildItems(int $companyId, string $linkTable, string $linkTableSibling): int
    {
        try {
            $query = DB::table($linkTable)
                ->select('order')
                ->where('companyId', $companyId);

            $result = DB::table($linkTableSibling)
                ->select('order')
                ->where('companyId', $companyId)
                ->union($query)
                ->orderByDesc('order')
                ->first();
            if ($result == null) {
                return 0;
            }
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), 403);
        }

        return $result->order;
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

    /**
     * @param int $id
     * @return bool
     * @throws Exception
     */
    private function checkForNoConnections(int $id): bool
    {
        try {
            $query = DB::table(UsersHandler::USERS_TABLE)
                ->select(UsersHandler::USERS_TABLE.'.companyId', DB::raw('count(*) as total'))
                ->where(UsersHandler::USERS_TABLE.'.companyId', $id)
                ->groupBy(UsersHandler::USERS_TABLE.'.companyId');
            $hasNoConnections = DB::table(self::TABLE_LINK_WORK_FUNCTION)
                ->select(self::TABLE_LINK_WORK_FUNCTION.'.companyId', DB::raw('count(*) as total'))
                ->union($query)
                ->where(self::TABLE_LINK_WORK_FUNCTION.'.companyId', $id)
                ->groupBy('companyId')
                ->get()->isEmpty();
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }

        return $hasNoConnections;
    }

    private function baseQueryForGettingCompaniesFromUserAndWorkFunction(): Builder
    {
        return DB::table(WorkFunctionsHandler::MAIN_TABLE)
            ->select(self::TABLE_COMPANIES.'.id', self::TABLE_COMPANIES.'.name')
            ->leftJoin(self::TABLE_LINK_WORK_FUNCTION, WorkFunctionsHandler::MAIN_TABLE.'.id', '=', self::TABLE_LINK_WORK_FUNCTION.'.workFunctionId')
            ->leftJoin(UsersHandler::PROJECT_LINK_TABLE, WorkFunctionsHandler::MAIN_TABLE.'.projectId', '=', UsersHandler::PROJECT_LINK_TABLE.'.projectId')
            ->leftJoin(UsersHandler::USERS_TABLE, UsersHandler::PROJECT_LINK_TABLE.'.userId', '=', UsersHandler::USERS_TABLE.'.id')
            ->rightJoin(self::TABLE_COMPANIES, function($join) {
                $join->on(UsersHandler::USERS_TABLE.'.companyId', '=', self::TABLE_COMPANIES.'.id')
                    ->orOn(self::TABLE_LINK_WORK_FUNCTION.'.companyId', '=', self::TABLE_COMPANIES.'.id');
            });
    }

    private function getLinkTableSibling($linkTable): string {
        switch ($linkTable) {
            case (self::TABLE_LINK_DOCUMENT):
                return self::TABLE_LINK_FOLDER;
            case (self::TABLE_LINK_FOLDER):
                return self::TABLE_LINK_DOCUMENT;
        }

        return self::TABLE_COMPANIES;
    }

}