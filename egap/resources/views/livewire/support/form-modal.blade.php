<div class="egap-modal-content egap-modal-form w-full">
    <form wire:submit="{{ $submitMethod }}" class="egap-modal-form-inner">
        <div class="egap-modal-form-body">
            {{ $this->form }}
        </div>

        <div class="egap-modal-form-footer flex flex-col-reverse gap-3 border-t border-gray-200 pt-4 dark:border-white/10 sm:flex-row sm:justify-end">
            <x-filament::button
                type="button"
                color="gray"
                outlined
                wire:click="cancelar"
            >
                Cancelar
            </x-filament::button>

            <x-filament::button
                type="submit"
                :icon="$submitIcon"
            >
                {{ $submitLabel }}
            </x-filament::button>
        </div>
    </form>
</div>
