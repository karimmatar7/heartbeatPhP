<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Form - {{ ucfirst($language) }}</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            .numpad-wrapper {
                display: flex;
                justify-content: center;
                align-items: center;
                margin-top: 20px;
                margin-bottom: 20px;
            }
            .numpad {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 12px;
                padding: 16px;
                background-color: #fff;
                border: 2px solid #e0e0e0;
                border-radius: 12px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                width: 300px;
            }
            .key {
                padding: 16px;
                text-align: center;
                font-size: 24px;
                font-weight: bold;
                background-color: #3b82f6;
                color: #ffffff;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                user-select: none;
                transition: background-color 0.2s, transform 0.1s;
            }
            .key:hover {
                background-color: #2563eb;
            }
            .key:active {
                background-color: #1d4ed8;
                transform: scale(0.95);
            }
            .key.zero {
                grid-column: span 2;
            }
            .key.backspace {
                background-color: #f87171;
            }
            .key.backspace:hover {
                background-color: #ef4444;
            }
            .key.backspace:active {
                background-color: #dc2626;
            }
        </style>
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
                    type="text" 
                    id="age" 
                    name="age" 
                    placeholder="{{ $language === 'nl' ? 'Voer je leeftijd in' : ($language === 'fr' ? 'Entrez votre âge' : ($language === 'de' ? 'Geben Sie Ihr Alter ein' : 'Enter your age')) }}" 
                    class="w-full px-4 py-3 border border-blue-500 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg mb-6"
                    readonly
                >
                <!-- On-Screen Numeric Keypad -->
                <div class="numpad-wrapper">
                    <div id="numpad" class="numpad"></div>
                </div>
                <!-- Submit Button -->
                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 rounded-lg transition-all">
                    {{ $language === 'nl' ? 'Doorgaan' : ($language === 'fr' ? 'Continuer' : ($language === 'de' ? 'Weiter' : 'Continue')) }}
                </button>
            </form>
        </div>

        <script>
            // Define the numpad layout
            const keys = [
                "1", "2", "3",
                "4", "5", "6",
                "7", "8", "9",
                "0", "BACKSPACE"
            ];

            // Get references to elements
            const numpadContainer = document.getElementById("numpad");
            const inputField = document.getElementById("age");

            // Create the numpad
            keys.forEach(key => {
                const keyElement = document.createElement("button");
                keyElement.textContent = key === "BACKSPACE" ? "⌫" : key;
                keyElement.classList.add("key");

                // Add specific classes for special keys
                if (key === "0") {
                    keyElement.classList.add("zero");
                } else if (key === "BACKSPACE") {
                    keyElement.classList.add("backspace");
                }

                // Add click event to handle input
                keyElement.addEventListener("click", (event) => {
                    event.preventDefault(); // Prevent form submission on button click
                    if (key === "BACKSPACE") {
                        inputField.value = inputField.value.slice(0, -1); // Remove last character
                    } else {
                        inputField.value += key; // Add key to input
                    }
                });

                // Append the key to the numpad container
                numpadContainer.appendChild(keyElement);
            });
        </script>
    </body>
</html>
