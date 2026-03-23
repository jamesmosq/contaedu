@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-cream-300 focus:border-forest-500 focus:ring-forest-500 rounded-xl shadow-sm']) }}>
