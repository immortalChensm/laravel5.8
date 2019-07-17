<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', function () {
//    return view('welcome');
//});
Route::middleware('auth')->namespace('Admin')->prefix('admin')->group(function (){

    Route::any('/testb', "Admin\TestController@index");
    Route::get('/testa/{name}/{age?}', "Admin\TestController@index")->name();
});


