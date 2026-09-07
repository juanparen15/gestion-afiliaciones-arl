{{-- Botón de guardar NATIVO de Filament, renderizado DENTRO del footer del wizard
     (último paso). Usa la acción de la página (create o edit) para conservar el
     cableado correcto de envío/validación. Va junto a "Anterior".
     En la página "Ver" (ViewRecord) estas acciones no existen, por eso se
     protegen con method_exists y no se renderiza nada. --}}
@php
    $submitAction = method_exists($this, 'getCreateFormAction')
        ? $this->getCreateFormAction()
        : (method_exists($this, 'getSaveFormAction') ? $this->getSaveFormAction() : null);

    $cancelAction = method_exists($this, 'getCancelFormAction')
        ? $this->getCancelFormAction()
        : null;
@endphp

@if ($submitAction || $cancelAction)
    <div class="flex flex-wrap items-center gap-3">
        @if ($submitAction)
            {{ $submitAction }}
        @endif

        @if ($cancelAction)
            {{ $cancelAction }}
        @endif
    </div>
@endif
