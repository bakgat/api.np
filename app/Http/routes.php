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
$app->get('/info', function () {
    return phpinfo();
});
$app->get('/ntuid', function () {
    return \App\Domain\NtUid::generate(4);
});
/* ***************************************************
 * STUDENTS
 * **************************************************/
$app->group(['prefix' => 'students', 'namespace' => 'App\Http\Controllers\Identity'], function () use ($app) {
    $app->get('/', 'StudentController@index');
    $app->get('/make/avatars', 'StudentController@makeAvatars');
    $app->get('/pic/{id}', 'StudentController@getPic');
    $app->post('/pic/{id}', 'StudentController@postPic');
    $app->get('/{id}', 'StudentController@show');

    $app->post('/', 'StudentController@store');
    $app->put('/{id}', 'StudentController@update');

    //GROUPS
    $app->get('/{id}/groups', 'StudentController@allGroups');
    $app->post('/{id}/groups', 'StudentController@joinGroup');
    $app->put('/{id}/groups/{studentGroupId}', 'StudentController@updateGroup');

    //REDICODI
    $app->get('/{id}/redicodi', 'StudentController@allRedicodi');
    $app->post('/{id}/redicodi', 'StudentController@addRedicodi');
    $app->put('/{id}/redicodi/{studentRedicodiId}', 'StudentController@updateRedicodi');

    //IAC
    $app->get('/{id}/iac', 'StudentController@allIac');
    $app->post('/{id}/iac', 'StudentController@addIac');
    $app->put('/{id}/iac/{iacId}', 'StudentController@updateIac');
    $app->delete('/{id}/iac/{iacId}', 'StudentController@destroyIac');

});
/* ***************************************************
 * STAFF
 * **************************************************/
$app->group(['prefix' => 'staff', 'namespace' => 'App\Http\Controllers\Identity'], function () use ($app) {
    $app->get('/', 'StaffController@index');
    $app->get('/types', 'StaffController@allTypes');

    $app->get('/{id}', 'StaffController@show');
    $app->post('/', 'StaffController@store');
    $app->put('/{id}', 'StaffController@update');

    $app->post('/login', 'StaffController@login');

    $app->get('/{id}/groups', 'StaffController@allGroups');
    $app->post('/{id}/groups', 'StaffController@addGroup');
    $app->put('/{id}/groups/{staffGroupId}', 'StaffController@updateGroup');
    $app->delete('/{id}/groups/{groupId}', 'StaffController@removeGroup');

    $app->get('/{id}/roles', 'StaffController@allRoles');
    $app->post('/{id}/roles', 'StaffController@addRole');
    $app->put('/{id}/roles/{staffRoleId}', 'StaffController@updateRole');
    $app->delete('/{id}/roles/{staffRoleId}', 'StaffController@removeRole');

    $app->get('/{id}/actions', 'StaffController@actions');
});
/* ***************************************************
 * GROUPS
 * **************************************************/
$app->group(['prefix' => 'groups', 'namespace' => 'App\Http\Controllers\Identity'], function () use ($app) {
    $app->get('/', 'GroupController@index');
    $app->get('/{id}', 'GroupController@show');
    $app->get('/{id}/students', 'GroupController@allActiveStudents');
    $app->post('/', 'GroupController@store');
    $app->put('/{id}', 'GroupController@update');

    $app->get('/{id}/branches', 'GroupController@allBranches');
    //$app->get('/{id}/evaluations', 'GroupController@indexEvaluations');
});
/* ***************************************************
 * ROLES
 * **************************************************/
$app->group(['prefix' => 'roles', 'namespace' => 'App\Http\Controllers\Identity'], function () use ($app) {
    $app->get('/', 'RoleController@index');
});
/* ***************************************************
 * BRANCHES
 * **************************************************/

$app->group(['prefix' => 'branches', 'namespace' => 'App\Http\Controllers\Education'], function () use ($app) {
    $app->get('/', 'BranchController@index');
    $app->get('/majors', 'BranchController@indexMajors');
});

/* ***************************************************
 * EVALUATIONS
 * **************************************************/
$app->group(['prefix' => 'evaluations', 'namespace' => 'App\Http\Controllers\Evaluation'], function () use ($app) {
    $app->get('/', 'EvaluationController@index');
    $app->get('/summary', 'EvaluationController@getSummary');
    $app->get('/{id}', 'EvaluationController@show');

    $app->post('/', 'EvaluationController@store');
    $app->put('/{id}', 'EvaluationController@update');
    $app->delete('/{id}', 'EvaluationController@destroy');
});

/* ***************************************************
 * REPORTING
 * **************************************************/
$app->group(['prefix' => 'pdf/report', 'namespace' => 'App\Http\Controllers\Evaluation'], function () use ($app) {
    $app->get('/group/{groupId}', 'ReportController@pdfByGroup');
    $app->get('/student', 'ReportController@pdfByStudents');
    $app->get('/custom', 'ReportController@pdfCustom');
});
$app->group(['prefix' => 'pdf/pivot', 'namespace' => 'App\Http\Controllers\Evaluation'], function () use ($app) {
    $app->get('/group/{groupId}', 'ReportController@pivotByGroup');
    $app->get('/student', 'ReportController@pivotByStudents');
});
$app->group(['prefix' => 'report', 'namespace' => 'App\Http\Controllers\Evaluation'], function () use ($app) {
    $app->get('/group/{groupId}', 'ReportController@jsonByGroup');
    $app->get('/student/{studentId}', 'ReportController@jsonByStudent');
    $app->get('/custom', 'ReportController@jsonCustom');
});
/* ***************************************************
 * IACs
 * **************************************************/
$app->group(['prefix' => 'iac', 'namespace' => 'App\Http\Controllers\Evaluation'], function () use ($app) {
    $app->get('/', 'IacController@indexIacs');

    $app->get('/goals', 'IacController@indexGoals');
    $app->get('/goals/major/{majorId}', 'IacController@indexGoalsByMajor');
    $app->get('/goals/branch/{branchId}', 'IacController@indexGoalsByBranch');

    $app->get('/student/{studentId}', 'IacController@indexGoalsForStudent');

    $app->put('/{iacId}', 'IacController@updateIac');
    $app->delete('/{iacId}', 'IacController@destroy');
});

$app->group(['prefix' => 'analytics', 'namespace' => 'App\Http\Controllers\Events'], function () use ($app) {
    $app->get('/events/report', 'EventController@reportEvents');
});