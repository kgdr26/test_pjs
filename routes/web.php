<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [HomeController::class, 'home'])->name('index');
Route::get('home', [HomeController::class, 'home'])->name('home');
Route::post('listdatapenitipan', [HomeController::class, 'listdata'])->name('listdatapenitipan');
Route::post('upload_image', [HomeController::class, 'upload'])->name('upload_image');
Route::post('save_data', [HomeController::class, 'save'])->name('save_data');
Route::post('show_data', [HomeController::class, 'show'])->name('show_data');
Route::post('save_edit', [HomeController::class, 'edit'])->name('save_edit');
Route::post('save_delete', [HomeController::class, 'delete'])->name('save_delete');



