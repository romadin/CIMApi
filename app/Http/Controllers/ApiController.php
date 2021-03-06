<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 13-1-2019
 * Time: 15:53
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JsonSerializable;
use Laravel\Lumen\Routing\Controller;


class ApiController extends Controller
{
    /**
     * Get the right return value from the given object, specified with the format given.
     * @param JsonSerializable | Response $object
     * @param Request $request
     * @param bool $format
     * @return JsonSerializable|string | object
     */
    public function getReturnValueObject(Request $request, $object, $format = true)
    {
        if ($object instanceof Response) {
            return response($object);
        }
        if ($request->input('format') === 'json' && $format) {
            return json_encode($object->jsonSerialize());
        }

        return $object;
    }

    /**
     * Get the right return value from the given array, specified with the format given.
     * @param Request $request
     * @param JsonSerializable[] $arrayItems
     * @param bool $format
     * @return array
     */
    public function getReturnValueArray(Request $request, $arrayItems, $format = true)
    {
        if ($request->input('format') === 'json' && $format) {
            $array = [];
            /** @var JsonSerializable $item */
            foreach ($arrayItems as $item) {
                if ($item instanceof Response) {
                    return response($item);
                }
                array_push($array, $item->jsonSerialize());
            }
            return json_encode($array);
        }

        return $arrayItems;
    }

}