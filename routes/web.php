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

$router->post('authenticate', 'Authenticate\Authenticate@login');

$router->group(['middleware' => 'auth'], function () use ($router)
{
    $router->get('/', function () use ($router) {
        return $router->app->version();
    });
    $router->get('projects', 'Projects\ProjectsController@getProjects');
    $router->get('documents', 'Documents\DocumentsController@getDocuments');
    $router->get('roles/{id}', 'Roles\RolesController@getRole');

    $router->get('folders', 'Folders\FoldersController@getFolders');
    $router->get('folders/{id}', 'Folders\FoldersController@getFolder');

    $router->get('users', 'Users\UsersController@getUsers');
    $router->get('users/{id}', 'Users\UsersController@getUser');

    $router->group(['middleware' => 'admin'], function() use ($router)
    {
        $router->post('projects[/{id}]', 'Projects\ProjectsController@createOrUpdateProject');
        $router->delete('projects/{id}', 'Projects\ProjectsController@deleteProject');

        $router->post('users', 'Users\UsersController@postUser');
        $router->post('folders/{id}', 'Folders\FoldersController@postFolders');
        $router->post('documents[/{id}]', 'Documents\DocumentsController@postDocuments');
    });
});



