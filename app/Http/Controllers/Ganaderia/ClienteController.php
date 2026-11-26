<?php

namespace App\Http\Controllers\Ganaderia;

use App\Http\Controllers\Controller;
use App\Models\Ganaderia\AbonoVenta;
use App\Models\Ganaderia\Cliente;
use App\Models\Venta\VentaGanado;
use App\Services\Ganaderia\CobranzaService;
use App\Support\Csv;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function __construct(private CobranzaService $cobranza) {}

    public function index(Request $request): View
    {
        $clientes = Cliente::query()
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('nombre', 'like', "%{$request->q}%")
                ->orWhere('rfc', 'like', "%{$request->q}%")->orWhere('razon_social', 'like', "%{$request->q}%")))
            ->when(! $request->boolean('inactivos'), fn ($q) => $q->where('activo', true))
            ->withSum(['ventas as saldo' => fn ($q) => $q->where('estado', VentaGanado::ESTADO_COMPLETADA)], 'saldo_pendiente')
            ->withSum(['ventas as comprado' => fn ($q) => $q->where('estado', VentaGanado::ESTADO_COMPLETADA)], 'total_venta')
            ->orderBy('nombre')
            ->paginate(25)->withQueryString();

        return view('ganaderia.clientes.index', [
            'clientes'   => $clientes,
            'antiguedad' => $this->cobranza->antiguedadSaldos(),
            'porCobrar'  => (float) VentaGanado::where('estado', VentaGanado::ESTADO_COMPLETADA)->sum('saldo_pendiente'),
        ]);
    }

    public function create(): View
    {
        return view('ganaderia.clientes.form', ['cliente' => new Cliente(['activo' => true, 'dias_credito' => 0])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $cliente = Cliente::create($this->validar($request) + ['activo' => true]);

        return redirect()->route('clientes.show', $cliente)->with('success', "Cliente {$cliente->nombre} registrado.");
    }

    public function edit(Cliente $cliente): View
    {
        return view('ganaderia.clientes.form', ['cliente' => $cliente]);
    }

    public function update(Request $request, Cliente $cliente): RedirectResponse
    {
        $cliente->update($this->validar($request, $cliente) + ['activo' => $request->boolean('activo')]);

        return redirect()->route('clientes.show', $cliente)->with('success', 'Cliente actualizado.');
    }

    /** Estado de cuenta del cliente. */
    public function show(Request $request, Cliente $cliente)
    {
        $ventas = $cliente->ventas()->with(['abonos.usuario'])->latest('fecha_venta')->get();

        if ($request->input('exportar') === 'pdf') {
            return \App\Support\DocumentoPdf::crear('ganaderia.clientes.estado-cuenta-pdf', compact('cliente', 'ventas'))
                ->download('estado_cuenta_' . str($cliente->nombre)->slug('_') . '.pdf');
        }

        if ($request->input('exportar') === 'csv') {
            return Csv::descargar('estado_cuenta_' . str($cliente->nombre)->slug('_'),
                ['Fecha', 'Folio', 'Condición', 'Vence', 'Cabezas', 'Total', 'Pagado', 'Saldo', 'Estado'],
                $ventas->map(fn ($v) => [$v->fecha_venta->format('Y-m-d'), $v->folio_venta, $v->condicion_pago, $v->fecha_vencimiento?->format('Y-m-d'),
                    $v->total_cabezas, (float) $v->total_venta, $v->totalPagado(), (float) $v->saldo_pendiente, $v->estado]));
        }

        return view('ganaderia.clientes.show', compact('cliente', 'ventas'));
    }

    /** Registrar un abono (pago) sobre una venta. */
    public function abonar(Request $request, VentaGanado $venta): RedirectResponse
    {
        $data = $request->validate([
            'monto'      => ['required', 'numeric', 'min:0.01'],
            'fecha'      => ['required', 'date', 'before_or_equal:today'],
            'forma_pago' => ['required', Rule::in(AbonoVenta::FORMAS_PAGO)],
            'referencia' => ['nullable', 'string', 'max:100'],
            'notas'      => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->cobranza->registrarAbono($venta, $data);
        } catch (Exception $e) {
            return back()->withErrors(['monto' => $e->getMessage()])->withInput();
        }

        return back()->with('success', 'Abono de $ ' . number_format($data['monto'], 2) . " registrado en la venta {$venta->folio_venta}.");
    }

    public function anularAbono(Request $request, AbonoVenta $abono): RedirectResponse
    {
        $data = $request->validate(['motivo' => ['required', 'string', 'max:255']]);

        try {
            $this->cobranza->anularAbono($abono, $data['motivo']);
        } catch (Exception $e) {
            return back()->withErrors(['motivo' => $e->getMessage()]);
        }

        return back()->with('success', 'Abono anulado; el saldo de la venta se recalculó.');
    }

    /** Cuentas por cobrar: todas las ventas con saldo. */
    public function cobranza(Request $request)
    {
        $ventas = VentaGanado::with('cliente')->where('estado', VentaGanado::ESTADO_COMPLETADA)
            ->where('saldo_pendiente', '>', 0)->orderBy('fecha_vencimiento')->get();

        if ($request->input('exportar') === 'csv') {
            return Csv::descargar('cuentas_por_cobrar', ['Cliente', 'RFC', 'Folio', 'Fecha venta', 'Vence', 'Días vencida', 'Total', 'Saldo'],
                $ventas->map(fn ($v) => [$v->cliente->nombre ?? $v->cliente_nombre, $v->cliente->rfc ?? $v->cliente_identificacion, $v->folio_venta,
                    $v->fecha_venta->format('Y-m-d'), $v->fecha_vencimiento?->format('Y-m-d'),
                    $v->vencida() ? (int) $v->fecha_vencimiento->diffInDays(now()) : 0, (float) $v->total_venta, (float) $v->saldo_pendiente]));
        }

        return view('ganaderia.clientes.cobranza', [
            'ventas'     => $ventas,
            'antiguedad' => $this->cobranza->antiguedadSaldos(),
        ]);
    }

    private function validar(Request $request, ?Cliente $cliente = null): array
    {
        $request->merge(['rfc' => $request->filled('rfc') ? mb_strtoupper(trim($request->rfc)) : null]);

        $data = $request->validate([
            'nombre'         => ['required', 'string', 'max:150'],
            'razon_social'   => ['nullable', 'string', 'max:200'],
            'rfc'            => ['nullable', 'regex:/^([A-ZÑ&]{3,4})\d{6}([A-Z\d]{3})$/', Rule::unique('clientes', 'rfc')->ignore($cliente?->id)->whereNull('deleted_at')],
            'regimen_fiscal' => ['nullable', Rule::in(array_keys(Cliente::REGIMENES))],
            'uso_cfdi'       => ['nullable', 'string', 'max:5'],
            'codigo_postal'  => ['nullable', 'digits:5'],
            'telefono'       => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:150'],
            'direccion'      => ['nullable', 'string', 'max:500'],
            'contacto'       => ['nullable', 'string', 'max:150'],
            'limite_credito' => ['nullable', 'numeric', 'min:0'],
            'dias_credito'   => ['nullable', 'integer', 'min:0', 'max:365'],
            'notas'          => ['nullable', 'string', 'max:1000'],
        ], ['rfc.regex' => 'El RFC no tiene un formato válido (12 o 13 caracteres).', 'rfc.unique' => 'Ya existe un cliente con ese RFC.']);

        $data['limite_credito'] = $data['limite_credito'] ?? 0;
        $data['dias_credito'] = $data['dias_credito'] ?? 0;

        return $data;
    }
}
