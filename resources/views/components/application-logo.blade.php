@php
    $logoPath = public_path('logo.png');
    $logoVersion = file_exists($logoPath) ? md5_file($logoPath) : time();
@endphp

<img
    src="{{ asset('logo.png') }}?v={{ $logoVersion }}"
    alt="MindSpace logo"
    loading="eager"
    decoding="async"
    {{ $attributes->merge(['class' => 'h-full w-auto object-contain']) }}
/>
