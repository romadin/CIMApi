<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 8-1-2019
 * Time: 13:17
 */

namespace App\Http\Controllers\Projects;


use App\Http\Controllers\Controller;

class Projects extends Controller
{
    public function getProjects()
    {
        echo json_encode(['projects' => 'foobar']);
    }

    public function createProject()
    {
        echo json_encode('project created');
    }
}