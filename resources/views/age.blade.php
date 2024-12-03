<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Form - {{ ucfirst($language) }}</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="antialiased bg-[#f7f2e9] flex flex-col justify-between min-h-screen py-6">
        <!-- Header -->
        <div class="w-full flex justify-between items-center px-6">
            <!-- Back Button -->
            <a href="/" class="text-blue-500 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div></div> <!-- Empty placeholder for alignment -->
        </div>

        <!-- Form Section -->
        <div class="flex-1 flex items-center justify-center">
            <form method="POST" action="{{ route('form.submit-age', ['language' => $language]) }}" class="w-full max-w-md text-center">
                @csrf
                <input type="hidden" name="name" value="{{ $name }}">
                
                <!-- Title -->
                <h2 class="text-2xl font-bold text-blue-500 mb-6">
                    {{ $language === 'nl' ? 'Vul je leeftijd in' : ($language === 'fr' ? 'Entrez votre âge' : ($language === 'de' ? 'Geben Sie Ihr Alter ein' : 'Enter your age')) }}
                </h2>
                <!-- Input Field -->
                <input 
                    type="number" 
                    id="age" 
                    name="age" 
                    placeholder="{{ $language === 'nl' ? 'Voer je leeftijd in' : ($language === 'fr' ? 'Entrez votre âge' : ($language === 'de' ? 'Geben Sie Ihr Alter ein' : 'Enter your age')) }}" 
                    class="w-full px-4 py-3 border border-blue-500 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg mb-6"
                    required
                >
                <!-- Submit Button -->
                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 rounded-lg transition-all">
                    {{ $language === 'nl' ? 'Doorgaan' : ($language === 'fr' ? 'Continuer' : ($language === 'de' ? 'Weiter' : 'Continue')) }}
                </button>
            </form>
        </div>

    </body>
</html>
