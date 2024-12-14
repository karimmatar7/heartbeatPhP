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
<body class="antialiased bg-[#f7f2e9] flex flex-col justify-between min-h-screen py-6">
    <div class="w-full flex items-center px-6"></div>

    <div class="flex-1 flex flex-col items-center justify-center px-6">
        <h1 class="text-2xl font-bold text-blue-500 mb-6">
            {{ $language === 'nl' ? 'Resultaat' : ($language === 'fr' ? 'Résultat' : ($language === 'de' ? 'Ergebnis' : 'Result')) }}
        </h1>

        <div class="flex items-center justify-center gap-10 mb-6">
            <!-- Generated Photo Section -->
            <div>
                <img 
                    id="generated-photo" 
                    src="{{ $photoUrl ?? '' }}" 
                    alt="{{ $photoUrl ? 'Generated Photo' : 'Photo not found' }}" 
                    class="rounded-lg shadow-lg w-64 h-64 object-cover">
                <p id="photo-error" class="text-red-500 mt-2" style="display: none;">Photo not available. Please check the server logs.</p>
            </div>

            <!-- Arduino Chart Section -->
            <div class="w-72 h-72">
                <canvas id="arduinoChart"></canvas>
            </div>
        </div>

        <!-- QR Code -->
        <div class="mb-6">
            <img id="qr-code" src="" alt="QR Code to Download Photo" class="w-24 h-24" style="display: none;">
        </div>

        <!-- End Button -->
        <a href="{{ url('/') }}" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-6 rounded-lg mt-8">
            {{ $language === 'nl' ? 'Einde' : ($language === 'fr' ? 'Fin' : ($language === 'de' ? 'Ende' : 'End')) }}
        </a>

        <!-- Info Button -->
        <a href="{{ url('/show/' . $personId) }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-lg mt-8 ml-4">
            {{ $language === 'nl' ? 'Info' : ($language === 'fr' ? 'Info' : ($language === 'de' ? 'Info' : 'Info')) }}
        </a>
    </div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/mqtt/2.18.8/mqtt.min.js"></script>
<script>
    // === Chart.js Setup ===
    const ctx = document.getElementById('arduinoChart').getContext('2d');

    // Initial chart data (start with 0, 0 and update dynamically)
    const arduinoResults = {
        labels: ['Heartrate', 'Bodytemp'],
        data: [0, 0] // These values will be updated via MQTT
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

    // === MQTT Setup ===
    const hostIP = "192.168.0.21";
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
        // Message contains the result of the heartbeat sensor (Example: "84,103.94")
        const result = message.toString();
        console.log('Received message:', result);
        
        // Extract heart rate and body temperature from the message
        const [heartRate, bodyTemp] = result.split(',').map(Number); // Split into two numbers

        // Update the chart with new heart rate and body temperature
        arduinoResults.data[0] = heartRate; // Update heart rate
        arduinoResults.data[1] = bodyTemp;  // Update body temperature
        chart.update(); // Refresh the chart with the new data

        // Simultaneously generate the image while updating the chart
        generateImage(heartRate, bodyTemp);

        // Update the form fields with the new values
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
    });

    function generateImage(heartRate, bodyTemp) {
        // Send the updated values to Laravel to generate a new image asynchronously
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
