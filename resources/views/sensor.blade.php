<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Sensor - {{ ucfirst($language) }}</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            #sensor-warning {
                display: none;
                color: red;
                margin-bottom: 10px;
                font-size: 0.9rem;
            }
        </style>
    </head>
    <body class="antialiased bg-[#f7f2e9] flex flex-col justify-between min-h-screen py-6">
        <div class="w-full flex items-center px-6">
            <a href="/" class="text-blue-500 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
        </div>

        <div class="flex-1 flex flex-col items-center justify-center">
            <h1 class="text-xl font-bold text-blue-500 mb-6">
                {{ $language === 'nl' ? 'Leg je hand op de scanner.' : ($language === 'fr' ? 'Placez votre main sur le capteur.' : ($language === 'de' ? 'Legen Sie Ihre Hand auf den Sensor.' : 'Put your hand on the scanner.')) }}
            </h1>

            <p id="sensor-warning" class="text-red-500 text-sm mb-4">
                {{ $language === 'nl' ? 'Je moet je hand op de scanner plaatsen om door te gaan!' : ($language === 'fr' ? 'Vous devez placer votre main sur le capteur pour continuer!' : ($language === 'de' ? 'Sie müssen Ihre Hand auf den Sensor legen, um fortzufahren!' : 'You must place your hand on the scanner to continue!')) }}
            </p>

            <img src="{{ asset('images/privacy.png') }}" alt="Sensor Image" class="mb-6 w-40 h-40 object-contain">

            <!-- Form -->
            <form id="sensor-form" method="POST" action="{{ route('form.submit-sensor', ['language' => $language, 'person_id' => $personId]) }}" class="w-full max-w-md text-center">
                @csrf
                <input type="hidden" name="name" value="{{ $name }}">
                <input type="hidden" name="age" value="{{ $age }}">

                <button
                    type="submit"
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 rounded-lg transition-all"
                    id="submit-button"
                    disabled
                >
                    {{ $language === 'nl' ? 'Doorgaan' : ($language === 'fr' ? 'Continuer' : ($language === 'de' ? 'Weiter' : 'Continue')) }}
                </button>
            </form>

        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/mqtt/2.18.8/mqtt.min.js"></script>

        <script>
            const form = document.getElementById('sensor-form');
            const submitButton = document.getElementById('submit-button');
            const warningMessage = document.getElementById('sensor-warning');

            form.addEventListener('mouseover', () => {
                submitButton.disabled = false;
                warningMessage.style.display = 'none'; 
            });

            form.addEventListener('submit', (event) => {
                if (submitButton.disabled) {
                    event.preventDefault();
                    warningMessage.style.display = 'block';
                }
            });



            // Form submission
            const hostIP = "192.168.0.95";
            const port = 9001;

            // Connect to the MQTT server
            var client = mqtt.connect('ws://' + hostIP + ':' + port);

            // On successful connection, subscribe to topics
            client.on('connect', function() {
                console.log("Connected to MQTT broker");
                client.subscribe('heartbeatcommence', function(err) {
                    if (!err) {
                        console.log("Subscribed to heartbeatcommence");
                    }
                });
            });

            // send a message to the topic 'heartbeatcommence' when submitting the form
            form.addEventListener('submit', (event) => {
    if (submitButton.disabled) {
        event.preventDefault();
        warningMessage.style.display = 'block';
    } else {
        event.preventDefault(); // Prevent the page from reloading immediately
        client.publish('heartbeatcommence', 'play', {}, () => {
            console.log('Message sent to topic heartbeatcommence');
            form.submit(); // Manually submit the form after the message is sent
        });
    }
});

            // .addEventListener('click', () => {
            //     client.publish('heartbeatcommence', 'play');
            //     console.log('Message sent to topic heartbeatcommence');
            // });
        </script>




        </div>


    </body>
</html>
