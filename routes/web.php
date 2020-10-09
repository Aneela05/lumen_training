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
   $router->get('verify', 'UserController@Verify');
   $router->post('forgot/{token}', 'UserController@forgot');
   $router->post('emailvalidity', 'UserController@checkvalidity');
   $router->post('reset', 'UserController@resetPassword');
   $router->get('/user/delete/{id}','AdminController@delete'); 
   $router->get('/users/Admin', 'ListController@show_admin'); 
   $router->get('/users/Normal', 'ListController@show_normal'); 
   $router->get('/user', 'ListController@show');
   $router->post('createpassword' ,'UserController@createpassword');
   $router->post('/user/create' ,'ListController@create');
   $router->get('/user/delete/{id}','ListController@delete');

});

