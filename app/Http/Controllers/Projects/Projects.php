<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 8-1-2019
 * Time: 13:17
 */

namespace App\Http\Controllers\Projects;


use App\Http\Controllers\ApiController;
use App\Models\Project\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Projects extends ApiController
{
    const PROJECT_TABLE = 'projects';

    public function getProjects(Request $request)
    {
        $results = DB::table(self::PROJECT_TABLE)->get();

        $projects = [];

        foreach ($results as $result) {
            array_push($projects, new Project(
                $result->id,
                $result->name
            ));
        }

        return $this->getReturnValueArray($request, $projects);
    }

    public function createProject()
    {
        echo json_encode('project created');
    }
}