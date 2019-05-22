<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 25-4-2019
 * Time: 15:04
 */

namespace App\Http\Handlers;


use App\Http\Controllers\Templates\TemplateDefault;
use App\Models\Chapter\Chapter;
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
    /**
     * @var WorkFunctionsHandler
     */
    private $workFunctionsHandler;
    /**
     * @var ChaptersHandler
     */
    private $chaptersHandler;

    public function __construct(TemplateItemsHandler $templateItemsHandler, WorkFunctionsHandler $workFunctionsHandler, ChaptersHandler $chaptersHandler)
    {
        $this->templateItemHandler = $templateItemsHandler;
        $this->workFunctionsHandler = $workFunctionsHandler;
        $this->chaptersHandler = $chaptersHandler;
    }

    public function getTemplatesByOrganisation(int $organisationId)
    {
        try {
            $result = DB::table(self::TEMPLATE_TABLE)
                ->where('organisationId', $organisationId)
                ->get();
            if ( $result->isEmpty() ) {
                return $this->createNewDefaultTemplate($organisationId);
            }
        } catch (\Exception $e) {
            return \response($e->getMessage(),500);
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
            return \response($e->getMessage(),500);
        }

        return $this->makeTemplate($result);
    }

    public function createNewTemplate(array $postData)
    {
        try {
            $id = DB::table(self::TEMPLATE_TABLE)
                ->insertGetId($postData);
        } catch (\Exception $e) {
            return \response($e->getMessage(),500);
        }

        $template = $this->getTemplateById($id);

        $template->setWorkFunctions($this->workFunctionsHandler->postWorkFunctions($template->getId(), TemplateDefault::WORK_FUNCTIONS));

        return $template;
    }

    public function updateTemplate(int $id, array $postData)
    {
        try {
            DB::table(self::TEMPLATE_TABLE)
                ->where('id', $id)
                ->update($postData);
        } catch (\Exception $e) {
            return \response($e->getMessage(),500);
        }
        return $this->getTemplateById($id);
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

    private function createNewDefaultTemplate(int $organisationId)
    {
        $templateDefault = $this->getTemplateById(1);
        $postData = [
            'name' => 'Standaard template',
            'organisationId' => $organisationId
        ];
        $newTemplate = $this->createNewTemplate($postData);

        foreach ($templateDefault->getWorkFunctions() as $workFunction) {
            foreach ($workFunction->getChapters() as $defaultChapter) {
                $chapterContent = $defaultChapter->getContent();
                if ($chapterContent !== null && $chapterContent !== '') {
                    // set to new chapter
                    foreach ($newTemplate->getWorkFunctions() as $newWorkFunction) {
                        $this->setDefaultContentForChapter($newWorkFunction->getChapters(), $defaultChapter);
                    }
                }
            }

            foreach ($workFunction->getHeadlines() as $defaultHeadline) {
                foreach ($defaultHeadline->getChapters() as $defaultHeadlineChapter) {
                    $chapterContent = $defaultHeadlineChapter->getContent();
                    if ($chapterContent !== null && $chapterContent !== '') {
                        // set to new chapter
                        foreach ($newTemplate->getWorkFunctions() as $newWorkFunction) {
                            foreach ($newWorkFunction->getHeadlines() as $newHeadline) {
                                $this->setDefaultContentForChapter($newHeadline->getChapters(), $defaultHeadlineChapter);
                            }
                        }
                    }
                }
            }
        }
        return [$newTemplate];
    }

    /**
     * Search for the chapter with the same name. And set the new content on the chapter.
     * @param Chapter[] $chapters
     * @param Chapter $defaultChapter
     */
    private function setDefaultContentForChapter($chapters, Chapter $defaultChapter): void
    {
        $chapterToSetContentOn = array_filter($chapters, function($chapter) use ($defaultChapter) { return $chapter->getName() === $defaultChapter->getName(); });
        if (count($chapterToSetContentOn) === 1) {
            $chapterToSetContentOn = reset($chapterToSetContentOn);
            $chapterToSetContentOn->setContent($defaultChapter->getContent());
            $this->chaptersHandler->updateChapter($chapterToSetContentOn);
        }
    }

    private function makeTemplate($data): Template
    {
        $template = new Template();

        $template->setId($data->id);
        $template->setName($data->name);
        $template->setOrganisationId($data->organisationId);
        $template->setDefault($data->isDefault);
        $template->setWorkFunctions($this->workFunctionsHandler->getWorkFunctions($template->getId()));

        return $template;
    }

}