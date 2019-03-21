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
    $router->get('organisations', 'Organisation\OrganisationController@getOrganisation');
});

$router->group(['middleware' => 'auth'], function () use ($router)
{
    $router->get('/', function () use ($router) {
        return $router->app->version();
    });
    $router->get('projects', 'Projects\ProjectsController@getProjects');
    $router->get('projects/{id}', 'Projects\ProjectsController@getProject');
    $router->get('documents', 'Documents\DocumentsController@getDocuments');
    $router->get('roles/{id}', 'Roles\RolesController@getRole');

    $router->get('folders', 'Folders\FoldersController@getFolders');
    $router->get('folders/{id}', 'Folders\FoldersController@getFolder');

    $router->get('users', 'Users\UsersController@getUsers');
    $router->get('users/activate', 'Users\UsersController@getUserActivation');
    $router->get('users/{id}', 'Users\UsersController@getUser');
    $router->get('users/{id}/image', 'Users\UsersController@getUserImage');
    $router->post('users[/{id}]', 'Users\UsersController@postUser');

    $router->get('actions', 'Actions\ActionsController@getActions');
    $router->post('actions[/{id}]', 'Actions\ActionsController@createOrUpdateAction');

    $router->get('mail/activate/{id}', 'Mail\MailController@sendUserActivation');

    $router->group(['middleware' => 'admin'], function() use ($router)
    {
        $router->post('projects[/{id}]', 'Projects\ProjectsController@createOrUpdateProject');
        $router->delete('projects/{id}', 'Projects\ProjectsController@deleteProject');

        $router->post('documents[/{id}]', 'Documents\DocumentsController@postDocuments');
        $router->delete('documents/{id}', 'Documents\DocumentsController@deleteDocument');

        $router->post('folders', 'Folders\FoldersController@createFolder');
        $router->post('folders/{id}', 'Folders\FoldersController@postFolders');
        $router->delete('folders[/{id}]', 'Folders\FoldersController@deleteFolders');

        $router->delete('folders/{folderId}/documents/{documentId}', 'FoldersLinkDocumentsController@deleteFoldersLinkDocuments');

        $router->delete('actions[/{id}]', 'Actions\ActionsController@deleteAction');

        $router->delete('users/{id}', 'Users\UsersController@deleteUser');

    });
});



