<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;
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
    return view('welcome');
});

Route::get('/upload', [UploadController::class, 'showUploadForm']);
Route::post('/upload', [UploadController::class, 'uploadImages']);
Route::post('/create-pdf', [UploadController::class, 'createPdf']);
Route::post('/save-s3', [UploadController::class, 'saveToS3']);
Route::get('/test-ocr', [UploadController::class, 'testOcr']);
Route::get('/save-s3', [UploadController::class, 'saveToS3']);
Route::get('/test-s3', function() {
    $s3 = Storage::disk('s3');
    $files = $s3->files();
    dd($files);
});