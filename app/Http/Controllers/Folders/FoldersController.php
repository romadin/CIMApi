<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 22-1-2019
 * Time: 12:53
 */

namespace App\Http\Controllers\Folders;


use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB;

class FoldersController extends ApiController
{
    const FOLDERS_TABLE = 'folders';

    //@todo need a better way for templating
    const defaultTemplate = ['BIM-Team', 'BIM-Modelleur', 'BIM-Coördinator', 'BIM Regisseur', 'BIM Manager'];

    public function createFoldersTemplate(int $projectId, $template): void {
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