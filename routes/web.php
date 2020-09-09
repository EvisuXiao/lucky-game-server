<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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
    return 'Hello World';
});

$router->group(['prefix' => 'user'], function($r) {
    $r->get('info', 'UserController@list');
    $r->post('info', 'UserController@add');
    $r->put('info', 'UserController@update');
    $r->delete('info', 'UserController@delete');
});

$router->group(['prefix' => 'team'], function($r) {
    $r->get('info', 'TeamController@list');
    $r->post('info', 'TeamController@add');
    $r->put('info', 'TeamController@update');
    $r->delete('info', 'TeamController@delete');
});

$router->group(['prefix' => 'schedule'], function($r) {
    $r->get('info', 'ScheduleController@list');
    $r->post('info', 'ScheduleController@add');
    $r->delete('info', 'ScheduleController@delete');
    $r->post('random', 'scheduleController@random');
});

$router->group(['prefix' => 'game'], function($r) {
    $r->get('list', 'GameController@list');
    $r->post('list', 'GameController@bet');
});

$router->group(['prefix' => 'setting'], function($r) {
    $r->get('info', 'SettingController@show');
    $r->put('info', 'SettingController@update');
});

$router->group(['prefix' => 'test'], function($r) {
    $r->get('init', 'TestController@init');
});