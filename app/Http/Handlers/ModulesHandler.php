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
     * @param mixed $restriction
     * @return Organisation
     * @throws Exception
     */
    public function joinModulesToOrganisation(Organisation $organisation, array $modulesId, $restriction = null)
    {
        $currentModules = $organisation->getModules();


        $templateModuleKey = array_search(1, $modulesId);

        array_filter($modulesId, function($module) {
            var_dump($module);
        });
        die;
        if ($templateModuleKey && $organisation->getModule(1)) {
            // we only need to edit restriction
            array_splice($modulesId, $templateModuleKey, 1);
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

        $postData = array_map(function($moduleId) use ($organisation, $restriction) {
            $data = ['organisationId' => $organisation->getId(), 'moduleId' => $moduleId, 'isOn' => true];
            return $moduleId === 1 && $restriction ? $data['restriction'] = $restriction : $data;
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
                    $module->$method($value);
                }
            }
        }

        return $module;
    }

}
