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
    //@todo need a better way for templating
    const defaultTemplate = ['BIM-Team', 'BIM-Modelleur', 'BIM-Coördinator', 'BIM Regisseur', 'BIM Manager'];
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

    public function createFoldersTemplate(int $projectId, $template): void
    {
        if ($template === 'default') {
            $insertData = [];
            foreach (self::defaultTemplate as $folderName) {
                $row = [
                    'name' => $folderName,
                    'projectId' => $projectId,
                    'mainFolder' => $folderName =='BIM-Team' ? true : false
                ];
                array_push($insertData, $row);
            }
            DB::table(self::FOLDERS_TABLE)->insert($insertData);
        }
    }

}