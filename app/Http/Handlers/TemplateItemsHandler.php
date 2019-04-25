<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 25-4-2019
 * Time: 15:34
 */

namespace App\Http\Handlers;


use App\Models\Template\TemplateItem;

class TemplateItemsHandler
{

    public function makeTemplateItem($data): TemplateItem
    {
        $item = new TemplateItem();

        foreach ($data as $key => $value) {
            if ($value) {
                $method = 'set'. ucfirst($key);
                if(method_exists($item, $method)) {
                    $item->$method($value);
                }
            }
        }
        return $item;
    }
}