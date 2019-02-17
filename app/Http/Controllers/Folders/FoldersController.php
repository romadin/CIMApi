<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 22-1-2019
 * Time: 12:53
 */

namespace App\Http\Controllers\Folders;


use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Handlers\FoldersHandler;

class FoldersController extends ApiController
{
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

    public function getFolder(Request $request, $id)
    {
        return $this->getReturnValueObject($request, $this->foldersHandler->getFolderById($id));
    }

    public function postFolders(Request $request, $id)
    {
        return $this->getReturnValueObject($request, $this->foldersHandler->editFolder($request->post(),$id));
    }

    public function deleteFolders(Request $request, $id = null)
    {
        if ( $id ) {
            return $this->foldersHandler->deleteFolder($this->foldersHandler->getFolderById($id));
        }
        if ( ! empty($request->input('projectId'))) {
            return $this->foldersHandler->deleteFolders($request->input('projectId'));
        }
    }
}
