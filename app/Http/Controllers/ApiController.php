<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 13-1-2019
 * Time: 15:53
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JsonSerializable;
use Laravel\Lumen\Routing\Controller;


class ApiController extends Controller
{
    /**
     * Get the right return value from the given object, specified with the format given.
     * @param JsonSerializable $object
     * @param Request $request
     * @return JsonSerializable|string | object
     */
    public function getReturnValue(Request $request, JsonSerializable $object)
    {
        if ($request->input('format') === 'json') {
            return json_encode($object->jsonSerialize());
        }

        return $object;
    }

}