<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        $orders = Order::orderBy('id', 'desc')->get();
        return response()->json($orders, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cliente' => 'required|string|max:255',
            'producto' => 'required|string|max:255',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $total = round($request->cantidad * $request->precio_unitario, 2);
        if ($total <= 0) {
            return response()->json(['errors' => ['total' => ['El total debe ser mayor a 0.']]], 422);
        }

        $fecha = $request->fecha;
        if (is_string($fecha)) {
            $fecha = \Carbon\Carbon::parse($fecha)->toDateString();
        } else {
            $fecha = $fecha->format('Y-m-d');
        }

        $existe = Order::where('cliente', $request->cliente)
            ->whereDate('fecha', $fecha)
            ->whereIn('estado', ['Pendiente', 'Procesada'])
            ->exists();

        if ($existe) {
            return response()->json([
                'errors' => ['cliente' => ['El cliente no puede tener más de una orden el mismo día.']]
            ], 422);
        }

        $order = Order::create([
            'cliente' => $request->cliente,
            'producto' => $request->producto,
            'cantidad' => (int) $request->cantidad,
            'precio_unitario' => $request->precio_unitario,
            'fecha' => $fecha,
            'total' => $total,
            'estado' => 'Pendiente',
        ]);

        return response()->json($order, 201);
    }

    public function show(int $id): JsonResponse
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Orden no encontrada.'], 404);
        }
        return response()->json($order, 200);
    }

    public function cancel(int $id): JsonResponse
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Orden no encontrada.'], 404);
        }
        if ($order->estado === 'Procesada') {
            return response()->json([
                'message' => 'No se puede cancelar una orden que esté Procesada.'
            ], 422);
        }
        if ($order->estado === 'Cancelada') {
            return response()->json(['message' => 'La orden ya está cancelada.'], 422);
        }

        $order->update(['estado' => 'Cancelada']);
        return response()->json($order, 200);
    }
}
