<img
    src="{{ asset('logo.png') }}"
    alt="{{ config('app.name', 'MindSpace') }} logo"
    loading="eager"
    decoding="async"
    {{ $attributes->merge(['class' => 'h-12 w-auto object-contain']) }}
/>
