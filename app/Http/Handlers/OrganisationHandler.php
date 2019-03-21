<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 19-3-2019
 * Time: 11:43
 */

namespace App\Http\Handlers;

use App\Models\Organisation\Organisation;
use Illuminate\Support\Facades\DB;

class OrganisationHandler
{
    const table = 'organisations';

    public function getOrganisationByName(string $name)
    {
        try {
            $result = DB::table(self::table)->where('name', $name)->first();
        } catch (\Exception $e) {
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

    private function makeOrganisation($data): Organisation
    {
        $organisation = new Organisation();

        foreach ($data as $key => $value) {
            if ($value) {
                $method = 'set'. ucfirst($key);
                if(method_exists($organisation, $method)) {
                    $organisation->$method($value);
                }
            }
        }
        return $organisation;
    }

}