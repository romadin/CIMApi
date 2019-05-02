<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 25-4-2019
 * Time: 15:04
 */

namespace App\Http\Handlers;


use App\Http\Controllers\Templates\TemplateDefault;
use App\Models\Template\Template;
use App\Models\Template\TemplateItem;
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

    public function getTemplatesByOrganisation(int $organisationId)
    {
        try {
            $result = DB::table(self::TEMPLATE_TABLE)
                ->where('organisationId', $organisationId)
                ->orWhere('organisationId', '=',0)
                ->get();
            if ( $result === null ) {
                return response('Template does not exist', 404);
            }
        } catch (\Exception $e) {
            return \response('TemplatesHandler: There is something wrong with the database connection',500);
        }

        $templates = [];
        foreach ($result as $item) {
            array_push($templates, $this->makeTemplate($item));
        }

        return $templates;
    }

    public function getTemplateByName(string $name, int $organisationId)
    {
        try {
            $result = DB::table(self::TEMPLATE_TABLE)
                ->where('name', $name)
                ->where('organisationId', $organisationId)
                ->first();
            if ( $result === null ) {
                return response('Template does not exist', 404);
            }
        } catch (\Exception $e) {
            return \response('TemplatesHandler: There is something wrong with the database connection',500);
        }
        return $this->makeTemplate($result);
    }

    public function getTemplateById(int $id)
    {
        try {
            $result = DB::table(self::TEMPLATE_TABLE)
                ->where('id', $id)
                ->first();
            if ( $result === null ) {
                return response('Template does not exist', 404);
            }
        } catch (\Exception $e) {
            return \response('TemplatesHandler: There is something wrong with the database connection',500);
        }

        return $this->makeTemplate($result);
    }

    public function createNewTemplate(array $postData)
    {
        $row = [
            'name' => $postData['name'],
            'organisationId' => $postData['organisationId'],
            'folders' => json_encode(TemplateDefault::FOLDER_DEFAULT),
            'subFolders' => json_encode(TemplateDefault::SUB_FOLDER_DEFAULT),
            'documents' => json_encode(TemplateDefault::DOCUMENTS_DEFAULT),
            'subDocuments' => json_encode(TemplateDefault::SUB_DOCUMENTS_DEFAULT)
        ];
        try {
            $id = DB::table(self::TEMPLATE_TABLE)
                ->insertGetId($row);
        } catch (\Exception $e) {
            return \response($e->getMessage(),500);
        }

        return $this->getTemplateById($id);
    }

    public function updateTemplate($id, array $postData)
    {
        /** @var Template $template */
        $template = $this->getTemplateById($id);

        foreach ($postData as $key => $data) {
            $setMethod = 'set'. ucfirst($key);
            if(method_exists($template, $setMethod)) {
                $getMethod = 'get'. ucfirst($key);
                /** @var TemplateItem[] | TemplateItemsWithParent[] $items */
                $items = $template->$getMethod();
                $data = json_decode($data);

                foreach($items as $item) {
                    /** @var TemplateItem | TemplateItemsWithParent $item */
                    if ($item instanceof TemplateItemsWithParent) {
                        if ($item->getName() === $data->name) {
                            foreach($item->getItems() as $subItem) {
                                /** @var TemplateItem $subItem */
                                array_filter($data->items, function($dataSubItem) use ($subItem) {
                                    if($subItem->getName() === $dataSubItem->name) {
                                        $this->templateItemHandler->updateTemplateItem($subItem, $dataSubItem);
                                    }
                                });
                            };
                        }
                    } else if ($item instanceof TemplateItem) {
                        if ($item->getName() === $data->name) {
                            $this->templateItemHandler->updateTemplateItem($item, $data);
                        }
                    }
                };
            }
        }
        return $this->updateTemplateDB($template);
    }

    public function deleteTemplate(int $id)
    {
        if ($id === 1) {
            return response('Forbidden to delete default template', 403);
        }
        try {
            DB::table(self::TEMPLATE_TABLE)
                ->where('id', $id)
                ->delete();
        } catch (\Exception $e) {
            return response('There is something wrong with the connection', 403);
        }
        return json_encode('Template deleted');
    }

    private function updateTemplateDB(Template $template)
    {
        try {
            $templateArray = $template->jsonSerialize();
            $templateArray['folders'] = json_encode($templateArray['folders']);
            $templateArray['subFolders'] = json_encode($templateArray['subFolders']);
            $templateArray['documents'] = json_encode($templateArray['documents']);
            $templateArray['subDocuments'] = json_encode($template->getSubDocuments());

            DB::table(self::TEMPLATE_TABLE)
                ->where('id', $template->getId())
                ->update($templateArray);
        } catch (\Exception $e) {
            return \response($e->getMessage(),500);
        }
        return $template;
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
        foreach (json_decode($data) as $item) {
            if ($multidimensional) {
                $array = [];
                $parentItems = new TemplateItemsWithParent();
                $parentItems->setName($item->name);
                foreach($item->items as $subItem) {
                    array_push($array, $this->templateItemHandler->makeTemplateItem($subItem));
                }
                $parentItems->setItems($array);
                array_push($items, $parentItems);

            } else {
                array_push($items, $this->templateItemHandler->makeTemplateItem($item));
            }
        }
        return $items;
    }
}