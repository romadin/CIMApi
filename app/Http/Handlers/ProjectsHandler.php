<?php
/**
 * Created by PhpStorm.
 * User: Romario
 * Date: 17-2-2019
 * Time: 10:24
 */

namespace App\Http\Handlers;


use App\Models\Project\Project;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ProjectsHandler
{
    const PROJECT_TABLE = 'projects';

    /**
     * @var CompaniesHandler
     */
    private $companiesHandler;

    public function __construct(CompaniesHandler $companiesHandler)
    {
        $this->companiesHandler = $companiesHandler;
    }

    /**
     * @param int $id
     * @return Project | Response
     */
    public function getProject(int $id)
    {
        try {
            $result = DB::table(self::PROJECT_TABLE)
                ->where('id', '=', $id)
                ->first();
            if ( $result === null ) {
                return response('Item does not exist', 404);
            }
        } catch (\Exception $e) {
            return \response('ProjectHandler: There is something wrong with the database connection',500);
        }

        return $this->makeProject($result);
    }

    public function getProjectsByOrganisation(int $organisationId)
    {
        try {
            $results = DB::table(self::PROJECT_TABLE)->where('organisationId', $organisationId)->get();

            if ( $results === null ) {
                return response('Item does not exist', 404);
            }
        } catch (\Exception $e) {
            return \response('ProjectHandler: There is something wrong with the database connection',500);
        }

        $projects = [];

        foreach ($results as $result) {
            array_push($projects, $this->makeProject($result));
        }

        return $projects;
    }

    /**
     * @param array $postData
     * @return Project|Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function postProject(array $postData)
    {
        try {
            $id = DB::table(self::PROJECT_TABLE)
                ->insertGetId($postData);
            if ( $id === null ) {
                return response('Item is not been made', 404);
            }
        } catch (\Exception $e) {
            return \response('ProjectHandler: There is something wrong with the database connection',500);
        }

        return $this->getProject($id);
    }

    /**
     * Make an project model from the given data.
     * @param $data
     * @return Project
     */
    public function makeProject($data)
    {
        $project = new Project(
            $data->id,
            $data->name,
            $data->organisationId
        );

        try {
            $companies = $this->companiesHandler->getCompaniesByWorkFunction($project);
        } catch (\Exception $e) {
            return response($e->getMessage());
        }

        $project->setCompanies($companies);

        return $project;
    }

}