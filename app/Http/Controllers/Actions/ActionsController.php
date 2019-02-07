<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 7-2-2019
 * Time: 23:36
 */

namespace App\Http\Controllers\Actions;

use App\Http\Controllers\ApiController;
use App\Http\Handlers\ActionsHandler;
use Illuminate\Http\Request;

class ActionsController extends ApiController
{
    /**
     * @var ActionsHandler
     */
    private $actionHandler;

    /**
     * ActionsController constructor.
     * @param ActionsHandler $actionHandler
     */
    public function __construct(ActionsHandler $actionHandler)
    {
        $this->actionHandler = $actionHandler;
    }

    public function getActions(Request $request)
    {

    }

}