<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 25-4-2019
 * Time: 15:03
 */

namespace App\Http\Controllers\Templates;


use App\Http\Handlers\TemplatesHandler;
use Illuminate\Http\Request;

class TemplatesController
{

    /**
     * @var TemplatesHandler
     */
    private $templateHandler;

    public function __construct(TemplatesHandler $templatesHandler)
    {
        $this->templateHandler = $templatesHandler;
    }

    public function getTemplates(Request $request)
    {
        if ( !$request->input('organisationId')) {
            return response('No organisation id was given', 400);
        }
        return $this->templateHandler->getTemplatesByOrganisation($request->input('organisationId'));
    }

    public function getTemplate($id)
    {
        if ( !$id) {
            return response('No template id was given', 400);
        }

        return $this->templateHandler->getTemplateById($id);
    }

    public function postTemplate(Request $request)
    {
        if ( !$request->input('name')) {
            return response('No name was given', 400);
        }
        if ( !$request->input('organisationId')) {
            return response('No organisation id was given', 400);
        }
        return $this->templateHandler->createNewTemplate($request->post());
    }

    public function updateTemplate(Request $request, $id)
    {
        if( !$request->input('name')) {
            return response('No new content was given', 400);
        }
        return $this->templateHandler->updateTemplate($id, $request->post());
    }

    public function deleteTemplate(int $id)
    {
        return $this->templateHandler->deleteTemplate($id);
    }
}