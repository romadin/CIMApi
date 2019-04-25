<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 25-4-2019
 * Time: 15:04
 */

namespace App\Http\Handlers;


use App\Models\Template\Template;
use App\Models\Template\TemplateItemsWithParent;
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

        $template->setId($data->id);
        $template->setName($data->name);
        $template->setFolders($this->createTemplateItems($data->folders));
        $template->setSubFolders($this->createTemplateItems($data->subFolders));
        $template->setDocuments($this->createTemplateItems($data->documents));
        $template->setSubDocuments($this->createTemplateItems($data->subDocuments, true));

        return $template;
    }

    /**
     * Create template items. If its multidimensional then we create template items with a parent.
     * @param $data
     * @param bool $multidimensional
     * @return array
     */
    private function createTemplateItems($data, $multidimensional = false): array
    {
        $items = [];
        foreach (json_decode($data) as $key => $itemValue) {
            if ($multidimensional) {
                $array = [];
                $parentItems = new TemplateItemsWithParent();
                $parentItems->setName($key);
                foreach($itemValue as $item) {
                    array_push($array, $this->templateItemHandler->makeTemplateItem($item));
                }
                $parentItems->setItems($array);
                array_push($items, $parentItems);

            } else {
                array_push($items, $this->templateItemHandler->makeTemplateItem($itemValue));
            }
        }
        return $items;
    }
}