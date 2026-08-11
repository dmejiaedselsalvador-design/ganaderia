<?php

namespace App\Http\Controllers;

use App\Models\Ganado\ProveedorGanado;
use App\Models\Ganado\FacturaGanado;
use App\Models\Ganado\Ganado;
use App\Models\Ganado\Adelanto;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AnimalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view('animals.index');
    }

    public function perfil()
    {
        return view('animals.perfil');
    }

    public function nuevoGanado()
    {
       $proveedores = ProveedorGanado::all();

        return view('compras.nuevoGanado',compact('proveedores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
 public function store(Request $request)
    {
        // 1. Validar los datos generales del formulario
        $request->validate([
            'supplier_id' => 'required|exists:proveedorGanado,id',
            'purchase_date' => 'required|date',
            'invoice_number' => 'nullable|string|max:255',
            'animals' => 'required|array|min:1',
            'animals.*.tag_number' => 'required|string|unique:ganado,areteID',
            'animals.*.gender' => 'required|in:Macho,Hembra',
            'animals.*.weight' => 'required|numeric|min:0',
            'animals.*.total' => 'required|numeric|min:0',
        ]);

        // Usamos una transacción para asegurar que si algo falla, no se guarde nada a medias
        DB::beginTransaction();

        try {
            // 2. Calcular el monto total del lote sumando cada animal
            $montoTotalLote = collect($request->animals)->sum('total');

            // 3. Crear el registro en facturaGanado (Nuestra cabecera de compra)
            $factura = FacturaGanado::create([
                'proveedorID' => $request->supplier_id,
                'fechaFactura' => $request->purchase_date,
                'numeroFactura' => $request->invoice_number,
                'montoTotal' => $montoTotalLote,
                'estado' => 'pendiente', // Se actualizará si se cubre con adelantos
                'notas' => 'Lote registrado mediante captura rápida web.',
            ]);

            // 4. Registrar cada animal individual en la tabla 'ganado'
            foreach ($request->animals as $animalData) {
                Ganado::create([
                    'facturaID' => $factura->id, // Vinculamos al lote/factura
                    'areteID' => $animalData['tag_number'],
                    'raza' => $animalData['breed'] ?? null,
                    'genero' => $animalData['gender'],
                    'pesoActual' => $animalData['weight'],
                    'ultimoPeso' => $animalData['weight'],
                    'precioCompra' => $animalData['total'], // O el precio unitario multiplicado
                    'fechaCompra' => $request->purchase_date,
                    'status' => 'Activo',
                    'notas' => $animalData['observation'] ?? null,
                ]);
            }

            // 5. LÓGICA DE CRUCE AUTOMÁTICO CON ADELANTOS (Opcional pero recomendado)
            // Buscamos si el proveedor tiene dinero disponible en sus adelantos
            $adelantosDisponibles = Adelanto::where('proveedor_id', $request->supplier_id)
                ->where('montoDisponible', '>', 0)
                ->orderBy('date', 'asc') // FIFO: Primero los adelantos más antiguos
                ->get();

            $saldoPendienteFactura = $montoTotalLote;

            foreach ($adelantosDisponibles as $adelanto) {
                if ($saldoPendienteFactura <= 0) break;

                // Cuánto le vamos a descontar a este adelanto
                $montoAUsar = min($adelanto->montoDisponible, $saldoPendienteFactura);

                // Actualizamos el adelanto
                $adelanto->montoDisponible -= $montoAUsar;
                if ($adelanto->montoDisponible <= 0) {
                    $adelanto->status = 'agotado';
                } else {
                    $adelanto->status = 'parcial';
                }
                $adelanto->save();

                // Registramos el cruce en la tabla pivote (si la implementaste)
                // AdelantoFactura::create([
                //     'adelanto_id' => $adelanto->id,
                //     'factura_id' => $factura->id,
                //     'montoAplicado' => $montoAUsar
                // ]);

                $saldoPendienteFactura -= $montoAUsar;
            }

            // Si el saldo pendiente de la factura bajó a 0, se marca como pagada
            if ($saldoPendienteFactura <= 0) {
                $factura->estado = 'pagada';
                $factura->save();
            }

            DB::commit();

            return redirect()->back()->with('success', '¡Lote de ganado registrado y cruzado con éxito!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Error al guardar el lote: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
