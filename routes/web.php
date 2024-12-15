<?php
use App\Http\Controllers\PersonController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Language Selection Form
Route::get('/form/{language}', [PersonController::class, 'showFormLanguage'])->name('form.language');

// Submit Name Form
Route::post('/form/{language}/submit-name', [PersonController::class, 'submitName'])->name('form.submit-name');

// Age Input Form
Route::get('/form/{language}/age', [PersonController::class, 'showAgeForm'])->name('form.age');

// Submit Age
Route::post('/form/{language}/submit-age', [PersonController::class, 'submitAge'])->name('form.submit-age');

// Sensor Page
Route::get('/form/{language}/sensor/{person_id}', [PersonController::class, 'showSensorPage'])->name('form.sensor');

// Submit Sensor Data
Route::post('/form/{language}/submit-sensor/{person_id}', [PersonController::class, 'submitSensorData'])->name('form.submit-sensor');

// Jump Instructions Page
Route::get('/form/{language}/jump/{person_id}', [PersonController::class, 'showJumpPage'])->name('form.jump');

// Result Page
Route::get('/form/{language}/result/{person_id}', [PersonController::class, 'showResultPage'])->name('form.result');

// Upload Photo (From Arduino Data)
Route::post('/form/{language}/upload-photo/{person_id}', [PersonController::class, 'uploadPhoto'])->name('form.upload-photo');

// API Endpoint for Sensor Data
Route::post('/update-senses', [PersonController::class, 'updateSenses']);

// Show Person Info
Route::get('/{language}/show/{person_id}', [PersonController::class, 'show'])
    ->name('info');
    