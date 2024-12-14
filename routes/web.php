<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Person;
use App\Http\Controllers\PersonController;

/*
|---------------------------------------------------------------------------
| Web Routes
|---------------------------------------------------------------------------
*/

// Welcome Page
Route::get('/', function () {
    return view('welcome');
});

// Form: Language Selection
Route::get('/form/{language}', function ($language) {
    return view('form', ['language' => $language]);
})->name('form.language');

// Form: Submit Name
Route::post('/form/{language}/submit-name', function ($language) {
    $name = request('name');
    return redirect()->route('form.age', ['language' => $language, 'name' => $name]);
})->name('form.submit-name');

// Form: Age Input Page
Route::get('/form/{language}/age', function ($language) {
    $name = request('name');
    return view('age', ['language' => $language, 'name' => $name]);
})->name('form.age');

// Form: Submit Age
// Form: Submit Age
// Form: Submit Age Route
Route::post('/form/{language}/submit-age', function (Request $request, $language) {
    $request->validate([
        'name' => 'required|string|max:255',
        'age' => 'required|integer|min:0',
    ]);

    // Create a new person in the database
    $person = Person::create([
        'name' => $request->name,
        'age' => $request->age,
    ]);

    // Redirect to the sensor page, passing personId, name, and age
    return redirect()->route('form.sensor', [
        'language' => $language, 
        'person_id' => $person->id, 
        'name' => $request->name, 
        'age' => $request->age
    ]);
})->name('form.submit-age');


// Sensor Page
Route::get('/form/{language}/sensor/{personId}', function ($language, $personId) {
    // Retrieve the 'name' and 'age' from query parameters
    $name = request()->query('name');
    $age = request()->query('age');
    
    // Return the view, passing language, personId, name, and age
    return view('sensor', compact('language', 'personId', 'name', 'age'));
})->name('form.sensor');;


// Sensor Page with the id of the person from URL parameters
Route::get('/form/{language}/sensor/{person_id}', function ($language, $person_id) {
    $person = Person::find($person_id);
    if (!$person) {
        return redirect()->route('form.language', ['language' => $language])->withErrors('Person not found.');
    }

    return view('sensor', ['language' => $language, 'person' => $person]);
})->name('form.sensor');

// Form: Submit Sensor Data
Route::post('/form/{language}/submit-sensor/{person_id}', function (Request $request, $language, $person_id) {
    $name = $request->input('name');
    $age = $request->input('age');
    
    // Redirect to the jump page with person_id as URL parameter
    return redirect()->route('form.jump', ['language' => $language, 'person_id' => $person_id])
        ->with(['name' => $name, 'age' => $age]);
})->name('form.submit-sensor');

// Jump Instructions Page
Route::get('/form/{language}/jump/{person_id}', function ($language, $person_id) {
    $person = Person::find($person_id);
    
    if (!$person) {
        return redirect()->route('form.language', ['language' => $language])->withErrors('Person not found.');
    }

    return view('jump', ['language' => $language, 'personId' => $person_id]);
})->name('form.jump');


// Form: Result Page
Route::get('/form/{language}/result/{person_id}', function ($language, $person_id) {
    $person = Person::find($person_id);
    
    if (!$person) {
        return redirect()->route('form.language', ['language' => $language])->withErrors('Person not found.');
    }

    $heartrate = session('heartrate', 70); // Default to 70 if not set
    $bodytemp = session('bodytemp', 36.5); // Default to 36.5 if not set
    $time = now()->format('H:i:s');

    // Path for Python script execution
    $scriptPath = base_path('scripts/GenerateImage.py');
    $uniqueFilename = 'generated_image_' . time() . '.png';
    $outputPath = public_path($uniqueFilename);

    // Execute the Python script to generate the image
    $command = escapeshellcmd("python3 $scriptPath --heartrate $heartrate --bodytemp $bodytemp --time $time --output $outputPath");
    $output = [];
    $returnVar = 0;
    exec($command, $output, $returnVar);

    Log::info('Executing Python script', [
        'command' => $command,
        'output' => implode("\n", $output),
        'returnVar' => $returnVar,
    ]);

    if ($returnVar !== 0 || !file_exists($outputPath)) {
        Log::error('Image generation failed');
        return response()->json(['error' => 'Image generation failed'], 500);
    }

    $fileUrl = asset($uniqueFilename);
    $qrCodeUrl = base64_encode(QrCode::format('png')->size(200)->generate($fileUrl));

    return view('result', [
        'language' => $language,
        'photoUrl' => $fileUrl,
        'qrCodeUrl' => $qrCodeUrl,
        'person' => $person,
        'personId' => $person_id // Pass the person_id to the view

    ]);
})->name('form.result');

// Generate and Upload Photo
Route::post('/form/{language}/upload-photo/{person_id}', function (Request $request, $language, $person_id) {
    // Get dynamic values from the frontend (via AJAX or session)
    $heartrate = $request->input('heartrate'); // From Arduino data
    $bodytemp = $request->input('bodytemp');   // From Arduino data
    $time = now()->format('H:i:s'); // Current time

    // Path to the Python script
    $scriptPath = base_path('scripts/GenerateImage.py');
    $uniqueFilename = 'generated_image_' . time() . '.png'; // Unique filename for the image
    $outputPath = public_path($uniqueFilename);

    // Build the command to run the Python script
    $command = escapeshellcmd("python3 $scriptPath --heartrate $heartrate --bodytemp $bodytemp --time $time --output $outputPath");
    $output = [];
    $returnVar = 0;

    // Execute the command and capture output
    exec($command, $output, $returnVar);

    // Log the command execution details
    Log::info('Executing Python script', [
        'command' => $command,
        'output' => implode("\n", $output),
        'returnVar' => $returnVar,
    ]);

    // Check if the image was generated successfully
    if ($returnVar !== 0 || !file_exists($outputPath)) {
        Log::error('Image generation failed', [
            'output' => implode("\n", $output),
            'returnVar' => $returnVar,
            'outputPathExists' => file_exists($outputPath),
        ]);
        return response()->json(['error' => 'Image generation failed', 'details' => implode("\n", $output)], 500);
    }

    // Generate the public URL for the image
    $fileUrl = asset($uniqueFilename);
    Log::info('Generated file URL: ' . $fileUrl);

    // Store the URL in the session
    session(['photoUrl' => $fileUrl]);

    // Redirect to the result page with the photo URL and person_id
    return redirect()->route('form.result', [
        'language' => $language,
        'person_id' => $person_id,
    ])->with([
        'photoUrl' => $fileUrl,
    ]);
})->name('form.upload-photo');

// API Endpoint for receiving sensor data
Route::post('/api/sensor-data', function (Request $request) {
    $request->validate([
        'heartrate' => 'required|integer|min:30|max:200',
        'bodytemp' => 'required|numeric|min:30|max:45',
    ]);

    $heartrate = $request->input('heartrate');
    $bodytemp = $request->input('bodytemp');
    $time = now()->format('H:i:s');

    // Log the incoming data
    Log::info('Sensor data received:', [
        'heartrate' => $heartrate,
        'bodytemp' => $bodytemp,
        'time' => $time,
    ]);

    return response()->json(['message' => 'Data received successfully']);
});

// Show Person Info (Optional)
Route::get('{language}/show/{id}', function ($language, $id) {
    App::setLocale($language);  // Set locale based on the language passed
    
    $person = Person::find($id);
    if (!$person) {
        return response()->json(['message' => __('messages.person_not_found')], 404);
    }

    // Get necessary data
    $heartrate = $person->heart_rate;
    $bodytemp = $person->bodytemp;
    $time = now()->format('H:i:s');
    
    // Execute Python script for image generation
    $scriptPath = base_path('scripts/GenerateImage.py');
    $outputPath = public_path('generated_image.png');  // Save the image locally
    $command = escapeshellcmd("python3 $scriptPath --heartrate $heartrate --bodytemp $bodytemp --time $time --output $outputPath");
    exec($command, $output, $returnVar);

    if ($returnVar !== 0) {
        return response()->json(['error' => __('messages.image_generation_failed')], 500);
    }

    // Generate dynamic prompt
    $prompt = generate_dynamic_prompt($heartrate, $bodytemp, $time);
    $imageUrl = asset('generated_image.png');  // URL for the generated image

    return view('info', [
        'person' => $person,
        'generatedPrompt' => $prompt,
        'photoUrl' => $imageUrl,
        'language' => $language  // Pass the language to the view
    ]);
})->name('show');


function generate_dynamic_prompt($heartrate, $bodytemp, $time) {
    // Define your JSON-like structure in PHP
    $prompt_data = [
        'temperature' => [
            ["prompt" => "icy, crystalline textures with frosted white highlights", "parameters" => ["min" => 0, "max" => 35.0]],
            ["prompt" => "cool, pale blue gradients with delicate textures", "parameters" => ["min" => 35.0, "max" => 35.5]],
            // add the other temperature values...
        ],
        'heartrate' => [
            ["prompt" => "soft, slow-moving blue waves with gentle transitions", "parameters" => ["min" => 0, "max" => 40]],
            ["prompt" => "calm, flowing blue shapes with subtle pulsations", "parameters" => ["min" => 40, "max" => 50]],
            // add the other heartrate values...
        ],
        'timestamp' => [
            ["prompt" => "soft, pastel tones with delicate movements", "parameters" => ["min" => 0, "max" => 5]],
            ["prompt" => "calm, flowing gradients with subtle contrasts", "parameters" => ["min" => 5, "max" => 10]],
            // add the other timestamp values...
        ],
    ];

    // Function to get the appropriate prompt based on range
    function get_prompt_from_json($category, $value, $prompt_data) {
        foreach ($prompt_data[$category] as $item) {
            if ($item["parameters"]["min"] <= $value && $value < $item["parameters"]["max"]) {
                return $item["prompt"];
            }
        }
        return "No matching prompt found.";
    }

    // Generate dynamic prompt
    $hr_element = get_prompt_from_json("heartrate", $heartrate, $prompt_data);
    $temp_element = get_prompt_from_json("temperature", $bodytemp, $prompt_data);
    $minutes = (int)substr($time, 3, 2);  // Extract minutes from time (HH:MM:SS)
    $time_element = get_prompt_from_json("timestamp", $minutes, $prompt_data);

    $prompt = "An abstract composition featuring $hr_element, enhanced by $temp_element. and is completed with $time_element. The elements blend seamlessly to create a cohesive and evocative image.";
    
    return $prompt;
}


// Update Person Senses (Optional)
Route::post('/update-senses', [PersonController::class, 'updateSenses'])->name('update-senses');

Route::post('/form/generate-image', function (Request $request) {
    $heartrate = $request->input('heart_rate');  // Heart rate from Arduino
    $bodytemp = $request->input('bodytemp');     // Body temperature from Arduino
    $time = now()->format('H:i:s');               // Current time

    $scriptPath = base_path('scripts/GenerateImage.py');
    $uniqueFilename = 'generated_image_' . time() . '.png'; // Unique filename for the image
    $outputPath = public_path($uniqueFilename);

    // Build the command to run the Python script
    $command = escapeshellcmd("python3 $scriptPath --heartrate $heartrate --bodytemp $bodytemp --time $time --output $outputPath");
    $output = [];
    $returnVar = 0;

    // Execute the command and capture output
    exec($command, $output, $returnVar);

    // Log the command execution details
    Log::info('Executing Python script', [
        'command' => $command,
        'output' => implode("\n", $output),
        'returnVar' => $returnVar,
    ]);

    // Check if the image was generated successfully
    if ($returnVar !== 0 || !file_exists($outputPath)) {
        Log::error('Image generation failed', [
            'output' => implode("\n", $output),
            'returnVar' => $returnVar,
            'outputPathExists' => file_exists($outputPath),
        ]);
        return response()->json(['error' => 'Image generation failed', 'details' => implode("\n", $output)], 500);
    }

    // Generate the public URL for the image
    $fileUrl = asset($uniqueFilename);

    return response()->json(['success' => true, 'photoUrl' => $fileUrl]);
});
