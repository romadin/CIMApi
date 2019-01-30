<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 22-1-2019
 * Time: 12:53
 */

namespace App\Http\Controllers\Folders;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use App\Http\Handlers\FoldersHandler;

class FoldersController extends ApiController
{
    const FOLDERS_TABLE = 'folders';

    /**
     * @var FoldersHandler
     */
    private $foldersHandler;

    public function __construct(FoldersHandler $foldersHandler)
    {
        $this->foldersHandler = $foldersHandler;
    }

    public function getFolders(Request $request)
    {
        if ( $request->input('projectId') === null ) {
            return response('The project id is missing', 404);
        }

        return $this->getReturnValueArray($request, $this->foldersHandler->getFoldersByProjectId($request->input('projectId')));
    }

    public function postFolders(Request $request, $id)
    {
        return $this->getReturnValueObject($request, $this->foldersHandler->editFolder($request->post(),$id));
    }
}
