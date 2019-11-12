<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 19-6-2019
 * Time: 15:25
 */

namespace App\Http\Handlers;


use App\Models\Module\Module;
use App\Models\Organisation\Organisation;
use Exception;
use Illuminate\Support\Facades\DB;

class ModulesHandler
{
    const DEFAULT_TEMPLATE = 3;
    const TABLE = 'modules';
    const TABLE_HAS_ORGANISATION = 'organisation_has_module';

    /**
     * Get the modules for the given organisation.
     * @param Organisation $organisation
     * @return Module[]|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws Exception
     */
    public function getModulesByOrganisation(Organisation $organisation)
    {
        try {
            $results = DB::table(self::TABLE)
                ->select(self::TABLE.'.id', self::TABLE.'.name', self::TABLE_HAS_ORGANISATION.'.isOn', self::TABLE_HAS_ORGANISATION.'.restrictions')
                ->join(self::TABLE_HAS_ORGANISATION, self::TABLE.'.id', '=', self::TABLE_HAS_ORGANISATION.'.moduleId')
                ->where('organisationId', $organisation->getId())
                ->get();
        }catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
        $modules = [];

        foreach ($results as $result) {
            array_push($modules, $this->makeModule($result));
        }

        return $modules;
    }

    /**
     * @param Organisation $organisation
     * @param array $modulesId
     * @param mixed $restrictions
     * @return Organisation
     * @throws Exception
     */
    public function joinModulesToOrganisation(Organisation $organisation, array $modulesId, $restrictions = null)
    {
        $currentModules = $organisation->getModules();

        foreach ($modulesId as $key => $module) {
            $moduleObj = isset($module['id']) ? $organisation->getModule($module['id']) : false;
            if (is_array($module) && $moduleObj) {
                var_dump($moduleObj->getRestrictions());
                $amount = $module['restrictions']->amount * self::DEFAULT_TEMPLATE + $moduleObj->getRestrictions()->amount;
                $rules = ['amount' => $amount];

                $updateData = ['restrictions' => json_encode($rules)];
                $where = [['organisationId', $organisation->getId()], ['moduleId', $module['id'] ]];
                $this->updateModuleRestriction($updateData, $where);
                array_splice($modulesId, $key, 1);
            }
        }

        $modulesIdToAdd = array_filter($modulesId, function($moduleId) use ($currentModules) {
            if ($moduleId > 0 && $moduleId < 5) {
                // If array is empty we know that we dont have that module.
                return empty(array_filter($currentModules, function ($currentModule) use ($moduleId) {
                    /** @var Module $currentModule */
                    return (int)$moduleId === $currentModule->getId();
                }));
            }
            return false;
        });

        $postData = array_map(function($moduleId) use ($organisation, $restrictions) {
            $data = ['organisationId' => $organisation->getId(), 'moduleId' => $moduleId, 'isOn' => true, 'restrictions' => '{}'];
            return $moduleId === 1 && $restrictions ? $data['restrictions'] = $restrictions : $data;
        }, $modulesIdToAdd);

        $whereData = [];
        foreach ($modulesIdToAdd as $moduleId) {
            $whereData[] = ['organisationId', $organisation->getId()];
            $whereData[] = ['moduleId', $moduleId];
        }

        if (empty($postData)) {
            return $organisation;
        }

        try {
            $this->linkModules($postData, $whereData);
            $organisation->setModules($this->getModulesByOrganisation($organisation));
            return $organisation;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 400);
        }
    }

    /**
     * @param array $postData
     * @param array $whereData
     * @throws Exception
     */
    private function updateModuleRestriction(array $postData, array $whereData)
    {
        try {
            DB::table(self::TABLE_HAS_ORGANISATION)
                ->where($whereData)
                ->update($postData);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 400);
        }
    }

    /**
     * @param array $postData
     * @param array $whereData
     * @return bool
     * @throws Exception
     */
    private function linkModules(array $postData, array $whereData)
    {
        try {
            $isEmpty = DB::table(self::TABLE_HAS_ORGANISATION)
                ->where($whereData)
                ->get()->isEmpty();
            if ($isEmpty) {
                DB::table(self::TABLE_HAS_ORGANISATION)
                    ->insert($postData);
                return true;
            }
            throw new \Exception('Link already exists', 400);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 400);
        }
    }

    private function makeModule($data): Module
    {
        $module = new Module();
        foreach ($data as $key => $value) {
            if ($value !== null) {
                $method = 'set'. ucfirst($key);
                if(method_exists($module, $method)) {
                    if ($key === 'restrictions') {
                        $value = json_decode($value);
                        var_dump($value);
                    }
                    $module->$method($value);
                }
            }
        }

        return $module;
    }

}
