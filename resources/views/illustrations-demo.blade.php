@php
    $items = [
        'egg-hatch' => 'Egg hatching',
        'chick-growth' => 'Chick growth stages',
        'incubator' => 'Incubator temperature and humidity',
        'feeding' => 'Chickens feeding',
        'farm-house' => 'Poultry house with ventilation fan',
        'delivery' => 'Delivery by truck and plane',
        'profit-chart' => 'Growing profit chart',
        'vaccination' => 'Vaccination and protection',
    ];
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Illustrations</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    <main class="mx-auto max-w-7xl space-y-10 px-4 py-10">
        <section class="mx-auto max-w-2xl">
            <x-illustrations.hero-farm label="Rooster mascot over farm hills at sunrise"/>
        </section>
        <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($items as $name => $label)
                <figure class="card p-4">
                    <x-dynamic-component :component="'illustrations.'.$name" :label="$label" class="mx-auto max-h-56"/>
                    <figcaption class="mt-3 truncate text-center text-sm font-semibold">{{ $name }}</figcaption>
                </figure>
            @endforeach
        </section>
    </main>
</body>
</html>
