<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Person Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="antialiased bg-[#f7f2e9] flex flex-col justify-between min-h-screen py-6">
    <!-- Header -->
    <div class="w-full flex justify-between items-center px-6">
        <!-- Back Button -->
        <a href="{{ url($language . '/show/' . $person->id) }}" class="text-blue-500 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div></div> <!-- Empty placeholder for alignment -->
    </div>

    <!-- Person Details Section -->
    <div class="flex-1 flex items-center justify-center">
        <div class="w-full max-w-md text-center bg-white shadow-lg rounded-lg p-8">
            <!-- Person Name -->
            <h2 class="text-2xl font-bold text-blue-500 mb-4">{{ $person->name }}</h2>

            <!-- Age -->
            <div class="mb-4">
                <span class="text-gray-700 font-semibold">
                    {{ $language === 'nl' ? 'Leeftijd' : ($language === 'fr' ? 'Âge' : ($language === 'de' ? 'Alter' : 'Age')) }}:
                </span>
                <span class="text-gray-900">{{ $person->age }} {{ $language === 'nl' ? 'jaren' : ($language === 'fr' ? 'ans' : ($language === 'de' ? 'Jahre' : 'years')) }}</span>
            </div>

            <!-- Body Temperature -->
            <div class="mb-4">
                <span class="text-gray-700 font-semibold">
                    {{ $language === 'nl' ? 'Lichaamstemperatuur' : ($language === 'fr' ? 'Température corporelle' : ($language === 'de' ? 'Körpertemperatur' : 'Body Temperature')) }}:
                </span>
                <span class="text-gray-900">{{ $person->bodytemp ? $person->bodytemp . '°C' : ($language === 'nl' ? 'N/B' : ($language === 'fr' ? 'Non disponible' : ($language === 'de' ? 'Nicht verfügbar' : 'N/A'))) }}</span>
            </div>

            <!-- Heart Rate -->
            <div class="mb-4">
                <span class="text-gray-700 font-semibold">
                    {{ $language === 'nl' ? 'Hartslag' : ($language === 'fr' ? 'Fréquence cardiaque' : ($language === 'de' ? 'Herzfrequenz' : 'Heart Rate')) }}:
                </span>
                <span class="text-gray-900">{{ $person->heart_rate ? $person->heart_rate . ' bpm' : ($language === 'nl' ? 'N/B' : ($language === 'fr' ? 'Non disponible' : ($language === 'de' ? 'Nicht verfügbar' : 'N/A'))) }}</span>
            </div>

            <!-- Generated Prompt -->
            <div class="mb-6">
                <h3 class="text-xl font-semibold text-gray-700">
                    {{ $language === 'nl' ? 'Gegenereerde prompt' : ($language === 'fr' ? 'Invite générée' : ($language === 'de' ? 'Generierte Eingabeaufforderung' : 'Generated Prompt')) }}
                </h3>
                <p class="text-gray-900">{{ $generatedPrompt }}</p>
            </div>

            <!-- Info Button -->
            <a href="javascript:void(0)" onclick="window.history.back()" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-lg mt-8 ml-4">
    {{ $language === 'nl' ? 'Terug' : ($language === 'fr' ? 'Retour' : ($language === 'de' ? 'Zurück' : 'Back')) }}
</a>
        </div>
    </div>
</body>
</html>
