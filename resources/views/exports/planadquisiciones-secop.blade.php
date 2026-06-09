<table>
    <thead>
        <tr>
            <th>Código UNSPSC (cada código separado por ;)</th>
            <th>Descripción</th>
            <th>Fecha estimada de inicio de proceso de selección (mes)</th>
            <th>Fecha estimada de presentación de ofertas (mes)</th>
            <th>Duración del contrato (número)</th>
            <th>Duración del contrato (intervalo: días, meses, años)</th>
            <th>Modalidad de selección</th>
            <th>Fuente de los recursos</th>
            <th>Valor total estimado</th>
            <th>Valor estimado en la vigencia actual</th>
            <th>¿Se requieren vigencias futuras?</th>
            <th>Estado de solicitud de vigencias futuras</th>
            <th>Unidad de contratación (referencia)</th>
            <th>Ubicación</th>
            <th>Nombre del responsable</th>
            <th>Teléfono del responsable</th>
            <th>Correo electrónico del responsable</th>
            <th>¿Debe cumplir con invertir mínimo el 30% de los recursos del presupuesto destinados a comprar alimentos, cumpliendo con lo establecido en la Ley 2046 de 2020, reglamentada por el Decreto 248 de 2021?</th>
            <th>¿El contrato incluye el suministro de bienes y servicios distintos a alimentos?</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($planadquisiciones as $planadquisicion)
            <tr>
                <td>
                    @foreach ($planadquisicion->productos as $item)
                        {{ $item->id }}{{ $loop->last && $planadquisicion->clases->isEmpty() ? '' : ';' }}
                    @endforeach
                    @foreach ($planadquisicion->clases as $clase)
                        {{ \Illuminate\Support\Str::endsWith($clase->id, '00') ? $clase->id : $clase->id . '00' }}{{ $loop->last ? '' : ';' }}
                    @endforeach
                </td>
                <td>{{ $planadquisicion->id_vigencia ?? '' }} - {{ $planadquisicion->descripcioncont }}</td>
                <td>{{ optional($planadquisicion->mese)->id }}</td>
                <td>{{ optional($planadquisicion->mese)->id ?? 'N/A' }}</td>
                <td>{{ $planadquisicion->duracont }}</td>
                <td>{{ optional($planadquisicion->intervalo)->codigo ?? '1' }}</td>
                <td>{{ optional($planadquisicion->modalidade)->codigo ?? 'N/A' }}</td>
                <td>{{ optional($planadquisicion->fuente)->codigo ?? 'N/A' }}</td>
                <td>{{ $planadquisicion->valorestimadocont }}</td>
                <td>{{ $planadquisicion->valorestimadovig }}</td>
                <td>{{ optional($planadquisicion->vigenfutura)->codigo }}</td>
                <td>{{ optional($planadquisicion->estadovigencia)->codigo }}</td>
                <td>{{ $planadquisicion->unidadContratacion ?? '' }}</td>
                <td>{{ $planadquisicion->ubicacion ?? 'CO-BOY-15572' }}</td>
                <td>{{ optional($planadquisicion->area)->nombre ?? optional($planadquisicion->dependencia)->nombre ?? 'N/A' }}</td>
                <td>{{ optional($planadquisicion->user)->telefono ?? '3103127401' }}</td>
                <td>{{ optional($planadquisicion->user)->email ?? 'N/A' }}</td>
                <td>{{ $planadquisicion->cumpleLey2046 ?? '0' }}</td>
                <td>{{ $planadquisicion->suministroBienes ?? '1' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
