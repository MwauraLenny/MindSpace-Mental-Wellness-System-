@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-green-600 dark:text-emerald-300']) }}>
        {{ $status }}
    </div>
@endif
