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
    public function getReturnValueObject(Request $request, JsonSerializable $object)
    {
        if ($request->input('format') === 'json') {
            return json_encode($object->jsonSerialize());
        }

        return $object;
    }

    /**
     * Get the right return value from the given array, specified with the format given.
     * @param Request $request
     * @param []JsonSerializable $arrayItems
     * @return bool | array
     */
    public function getReturnValueArray(Request $request, $arrayItems)
    {
        if ($request->input('format') === 'json') {
            $array = [];
            /** @var JsonSerializable $item */
            foreach ($arrayItems as $item) {
                array_push($array, $item->jsonSerialize());
            }
            return json_encode($array);
        }

        return $arrayItems;
    }

}