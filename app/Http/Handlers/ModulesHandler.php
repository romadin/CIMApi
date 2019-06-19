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

    private function makeModule($data): Module
    {
        $module = new Module();
        foreach ($data as $key => $value) {
            if ($value) {
                $method = 'set'. ucfirst($key);
                if(method_exists($module, $method)) {
                    $module->$method($value);
                }
            }
        }

        return $module;
    }

}