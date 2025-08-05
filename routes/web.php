<?php

use App\Notifications\NewNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});

Route::get('/add', function () {
    return view('add');
});

Route::get('/sample-webpage', function () {
   return view('add');
});

Route::get('/chat', function () {
    return view('chat');
})->middleware('auth');

