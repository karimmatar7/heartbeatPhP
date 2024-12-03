<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Resultaat - {{ ucfirst($language) }}</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    <body class="antialiased bg-[#f7f2e9] flex flex-col justify-between min-h-screen py-6">
        <div class="w-full flex items-center px-6">
        </div>

        <div class="flex-1 flex flex-col items-center justify-center px-6">
            <h1 class="text-2xl font-bold text-blue-500 mb-6">
                {{ $language === 'nl' ? 'Resultaat' : ($language === 'fr' ? 'Résultat' : ($language === 'de' ? 'Ergebnis' : 'Result')) }}
            </h1>

            <div class="flex items-center justify-center gap-10 mb-6">
                <!-- Random Photo Section -->
                <div>
                    <img src="{{ $photoUrl }}" alt="Random Generated Photo" class="rounded-lg shadow-lg w-64 h-64 object-cover">
                </div>

                <div class="w-72 h-72">
                    <canvas id="arduinoChart"></canvas>
                </div>
            </div>

            <div class="mb-6">
                <img src="data:image/png;base64,{{ $qrCodeUrl }}" alt="QR Code to Download Photo" class="w-24 h-24">
            </div>

            <a href="{{ url('/') }}" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-6 rounded-lg mt-8">
                {{ $language === 'nl' ? 'Einde' : ($language === 'fr' ? 'Fin' : ($language === 'de' ? 'Ende' : 'End')) }}
            </a>
        </div>

        <script>
            const ctx = document.getElementById('arduinoChart').getContext('2d');

            const arduinoResults = {
                labels: ['Result 1', 'Result 2', 'Result 3'], 
                data: [30, 50, 20] 
            };

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: arduinoResults.labels,
                    datasets: [{
                        label: 'Arduino Results',
                        data: arduinoResults.data,
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
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
                                    return `${label}: ${value}%`;
                                }
                            }
                        }
                    }
                }
            });
        </script>
    </body>
</html>
