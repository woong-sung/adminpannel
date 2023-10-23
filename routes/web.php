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

//Route::get('/', function () {
//    return view('welcome');
//});

Route::group([ 'as' => 'eldmin.' ], function () {
  // tools - menu builder
  // menu
  Route::group([ 'as' => 'menus.', 'prefix' => 'menus' ], function () {
    Route::post('/', [ \App\Http\Controllers\MenuController::class, 'store', 'as' => 'store' ]);
    Route::get('/', [ \App\Http\Controllers\MenuController::class, 'index', 'as' => 'index' ]);
    Route::patch('/{menu}', [ \App\Http\Controllers\MenuController::class, 'update', 'as' => 'update' ]);
    Route::delete('/{menu}', [ \App\Http\Controllers\MenuController::class, 'destroy', 'as' => 'delete' ]);
    Route::post('/{menu}/order', [ \App\Http\Controllers\MenuController::class, 'order', 'as' => 'order' ]);
  });
  // menu items
  Route::group([ 'as' => 'menu_items.', 'prefix' => 'menu-items' ], function () {
    Route::get('/', [ \App\Http\Controllers\MenuItemController::class, 'show', 'as' => 'show' ]);
    Route::post('/', [ \App\Http\Controllers\MenuItemController::class, 'store', 'as' => 'store' ]);
    Route::patch('/{menu_item}', [ \App\Http\Controllers\MenuItemController::class, 'update', 'as' => 'update' ]);
    Route::delete('/{menu_item}', [ \App\Http\Controllers\MenuItemController::class, 'destroy', 'as' => 'delete' ]);
  });

  // tools - compass
  Route::group([ 'as' => 'compass.', 'prefix' => 'compass' ], function () {
    Route::group([ 'as' => 'commands.', 'prefix' => 'commands' ], function () {
      Route::get('/', [ \App\Http\Controllers\CompassController::class, 'getCommands', 'as' => 'show' ]);
      Route::post('/', [ \App\Http\Controllers\CompassController::class, 'runCommand', 'as' => 'run' ]);
    });
    // logs
    Route::group([ 'as' => 'logs.', 'prefix' => 'logs' ], function () {
      Route::get('/', [ \App\Http\Controllers\CompassController::class, 'getLogs', 'as' => 'show' ]);
    });
  });
});