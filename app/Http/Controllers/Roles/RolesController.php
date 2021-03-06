<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 13-1-2019
 * Time: 15:35
 */

namespace App\Http\Controllers\Roles;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

class RolesController extends ApiController
{
    const ROLES_TABLE = 'roles';

    /**
     * @param Request $request
     * @param int $id
     * @param bool $objectFormat
     * @return \Illuminate\Http\Response|\JsonSerializable|\Laravel\Lumen\Http\ResponseFactory|object|string
     */
    public function getRole(Request $request, $id, $objectFormat = true)
    {
        try {
            $result = DB::table(self::ROLES_TABLE)
                ->where('id', '=', $id)
                ->first();
            if ($result === null) {
                return response('Role not found', 404);
            }

        } catch (\Exception $e) {
            return response('Role not found', 404);
        }

        $role = new Role(
            $result->id,
            $result->name
        );

        return $this->getReturnValueObject($request, $role, $objectFormat);
    }

}