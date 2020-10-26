<?php

use Illuminate\Support\Facades\Route;


/*<----------------------------------------------------------------------------------------------------->*/
/* Autentication */
Route::post('login', 'AuthController@login');
Route::post('register', 'AuthController@register');

/* Authorization */
Route::get('/auth/profile', 'AuthController@bioProfile');
Route::post('/auth/logout', 'AuthController@logout');
Route::post('/refresh/token', 'AuthController@refresh');
