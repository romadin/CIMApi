<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 19-3-2019
 * Time: 11:43
 */

namespace App\Http\Handlers;

use App\Models\Organisation\Organisation;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;

class OrganisationHandler
{
    const table = 'organisations';
    /**
     * @var ModulesHandler
     */
    private $modulesHandler;

    public function __construct(ModulesHandler $modulesHandler)
    {
        $this->modulesHandler = $modulesHandler;
    }

    public function getOrganisationByName(string $name)
    {
        try {
            $result = DB::table(self::table)->where('name', $name)->first();
        } catch (Exception $e) {
            return json_encode($e->getMessage());
        }

        if (!$result) {
            return [];
        }

        return $this->makeOrganisation($result);
    }

    public function getOrganisationById(int $id)
    {
        $result = DB::table(self::table)->where('id', $id)->first();

        return $this->makeOrganisation($result);
    }

    public function getImage(int $id)
    {
        try {
            $logo = DB::table(self::table)
                ->select('logo')
                ->where('id', $id)
                ->first();
        } catch (Exception $e) {
            return json_encode($e->getMessage());
        }

        return $logo->logo ?: json_encode(null);
    }

    /**
     * @param string $name
     * @param DateTime $demoPeriod
     * @return Organisation|bool|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws Exception
     */
    public function createOrganisation(string $name, DateTime $demoPeriod = null)
    {
        try {
            $exist = DB::table(self::table)
                ->where('name', $name)
                ->get()->isNotEmpty();

            if ($exist) return true;


            $id = DB::table(self::table)
                ->insertGetId(['name' => $name, 'demoPeriod' => $demoPeriod->format('Y-m-d H:i:s')]);

            // Add template module for default with restriction amount 1.
            $where = [['organisationId', $id], ['moduleId', 1] ];
            $data = [['organisationId' => $id, 'moduleId' => 1, 'isOn' => true, 'restrictions' => '{"amount": 1}'], ['organisationId' => $id, 'moduleId' => 3, 'isOn' => true, 'restrictions' => '{}']];
            $this->modulesHandler->linkModules($data, $where);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 400);
        }
        return $this->getOrganisationById($id);
    }

    public function updateOrganisation($postData, int $id, $logo)
    {
        $logo ? $postData['logo'] = $logo->openFile()->fread($logo->getSize()) : null;

        try {
            DB::table(self::table)
                ->where('id', $id)
                ->update($postData);
        } catch (\Exception $e) {
            return response($e->getMessage());
        }

        return $this->getOrganisationById($id);
    }


    /**
     * @param $data
     * @return Organisation|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws Exception
     */
    private function makeOrganisation($data)
    {
        $organisation = new Organisation();

        foreach ($data as $key => $value) {
            if ($value) {
                $method = 'set'. ucfirst($key);
                if(method_exists($organisation, $method) && $method === 'setDemoPeriod') {
                    $period = new DateTime($value);
                    $organisation->$method($period);
                } else if (method_exists($organisation, $method) ) {
                    $organisation->$method($value);
                }
            }
        }
        try {
            $modules = $this->modulesHandler->getModulesByOrganisation($organisation);
        } catch (Exception $e) {
            return response($e->getMessage(), 500);
        }

        $organisation->setModules($modules);
        return $organisation;
    }

}
