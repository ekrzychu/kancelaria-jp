<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        class="space-y-2"
        x-data="{
            image: $wire.$entangle(@js($getStatePath())){{ $field->isLive() ? '.live' : '' }},
            pending: false,
            progress: 0,
            error: '',
            upload(event) {
                const file = event.target.files[0]
                if (!file) return
                this.error = ''
                this.progress = 0
                if (file.size > @js(config('media.max_kilobytes') * 1024)) {
                    this.error = 'Zdjęcie przekracza dozwolony rozmiar.'
                    event.target.value = ''
                    return
                }
                this.pending = true
                const form = new FormData()
                form.append('image', file)
                const request = new XMLHttpRequest()
                request.open('POST', @js($field->uploadUrl()))
                request.responseType = 'json'
                request.setRequestHeader('Accept', 'application/json')
                request.setRequestHeader('X-CSRF-TOKEN', @js(csrf_token()))
                request.upload.onprogress = e => { if (e.lengthComputable) this.progress = e.loaded / e.total * 100 }
                request.onload = () => {
                    if (request.status === 201 && request.response?.id) {
                        this.image = request.response.id
                    } else {
                        this.error = request.response?.errors?.image?.[0] ?? (request.status === 413
                            ? 'Zdjęcie przekracza dozwolony rozmiar.'
                            : 'Przesłanie nie powiodło się. Sprawdź plik lub zaloguj się ponownie.')
                    }
                }
                request.onerror = () => { this.error = 'Nie udało się przesłać zdjęcia.' }
                request.onloadend = () => { this.pending = false; event.target.value = '' }
                request.send(form)
            },
        }"
    >
        <template x-if="image">
            <img :src="@js($field->previewPrefix()) + encodeURIComponent(image)" alt="Podgląd zdjęcia"
                class="panel-image-preview">
        </template>
        <input id="{{ $getId() }}" type="file" accept="image/jpeg,image/png,image/webp" hidden
            x-ref="fileInput" :disabled="pending" x-on:change="upload($event)">
        <x-filament::button type="button" x-bind:disabled="pending" x-on:click="$refs.fileInput.click()">
            <span x-text="image ? 'Zastąp zdjęcie' : 'Wybierz zdjęcie'">Wybierz zdjęcie</span>
        </x-filament::button>
        <div x-show="pending" x-cloak role="status">
            <span>Przesyłanie zdjęcia</span>
            <progress max="100" :value="progress" aria-label="Postęp przesyłania"></progress>
        </div>
        <x-filament::button type="button" color="gray" x-show="image" x-cloak
            x-bind:disabled="pending" x-on:click="image = null; error = ''">
            Usuń zdjęcie
        </x-filament::button>
        <p x-show="error" x-cloak x-text="error" role="alert"></p>
    </div>
</x-dynamic-component>
