<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>heARTbeat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .fade-text {
            animation: fadeEffect 2.5s ease-in-out forwards;
        }

        @keyframes fadeEffect {
            0% { opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { opacity: 0; }
        }
    </style>
</head>
<body class="bg-[#f7f2e9] flex items-center justify-center min-h-screen">
    <div class="px-8 py-12 text-center">
        <!-- Title -->
        <h1 class="text-5xl font-bold text-gray-900 mb-6">
            <span class="text-gray-800">he</span><span class="text-blue-500 font-light">ART</span><span class="text-gray-800">beat</span>
        </h1>

        <!-- Subtitle with fade effect -->
        <h2 id="subtitle" class="text-xl text-gray-600 font-medium"></h2>

        <!-- Language Buttons -->
        <div class="grid grid-cols-2 gap-6 mt-8">
            <a href="/form/nl" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-4 px-6 rounded-lg shadow-md">
                Nederlands
            </a>
            <a href="/form/fr" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-4 px-6 rounded-lg shadow-md">
                Français
            </a>
            <a href="/form/en" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-4 px-6 rounded-lg shadow-md">
                English
            </a>
            <a href="/form/de" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-4 px-6 rounded-lg shadow-md">
                Deutsch
            </a>
        </div>
    </div>

    <script>
        // Subtitle translations with the new sentence
        const translations = [
            "Art flows from the heart", // English
            "Kunst stroomt uit het hart",  // Dutch
            "L'art coule du cœur", // French
            "Die Kunst fließt aus dem Herzen" // German
        ];

        let currentIndex = 0; // Track the current translation index
        const subtitleElement = document.getElementById('subtitle');

        // Function to update the subtitle text and apply the fade effect
        function updateSubtitle() {
            subtitleElement.classList.remove('fade-text'); // Remove the animation class to restart the animation
            void subtitleElement.offsetWidth; // Trigger a reflow to restart the animation
            subtitleElement.textContent = translations[currentIndex]; // Update the text content
            subtitleElement.classList.add('fade-text'); // Apply the fade animation
            currentIndex = (currentIndex + 1) % translations.length; // Cycle through the translations
        }

        // Initial subtitle
        updateSubtitle();

        // Set interval to change the subtitle every 3 seconds
        setInterval(updateSubtitle, 3000);
    </script>
</body>
</html>
