<x-filament-panels::page>
    <x-filament-panels::form wire:submit="notify">
        {{$this->form}}
        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
        />
    </x-filament-panels::form>

    {!! $this->htmlRender() !!}

    @push('scripts')
        <script>
            document.getElementById('html-render').addEventListener('click', function () {
                alert('html-render click');
            });
        </script>
    @endpush

</x-filament-panels::page>
