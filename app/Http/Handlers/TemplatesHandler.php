<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 25-4-2019
 * Time: 15:04
 */

namespace App\Http\Handlers;


use App\Models\Template\Template;
use Illuminate\Support\Facades\DB;

class TemplatesHandler
{
    const TEMPLATE_TABLE = 'templates';
    /**
     * @var TemplateItemsHandler
     */
    private $templateItemHandler;

    public function __construct(TemplateItemsHandler $templateItemsHandler)
    {
        $this->templateItemHandler = $templateItemsHandler;
    }

    public function getTemplateByName(string $name)
    {
        try {
            $result = DB::table(self::TEMPLATE_TABLE)
                ->where('name', '=', $name)
                ->first();
            if ( $result === null ) {
                return response('Template does not exist', 404);
            }
        } catch (\Exception $e) {
            return \response('TemplatesHandler: There is something wrong with the database connection',500);
        }

        return $this->makeTemplate($result);
    }

    private function makeTemplate($data): Template
    {
        $template = new Template();

        foreach ($data as $key => $value) {
            if ($value) {
                $method = 'set'. ucfirst($key);
                if(method_exists($template, $method)) {
                    if ($method === 'setFolders' || $method === 'setSubFolders') {
                        $items = [];
                        foreach (json_decode($value) as $itemValue) {
                            array_push($items, $this->templateItemHandler->makeTemplateItem($itemValue));
                        }
                        $template->$method($items);
                    }else {
                        $template->$method($value);
                    }
                }
            }
        }
        return $template;
    }
}