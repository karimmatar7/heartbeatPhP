<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Person;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/form/{language}', function ($language) {
    return view('form', ['language' => $language]);
});

Route::post('/form/{language}/submit-name', function ($language) {
    $name = request('name');
    return redirect()->route('form.age', ['language' => $language, 'name' => $name]);
})->name('form.submit-name');

Route::get('/form/{language}/age', function ($language) {
    $name = request('name');
    return view('age', ['language' => $language, 'name' => $name]);
})->name('form.age');

Route::post('/form/{language}/submit-age', function (Request $request, $language) {
    // Validate the input
    $request->validate([
        'name' => 'required|string|max:255',
        'age' => 'required|integer|min:0',
    ]);

    // Store the data in the database
    Person::create([
        'name' => $request->name,
        'age' => $request->age,
    ]);

    // Redirect to the sensor page
    return redirect()->route('form.sensor', ['language' => $language]);
})->name('form.submit-age');

// View the people table
Route::get('/people', function () {
    $people = Person::all();
    return view('people', ['people' => $people]);
})->name('people.index');


Route::get('/form/{language}/sensor', function ($language) {
    $name = request('name');
    $age = request('age');
    return view('sensor', ['language' => $language, 'name' => $name, 'age' => $age]);
})->name('form.sensor');

Route::post('/form/{language}/submit-sensor', function ($language) {
    $name = request('name');
    $age = request('age');
    return view('jump', ['language' => $language, 'name' => $name, 'age' => $age]);
})->name('form.submit-sensor');


Route::get('/form/{language}/result', function ($language) {
    return view('result', ['language' => $language]);
})->name('form.result');

Route::post('/form/{language}/upload-photo', function (Request $request, $language) {
    // Validate and upload the photo
    $request->validate([
        'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // Store the photo
    $filePath = $request->file('photo')->store('public/photos');
    $fileUrl = url(Storage::url($filePath));

    // Generate the QR code with the photo URL
    $qrCodeUrl = base64_encode(QrCode::format('png')->size(200)->generate($fileUrl));

    return view('result', [
        'language' => $language,
        'photoUrl' => $fileUrl,
        'qrCodeUrl' => $qrCodeUrl,
    ]);
})->name('form.upload-photo');

Route::get('/form/{language}/result', function ($language) {
    // Generate a random photo URL
    $photoUrl = 'https://picsum.photos/300';

    // Generate a QR code with the photo URL
    $qrCodeUrl = base64_encode(QrCode::format('png')->size(200)->generate($photoUrl));

    return view('result', [
        'language' => $language,
        'photoUrl' => $photoUrl,
        'qrCodeUrl' => $qrCodeUrl,
    ]);
})->name('form.result');

