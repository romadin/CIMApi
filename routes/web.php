<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(['middleware' => 'appToken'], function () use ($router) {
    $router->post('authenticate', 'Authenticate\Authenticate@login');
    $router->post('reset-password', 'Authenticate\Authenticate@reset');

    $router->get('organisations', 'Organisation\OrganisationController@getOrganisation');
    $router->post('organisations', 'Organisation\OrganisationController@createOrganisation');
    $router->post('organisations/{id}/site', 'Organisation\OrganisationController@updateOrganisationStrict');
    $router->get('organisations/{id}/image', 'Organisation\OrganisationController@getOrganisationImage');

    $router->post('modules', 'Module\ModuleController@joinModule');

    $router->get('organisations/test', 'Organisation\OrganisationController@getTest');
});

$router->group(['middleware' => 'auth'], function () use ($router)
{
    $router->get('/', function () use ($router) {
        return $router->app->version();
    });

    $router->get('projects', 'Projects\ProjectsController@getProjects');
    $router->get('projects/{id}', 'Projects\ProjectsController@getProject');
    $router->get('documents', 'Documents\DocumentsController@getDocuments');

    $router->get('documents/pdf', 'Documents\DocumentsController@createPdf');

    $router->get('documents/{id}', 'Documents\DocumentsController@getDocument');
    $router->get('roles/{id}', 'Roles\RolesController@getRole');

    $router->get('users', 'Users\UsersController@getUsers');
    $router->get('users/activate', 'Users\UsersController@getUserActivation');
    $router->get('users/{id}', 'Users\UsersController@getUser');
    $router->get('users/{id}/image', 'Users\UsersController@getUserImage');
    $router->post('users/{id}', 'Users\UsersController@editUser');

    $router->get('actions', 'Actions\ActionsController@getActions');
    $router->post('actions[/{id}]', 'Actions\ActionsController@createOrUpdateAction');

    $router->get('events', 'Events\EventsController@getEvents');

    $router->get('mail/activate/{id}', 'Mail\MailController@sendUserActivation');
    $router->get('mail/user/{id}/project/{projectId}', 'Mail\MailController@sendUserAddedToProject');

    $router->get('workFunctions', 'WorkFunctions\WorkFunctionsController@getWorkFunctions');
    $router->get('workFunctions/{id}', 'WorkFunctions\WorkFunctionsController@getWorkFunction');

    $router->get('chapters', 'Chapters\ChaptersController@getChapters');
    $router->get('chapters/{id}', 'Chapters\ChaptersController@getChapter');

    $router->get('companies', 'Companies\CompaniesController@getCompanies');
    $router->get('companies/{id}', 'Companies\CompaniesController@getCompany');

    $router->get('cache[/{id}]', 'Cache\CacheController@getCache');

    $router->group(['middleware' => 'admin'], function() use ($router)
    {
        $router->post('organisations/{id}', 'Organisation\OrganisationController@updateOrganisation');

        $router->post('projects[/{id}]', 'Projects\ProjectsController@createOrUpdateProject');
        $router->delete('projects/{id}', 'Projects\ProjectsController@deleteProject');

        $router->post('documents[/{id}]', 'Documents\DocumentsController@postDocuments');
        $router->delete('documents/{id}', 'Documents\DocumentsController@deleteDocument');
        $router->post('documents/{id}/image', 'Documents\DocumentsController@uploadImage');

        $router->delete('actions[/{id}]', 'Actions\ActionsController@deleteAction');

        $router->delete('users/{id}', 'Users\UsersController@deleteUser');
        $router->post('users', 'Users\UsersController@createUser');

        $router->post('events[/{id}]', 'Events\EventsController@postEvents');
        $router->delete('events/{id}', 'Events\EventsController@deleteEvents');

        $router->get('templates', 'Templates\TemplatesController@getTemplates');
        $router->get('templates/{id}', 'Templates\TemplatesController@getTemplate');
        $router->post('templates', 'Templates\TemplatesController@postTemplate');
        $router->post('templates/{id}', 'Templates\TemplatesController@updateTemplate');
        $router->delete('templates/{id}', 'Templates\TemplatesController@deleteTemplate');

        $router->post('workFunctions', 'WorkFunctions\WorkFunctionsController@postWorkFunction');
        $router->post('workFunctions/{id}', 'WorkFunctions\WorkFunctionsController@editWorkFunction');
        $router->delete('workFunctions/{id}', 'WorkFunctions\WorkFunctionsController@deleteWorkFunction');

        $router->post('chapters', 'Chapters\ChaptersController@postChapter');
        $router->post('chapters/{id}', 'Chapters\ChaptersController@editChapter');
        $router->delete('chapters/{id}', 'Chapters\ChaptersController@deleteChapter');

        $router->post('companies', 'Companies\CompaniesController@postCompany');
        $router->post('companies/{id}', 'Companies\CompaniesController@editCompany');
        $router->delete('companies/{id}', 'Companies\CompaniesController@deleteCompany');

        $router->post('cache/{id}', 'Cache\CacheController@updateCache');
        $router->post('cache', 'Cache\CacheController@postCache');
    });
});



