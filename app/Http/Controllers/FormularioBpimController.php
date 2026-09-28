<?php

namespace App\Http\Controllers;

use App\Models\PresupuestoItem;
use App\Models\Proyecto;
use App\Models\SolicitudBpim;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Formulario público de solicitud BPIM (sin autenticación). Port del
 * FormularioBpimController de sistema-bpim.
 */
class FormularioBpimController extends Controller
{
    public function index(): View
    {
        $proyectos = Proyecto::with(['itemsCatalogo' => function ($query) {
            $query->where('activo', true)->orderBy('nombre');
        }])->orderBy('nombre_proyecto')->get();

        return view('formulario.bpim', compact('proyectos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'autorizacion_uso_datos' => 'required|accepted',
            'consecutivo' => 'required|string|max:100',
            'dependencia' => 'required|string|max:255',
            'nombre_solicitante' => 'required|string|max:255',
            'correo_solicitante' => 'required|email|ends_with:@puertoboyaca-boyaca.gov.co',
            'proyecto_id' => 'required|exists:proyectos,id',
            'nombre_proyecto' => 'required|string|max:255',
            'codigo_bpim' => 'required|string|max:100',
            'codigo_bpin' => 'nullable|string|max:100',
            'objeto' => 'required|string',
            'cdp' => 'required|string|max:100',
            'valor_cdp' => 'required|numeric|min:0',
            'valor_ep' => 'required|numeric|min:0',
            'objeto_gasto' => 'required|string|max:255',
            'fuente_recursos' => 'required|string|max:255',
            'mga' => 'required|string|max:100',
            'cpc' => 'required|string|max:100',
            'pdn_sector' => 'required|string|max:255',
            'pdn_programa' => 'required|string|max:255',
            'pdn_subprograma' => 'required|string|max:255',
            'pdm_sector' => 'required|string|max:255',
            'pdm_programa' => 'required|string|max:255',
            'pdm_producto' => 'required|string|max:255',
            'tiempo_contractual' => 'required|string|max:100',
            'codigo_acta_necesidad' => 'required|string|max:100',
            'nombre_elabora' => 'required|string|max:255',

            'presupuesto_items' => 'required|array|min:1',
            'presupuesto_items.*.item_catalogo_id' => 'required|exists:items_catalogo,id',
            'presupuesto_items.*.descripcion' => 'required|string',
            'presupuesto_items.*.valor_unitario' => 'required|numeric|min:0',
            'presupuesto_items.*.unidad' => 'required|string|max:50',
            'presupuesto_items.*.cantidad' => 'required|integer|min:1',
            'presupuesto_items.*.valor_total' => 'required|numeric|min:0',
        ], [
            'autorizacion_uso_datos.accepted' => 'Debe aceptar el uso de datos para continuar.',
            'correo_solicitante.ends_with' => 'El correo debe ser institucional (@puertoboyaca-boyaca.gov.co)',
            'presupuesto_items.required' => 'Debe agregar al menos un ítem de presupuesto.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()
                ->with('error', 'Por favor corrija los errores en el formulario.');
        }

        try {
            DB::beginTransaction();

            $solicitud = SolicitudBpim::create([
                'autorizacion_uso_datos' => true,
                'proyecto_id' => $request->proyecto_id,
                'consecutivo' => strtoupper($request->consecutivo),
                'dependencia' => strtoupper($request->dependencia),
                'nombre_solicitante' => strtoupper($request->nombre_solicitante),
                'correo_solicitante' => strtolower($request->correo_solicitante),
                'correo_notificacion' => $request->correo_notificacion ? strtolower($request->correo_notificacion) : null,
                'nombre_proyecto' => strtoupper($request->nombre_proyecto),
                'codigo_bpim' => strtoupper($request->codigo_bpim),
                'codigo_bpin' => $request->codigo_bpin ? strtoupper($request->codigo_bpin) : null,
                'objeto' => strtoupper($request->objeto),
                'cdp' => strtoupper($request->cdp),
                'valor_cdp' => $request->valor_cdp,
                'valor_ep' => $request->valor_ep,
                'objeto_gasto' => strtoupper($request->objeto_gasto),
                'fuente_recursos' => strtoupper($request->fuente_recursos),
                'otra_fuente_recurso' => $request->otra_fuente_recurso ? strtoupper($request->otra_fuente_recurso) : null,
                'mga' => strtoupper($request->mga),
                'cpc' => strtoupper($request->cpc),
                'pdn_sector' => strtoupper($request->pdn_sector),
                'pdn_programa' => strtoupper($request->pdn_programa),
                'pdn_subprograma' => strtoupper($request->pdn_subprograma),
                'pdm_sector' => strtoupper($request->pdm_sector),
                'pdm_programa' => strtoupper($request->pdm_programa),
                'pdm_producto' => strtoupper($request->pdm_producto),
                'tiempo_contractual' => $request->tiempo_contractual,
                'codigo_acta_necesidad' => strtoupper($request->codigo_acta_necesidad),
                'nombre_elabora' => strtoupper($request->nombre_elabora),
            ]);

            foreach ($request->presupuesto_items as $index => $item) {
                PresupuestoItem::create([
                    'solicitud_bpim_id' => $solicitud->id,
                    'item_catalogo_id' => $item['item_catalogo_id'],
                    'descripcion' => strtoupper($item['descripcion']),
                    'valor_unitario' => $item['valor_unitario'],
                    'unidad' => strtoupper($item['unidad']),
                    'cantidad' => $item['cantidad'],
                    'valor_total' => $item['valor_total'],
                    'orden' => $index + 1,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('bpim.formulario.exito', ['solicitud' => $solicitud->id])
                ->with('success', 'Solicitud enviada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return back()->withInput()
                ->with('error', 'Error al procesar la solicitud: ' . $e->getMessage());
        }
    }

    public function exito(int $solicitudId): View
    {
        $solicitud = SolicitudBpim::with('presupuestoItems')->findOrFail($solicitudId);

        return view('formulario.exito', compact('solicitud'));
    }
}
