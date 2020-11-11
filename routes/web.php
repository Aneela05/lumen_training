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

$router->get('/', function () use ($router) {

    return $router->app->version();

});

$router->group(['prefix' => 'api'], function () use ($router) {

   $router->post('register', 'UserController@register');//
   $router->post('login', 'UserController@login');//
   $router->get('verify/{token}', 'UserController@Verify');
   $router->get('forgot', 'UserController@forgot');
   $router->post('emailvalidity', 'UserController@checkvalidity');
   $router->post('forgotpassword', 'UserController@forgotpassword');
   $router->post('reset', 'UserController@resetPassword');
   $router->get('/users/Admin', 'ListController@show_admin'); 
   $router->get('/users/Normal', 'ListController@show_normal'); 
   $router->get('/user', 'ListController@show');
   $router->get('/users/{id}','ListController@list');
   $router->get('createpassword' ,'UserController@createpassword');
   $router->post('/user/create' ,'ListController@create');
   $router->get('/user/delete/{id}','ListController@delete');
   $router->post('/filter', 'ListController@searchfilters');
   $router->get('/task' , 'TaskController@task');
   //-------------------------------------------------------
   $router->post('/addtask', 'TaskController@addtask');
   $router->get('/task/delete/{id}', 'TaskController@deletetask');
   $router->get('sendmail', 'TaskController@sendmail');
   $router->get('/delete', 'TaskController@taskdelete');
   $router->post('task/update','TaskController@taskupdate');
   $router->get('alltasks', 'TaskController@alltasks');
   $router->post('status', 'TaskController@statusupdate');
   $router->get('assignedtasks', 'TaskController@assignedtasks');
   $router->post('/searchtasks', 'TaskController@searchtasks');
   $router->get('/dashboard', 'TaskController@dashboard');
   $router->get('/gettingtasks', 'TaskController@gettingtasks');
});

