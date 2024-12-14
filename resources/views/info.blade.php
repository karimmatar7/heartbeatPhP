<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Person Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">

    <!-- Page Header -->
    <div class="bg-blue-600 p-6">
        <h1 class="text-white text-3xl font-bold text-center">Person Details</h1>
    </div>

    <!-- Person Details Card -->
    <div class="container mx-auto mt-10 p-4">
        <div class="bg-white shadow-md rounded-lg p-6 max-w-lg mx-auto">
            
            <!-- Person Name -->
            <h2 class="text-2xl font-bold text-gray-700 text-center mb-6">{{ $person->name }}</h2>
            
            <div class="space-y-4">

                <!-- Age -->
                <div class="flex justify-between items-center border-b pb-3">
                    <span class="text-gray-600 font-medium">Age:</span>
                    <span class="text-gray-900">{{ $person->age }} years</span>
                </div>

                <!-- Body Temperature -->
                <div class="flex justify-between items-center border-b pb-3">
                    <span class="text-gray-600 font-medium">Body Temperature:</span>
                    <span class="text-gray-900">{{ $person->bodytemp ? $person->bodytemp . '°C' : 'N/A' }}</span>
                </div>

                <!-- Heart Rate -->
                <div class="flex justify-between items-center border-b pb-3">
                    <span class="text-gray-600 font-medium">Heart Rate:</span>
                    <span class="text-gray-900">{{ $person->heart_rate ? $person->heart_rate . ' bpm' : 'N/A' }}</span>
                </div>

                <!-- Back Button -->
                <div class="mt-6 text-center">
                <a href="{{ route('form.result', ['language' => $language, 'person_id' => $personId]) }}" 
   class="inline-block bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition">
    Back
</a>

                </div>
            </div>

        </div>
    </div>

</body>
</html>
