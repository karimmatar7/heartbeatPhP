<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Resultaat - {{ ucfirst($language) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="antialiased bg-[#f7f2e9] flex justify-center items-center min-h-screen py-4 px-4">
    <div class="flex flex-col items-center p-6 w-full max-w-5xl">
        <!-- Title and Person Details -->
        <div class="flex flex-col items-center text-center mb-4">
            <h1 class="text-2xl font-bold text-blue-500 mb-2">
                {{ $language === 'nl' ? 'Resultaat' : ($language === 'fr' ? 'Résultat' : ($language === 'de' ? 'Ergebnis' : 'Result')) }}
            </h1>
            <h2 class="text-lg font-bold">{{ $person->name }}</h2>
            <p class="font-semibold">
                {{ $language === 'nl' ? 'Leeftijd' : ($language === 'fr' ? 'Âge' : ($language === 'de' ? 'Alter' : 'Age')) }}: 
                <span class="text-gray-700 text-sm mt-2">{{ $person->age }} {{ $language === 'nl' ? 'jaar' : ($language === 'fr' ? 'ans' : ($language === 'de' ? 'Jahre' : 'years')) }}</span>
            </p>
            <p class="font-semibold">
                {{ $language === 'nl' ? 'Gegenereerde prompt' : ($language === 'fr' ? 'Invite générée' : ($language === 'de' ? 'Generierte Eingabeaufforderung' : 'Generated Prompt')) }}:
                <span class="text-gray-700 text-sm mt-2">{{ $generatedPrompt }}</span>
            </p>
        </div>

        <!-- Content Section -->
        <div class="grid grid-cols-2 gap-6 items-center justify-center">
            <!-- Generated Photo -->
            <div class="flex flex-col items-center">
                <img 
                    id="generated-photo" 
                    src="{{ $photoUrl ?? '' }}" 
                    alt="{{ $photoUrl ? 'Generated Photo' : 'Photo not found' }}" 
                    class="rounded-lg shadow-lg w-64 h-64 object-cover">
                <p id="photo-error" class="text-red-500 mt-2" style="display: none;">Photo not available. Please check the server logs.</p>
            </div>

            <!-- Arduino Chart -->
            <div class="w-64 h-64 relative">
                <!-- Loader -->
                <div id="chart-loader" class="absolute inset-0 flex items-center justify-center bg-gray-100">
                    <div class="loader"></div>
                </div>
                <!-- Chart -->
                <canvas id="arduinoChart" class="hidden"></canvas>
            </div>
        </div>

        <!-- QR Code Section -->
        <div class="flex flex-col items-center mt-6">
            <img 
                id="qr-code" 
                src="data:image/png;base64,{{ $qrCodeUrl }}" 
                alt="QR Code to Download Photo" 
                class="w-24 h-24"
                style="display: {{ $qrCodeUrl ? 'block' : 'none' }};">
            <p class="text-gray-700 text-sm mt-2">
                {{ $language === 'nl' ? 'Scan om de foto te downloaden' : 
                ($language === 'fr' ? 'Scannez pour télécharger la photo' : 
                ($language === 'de' ? 'Scannen, um das Foto herunterzuladen' : 
                'Scan to download the photo')) }}
            </p>
        </div>

        <!-- End Button -->
        <div class="flex justify-center mt-6">
            <a href="{{ url('/') }}" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-6 rounded-lg text-center text-sm">
                {{ $language === 'nl' ? 'Einde' : ($language === 'fr' ? 'Fin' : ($language === 'de' ? 'Ende' : 'End')) }}
            </a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mqtt/2.18.8/mqtt.min.js"></script>
    <script>
        // === Loader Style ===
        const style = document.createElement('style');
        style.innerHTML = `
            .loader {
                border: 4px solid #f3f3f3;
                border-top: 4px solid #3498db;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
        const language = "{{ $language }}"; // Language: 'nl', 'fr', 'de', or 'en'

// Translation mapping
const translations = {
    heartrate: {
        nl: 'Hartslag',
        fr: 'Fréquence cardiaque',
        de: 'Herzfrequenz',
        en: 'Heartrate'
    },
    bodytemp: {
        nl: 'Lichaamstemperatuur',
        fr: 'Température corporelle',
        de: 'Körpertemperatur',
        en: 'Body Temperature'
    }
};
        // === Chart.js Setup ===
        const ctx = document.getElementById('arduinoChart').getContext('2d');

        // Initial chart data
        const arduinoResults = {
        labels: [
            translations.heartrate[language] || translations.heartrate.en,
            translations.bodytemp[language] || translations.bodytemp.en
        ],
        data: [0, 0]
    };

        const chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: arduinoResults.labels,
                datasets: [{
                    label: 'Arduino Results',
                    data: arduinoResults.data,
                    backgroundColor: ['#FF6384', '#36A2EB'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                const label = tooltipItem.label || '';
                                const value = tooltipItem.raw || 0;
                                return `${label}: ${value}`;
                            }
                        }
                    }
                }
            }
        });

        // Show chart and hide loader
        function showChart() {
            document.getElementById('chart-loader').style.display = 'none';
            document.getElementById('arduinoChart').classList.remove('hidden');
        }

        // === MQTT Setup ===
        const hostIP = "192.168.0.95";
        const port = 9001;
        const client = mqtt.connect(`ws://${hostIP}:${port}`);

        client.on('connect', function() {
            console.log('Connected to MQTT broker.');
            client.subscribe('heartbeatresult', function(err) {
                if (!err) {
                    console.log('Subscribed to topic: heartbeatresult');
                } else {
                    console.error('Failed to subscribe to topic:', err);
                }
            });
        });

        client.on('message', function(topic, message) {
            const result = message.toString();
            console.log('Received message:', result);
            
            const [heartRate, bodyTemp] = result.split(',').map(Number);

            arduinoResults.data[0] = heartRate;
            arduinoResults.data[1] = bodyTemp;
            chart.update();

            // Show chart once data is received
            showChart();

            // === Update Senses and Generate Image ===
            updateSenses(heartRate, bodyTemp);
            generateImage(heartRate, bodyTemp);
        });

        // === Update Senses Function ===
        function updateSenses(heartRate, bodyTemp) {
            fetch('/update-senses', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    person_id: {{ $personId }},
                    heart_rate: heartRate,
                    bodytemp: bodyTemp
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message === 'Senses updated') {
                    console.log('Senses updated successfully.');
                } else {
                    console.error('Failed to update the senses.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // === Generate Image Function ===
        function generateImage(heartRate, bodyTemp) {
            fetch('/form/generate-image', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    heart_rate: heartRate,
                    bodytemp: bodyTemp
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the photo URL on the page **immediately** after image generation is successful
                    const photoUrl = data.photoUrl;
                    const photoElement = document.getElementById('generated-photo');
                    const qrCodeElement = document.getElementById('qr-code');
                    const photoErrorElement = document.getElementById('photo-error');

                    photoElement.src = photoUrl;
                    qrCodeElement.src = `data:image/png;base64,${data.qrCodeUrl}`;
                    qrCodeElement.style.display = 'block'; // Show the QR code

                    // Show the photo error text if no image URL is provided
                    if (!photoUrl) {
                        photoErrorElement.style.display = 'block';
                    } else {
                        photoErrorElement.style.display = 'none';
                    }
                } else {
                    console.error('Failed to generate the image.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    </script>
</body>
</html>
