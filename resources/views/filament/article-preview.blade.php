<div x-data="{ mobile: false }">
    <div class="panel-preview-controls">
        <x-filament::button color="gray" type="button" x-on:click="mobile = false" x-bind:aria-pressed="!mobile">Komputer</x-filament::button>
        <x-filament::button color="gray" type="button" x-on:click="mobile = true" x-bind:aria-pressed="mobile">Telefon</x-filament::button>
        <x-filament::link :href="$standaloneUrl" target="_blank" rel="noopener">Otwórz w nowej karcie</x-filament::link>
    </div>
    <iframe class="panel-preview" x-bind:class="{ 'panel-preview-mobile': mobile }" src="{{ $url }}"
        sandbox="allow-scripts allow-same-origin" title="Podgląd zapisanej publikacji"></iframe>
</div>
