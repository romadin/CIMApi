<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 25-4-2019
 * Time: 15:03
 */

namespace App\Http\Controllers\Templates;


use App\Http\Handlers\TemplatesHandler;

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

    public function getTemplate(string $name)
    {
        return $this->templateHandler->getTemplateByName($name);
    }
}