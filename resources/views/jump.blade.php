<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Photo Generation</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            #loading-text {
                opacity: 0;
                transition: opacity 1s ease-in-out;
            }
        </style>
    </head>
    <body class="antialiased bg-[#f7f2e9] flex flex-col items-center justify-center min-h-screen">
        <!-- Header -->
        <h1 class="text-3xl font-bold text-blue-500 mb-10">
            <span class="text-gray-800">he</span><span class="text-blue-500 font-light">ART</span><span class="text-gray-800">beat</span>
        </h1>

        <!-- Instructions -->
        <div class="text-center">
            <h2 id="loading-text" class="text-2xl font-semibold text-blue-600"></h2>
        </div>

        <!-- Progress Bar -->
        <div class="w-10/12 max-w-md mt-6 bg-gray-200 h-2 rounded-full overflow-hidden">
            <div id="progress-bar" class="bg-blue-500 h-full w-0 transition-all duration-1000 ease-in-out"></div>
        </div>

        <script>
    const instructions = [
        "{{ $language === 'nl' ? 'Je unieke foto wordt gegenereerd. Even geduld...' : ($language === 'fr' ? 'Votre photo unique est en cours de génération. Veuillez patienter...' : ($language === 'de' ? 'Ihr einzigartiges Foto wird generiert. Bitte warten...' : 'Your unique photo is being generated. Please wait...')) }}",
        "{{ $language === 'nl' ? 'Bijna klaar! Nog een ogenblik geduld...' : ($language === 'fr' ? 'Presque prêt ! Encore un instant...' : ($language === 'de' ? 'Fast fertig! Noch einen Moment...' : 'Almost ready! Just a moment more...')) }}"
    ];

    let index = 0;
    const loadingText = document.getElementById('loading-text');
    const progressBar = document.getElementById('progress-bar');

    const showInstruction = () => {
        if (index >= instructions.length) {
            // Redirect to the result page after the last instruction with the person's id
            window.location.href = "{{ route('form.result', ['language' => $language, 'person_id' => $personId]) }}";
            return;
        }

        // Set the instruction text
        loadingText.innerText = instructions[index];
        loadingText.style.opacity = 1;

        // Update progress bar
        const progress = ((index + 1) / instructions.length) * 100;
        progressBar.style.width = `${progress}%`;

        // Fade out the text after 8 seconds (5 seconds longer than before)
        setTimeout(() => {
            loadingText.style.opacity = 0;

            // Move to the next instruction
            index++;
            setTimeout(showInstruction, 500); // Wait for fade-out to complete
        }, 1000); // Extended time for each instruction
    };

    showInstruction();
</script>
    </body>
</html>
