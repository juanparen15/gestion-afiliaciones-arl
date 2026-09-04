{{-- Botón de guardar NATIVO de Filament, renderizado DENTRO del footer del wizard
     (último paso). Usa la acción de la página (create o edit) para conservar el
     cableado correcto de envío/validación. Va junto a "Anterior". --}}
@php
    $submitAction = method_exists($this, 'getCreateFormAction')
        ? $this->getCreateFormAction()
        : (method_exists($this, 'getSaveFormAction') ? $this->getSaveFormAction() : null);
@endphp

<div class="flex flex-wrap items-center gap-3">
    @if ($submitAction)
        {{ $submitAction }}
    @endif

    {{ $this->getCancelFormAction() }}
</div>
