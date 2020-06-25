<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('vignette');
});

Auth::routes();

Route::post('createUserWithTicket', 'UserController@createWithTicket');

Route::get('/home', 'HomeController@index')->name('home');
/* Admin routes */
Route::get('/admin/consultation', 'UserController@index');
Route::get('/admin/modify-user', 'UserController@edit');
/* Billeterie - Enregistremet user */
Route::get('/billeterie', 'BilleterieController@displayForm');

Route::get('/exposant/catalogue', 'ExposantController@displayCatalogue');

/* Exposants */
Route::get('/gestion', function () {
    return view('gestionTest');});

Route::get('exposant/ajouter-un-produit','ExposantController@displayFormAddProduct');

Route::get('/', function () {
    return view('home');
});
