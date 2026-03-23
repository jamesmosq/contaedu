<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-5 py-2.5 bg-forest-800 border border-transparent rounded-xl font-semibold text-sm text-white tracking-wide hover:bg-forest-700 focus:bg-forest-700 active:bg-forest-900 focus:outline-none focus:ring-2 focus:ring-forest-500 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
