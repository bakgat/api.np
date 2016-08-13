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

$app->get('/', function () use ($app) {
    return phpinfo();
});

$app->group(['prefix' => 'users', 'namespace' => 'App\Http\Controllers\Identity'], function() use($app) {
    $app->post('/login', 'UserController@login');
});
$app->group(['prefix' => 'students', 'namespace' => 'App\Http\Controllers\Identity'], function () use ($app) {
    $app->get('/', 'StudentController@index');
    $app->get('/{id}', 'StudentController@show');
    $app->get('/{id}/groups', 'StudentController@allGroups');
    $app->get('/{id}/redicodi', 'StudentController@allRedicodi');
});

$app->group(['prefix' => 'groups', 'namespace' => 'App\Http\Controllers\Identity'], function () use ($app) {
    $app->get('/', 'GroupController@index');
    $app->get('{id}', 'GroupController@show');
    $app->get('/{id}/students', 'GroupController@allActiveStudents');
    $app->post('/', 'GroupController@store');
    $app->put('/{id}', 'GroupController@update');

    //$app->get('/{id}/evaluations', 'GroupController@indexEvaluations');
});

$app->group(['prefix' => 'majors', 'namespace' => 'App\Http\Controllers\Education'], function () use ($app) {
    $app->get('/', 'BranchController@indexMajors');
});
$app->group(['prefix' => 'branches', 'namespace' => 'App\Http\Controllers\Education'], function () use ($app) {
    $app->get('/', 'BranchController@index');
});

$app->group(['prefix'=>'evaluations', 'namespace' => 'App\Http\Controllers\Evaluation'], function() use($app) {
    $app->get('/', 'EvaluationController@index');
    $app->get('/{id}', 'EvaluationController@show');
});