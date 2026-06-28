@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-400 dark:focus:border-amber-400 dark:focus:ring-amber-400']) }}>
