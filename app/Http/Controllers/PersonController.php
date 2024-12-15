<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PersonController extends Controller
{
    public function updateSenses( Request $request
    ) {
        $request->validate([
            'person_id' => 'required|exists:person,id',
            'bodytemp' => 'required|numeric',
            'heart_rate' => 'required|numeric',
        ]);
        $person = Person::find($request->person_id);
        if (!$person) {
            return response()->json(['message' => 'Person not found'], 404);
        }
        $person->heart_rate = $request->heart_rate;
        $person->bodytemp = $request->bodytemp;
        $person->save();
        return response()->json(['success' => true, 'message' => 'Senses updated'], 200);

    }

    public function showFormLanguage($language)
    {
        return view('form', ['language' => $language]);
    }

    public function submitName(Request $request, $language)
    {
        $name = $request->input('name');
        return redirect()->route('form.age', ['language' => $language, 'name' => $name]);
    }

    public function showAgeForm($language)
    {
        $name = request('name');
        return view('age', ['language' => $language, 'name' => $name]);
    }

    public function submitAge(Request $request, $language)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:0',
        ]);

        $person = Person::create([
            'name' => $request->name,
            'age' => $request->age,
        ]);

        return redirect()->route('form.sensor', [
            'language' => $language,
            'person_id' => $person->id,
            'name' => $request->name,
            'age' => $request->age,
        ]);
    }

    public function showSensorPage($language, $person_id)
    {
        $person = Person::find($person_id);
        if (!$person) {
            return redirect()->route('form.language', ['language' => $language])->withErrors('Person not found.');
        }
    
        return view('sensor', ['language' => $language, 'personId' => $person_id, 'person' => $person, 'name' => $person->name, 'age' => $person->age]);
    }
    

    public function submitSensorData(Request $request, $language, $person_id)
    {
        $name = $request->input('name');
        $age = $request->input('age');
        
        
        return redirect()->route('form.jump', ['language' => $language, 'person_id' => $person_id])
            ->with(['name' => $name, 'age' => $age]);
    }

    public function showJumpPage($language, $person_id)
    {
        $person = Person::find($person_id);
        if (!$person) {
            return redirect()->route('form.language', ['language' => $language])->withErrors('Person not found.');
        }

        return view('jump', ['language' => $language, 'personId' => $person_id]);
    }

    public function showResultPage(Request $request, $language, $person_id)
    {
        // Retrieve the person based on the provided person_id
        $person = Person::find($person_id);
        if (!$person) {
            return redirect()->route('form.language', ['language' => $language])
                             ->withErrors('Person not found.');
        }
    
        // Default values for heart rate and body temperature
        $heartrate = session('heartrate', 70);
        $bodytemp = session('bodytemp', 36.5);
        $time = now()->format('H:i:s');
    
        // Path to the Python script that generates the image
        $scriptPath = base_path('scripts/GenerateImage.py');
        
        // Generate a unique filename based on the current timestamp
        $uniqueFilename = 'generated_image_' . time() . '.png';
        $outputPath = public_path($uniqueFilename);
    
        // Command to execute the Python script with the heart rate, body temperature, and time
        $command = escapeshellcmd("python3 $scriptPath --heartrate $heartrate --bodytemp $bodytemp --time $time --output $outputPath");
        
        // Initialize variables to capture the output and return status of the Python script execution
        $output = [];
        $returnVar = 0;
        
        // Execute the Python script
        exec($command, $output, $returnVar);
    
        // Log the command and its output for debugging
        Log::info('Executing Python script', [
            'command' => $command,
            'output' => implode("\n", $output),
            'returnVar' => $returnVar,
        ]);
    
        // Check if the script executed successfully and the image was generated
        if ($returnVar !== 0 || !file_exists($outputPath)) {
            Log::error('Image generation failed', [
                'command' => $command,
                'output' => implode("\n", $output),
                'returnVar' => $returnVar,
                'outputPath' => $outputPath
            ]);
            return response()->json(['error' => 'Image generation failed'], 500);
        }
    
        // URL of the generated image
        $fileUrl = asset($uniqueFilename);
        
        // Generate the QR code for the image URL
        $qrCodeUrl = base64_encode(QrCode::format('png')->size(200)->generate($fileUrl));
    
        // Log the QR code URL for debugging purposes
        Log::info('Generated QR code URL', ['qrCodeUrl' => $qrCodeUrl]);
    
        // Return the result view with the relevant data
        return view('result', [
            'language' => $language,
            'photoUrl' => $fileUrl,  // URL for the generated image
            'qrCodeUrl' => $qrCodeUrl,  // Base64 encoded QR code for the image URL
            'person' => $person,
            'personId' => $person_id,
        ]);
    }
    
    public function show($language, $person_id)
    {
        // Retrieve the person from the database
        $person = Person::find($person_id);
        
        // If person not found, redirect back with an error
        if (!$person) {
            return redirect()->route('form.language', ['language' => $language])->withErrors('Person not found.');
        }
    
        // Get the heart rate and body temperature for generating the image
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
        $generatedPrompt = $this->generate_dynamic_prompt($heartrate, $bodytemp, $time);
    
        // Image URL for the generated image
        $imageUrl = asset('generated_image.png');  // URL for the generated image
    
        // Return a view and pass the person data, generated prompt, image URL, and language
        return view('info', [
            'person' => $person,
            'generatedPrompt' => $generatedPrompt,
            'photoUrl' => $imageUrl,
            'language' => $language,
            'photoUrl' => $person->generated_image_url
        ]);
    }
    
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
    
        $hr_element = get_prompt_from_json("heartrate", $heartrate, $prompt_data);
        $temp_element = get_prompt_from_json("temperature", $bodytemp, $prompt_data);
        $minutes = (int)substr($time, 3, 2);  // Extract minutes from time (HH:MM:SS)
        $time_element = get_prompt_from_json("timestamp", $minutes, $prompt_data);
    
        $prompt = "An abstract composition featuring $hr_element, enhanced by $temp_element. and is completed with $time_element. The elements blend seamlessly to create a cohesive and evocative image.";
        
        return $prompt;
    }
    
    
}
