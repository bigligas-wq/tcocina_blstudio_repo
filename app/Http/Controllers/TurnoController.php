<?php

namespace App\Http\Controllers;

use App\Models\Microturno;
use App\Models\TurnoConfig;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TurnoController extends Controller
{
    /**
     * Obtener configuración actual de turnos
     */
    public function getConfig(): JsonResponse
    {
        $config = TurnoConfig::getCurrentConfig();

        return response()->json([
            'success' => true,
            'config' => [
                'hora_inicio' => $config->hora_inicio,
                'hora_fin' => $config->hora_fin,
                'duracion_microturno_minutos' => $config->duracion_microturno_minutos,
                'max_pedidos_por_microturno' => $config->max_pedidos_por_microturno,
            ]
        ]);
    }

    /**
     * Actualizar configuración de turnos
     */
    public function updateConfig(Request $request): JsonResponse
    {
        try {
            \Log::info('UpdateConfig called with data:', $request->all());

            $request->validate([
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
                'duracion_microturno_minutos' => 'required|integer|min:5|max:60',
                'max_pedidos_por_microturno' => 'required|integer|min:1|max:20',
            ]);

            \Log::info('Validation passed');

            $config = TurnoConfig::getCurrentConfig();
            \Log::info('Current config found:', ['id' => $config->id]);

            // Desactivar configuración anterior
            $config->update(['is_active' => false]);
            \Log::info('Previous config deactivated');

            // Crear nueva configuración
            $newConfig = TurnoConfig::create([
                'hora_inicio' => $request->hora_inicio . ':00',  // Agregar segundos
                'hora_fin' => $request->hora_fin . ':00',  // Agregar segundos
                'duracion_microturno_minutos' => $request->duracion_microturno_minutos,
                'max_pedidos_por_microturno' => $request->max_pedidos_por_microturno,
                'is_active' => true,
            ]);

            \Log::info('New config created:', ['id' => $newConfig->id]);

            return response()->json([
                'success' => true,
                'message' => 'Configuración de turnos actualizada exitosamente',
                'config' => $newConfig
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . implode(', ', collect($e->errors())->flatten()->toArray()),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating config:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener microturnos disponibles para una fecha
     */
    public function getDisponibles(Request $request): JsonResponse
    {
        $fecha = $request->get('fecha', Carbon::today()->format('Y-m-d'));

        // Obtener contenido del carrito si se proporciona
        $cartContent = $request->get('cart', []);
        $cartHamburguesas = 0;
        $cartAcompañamientos = 0;

        // Calcular productos del carrito si se proporciona
        \Log::info('Debug TurnoController - Contenido del carrito', [
            'cart_content' => $cartContent,
            'cart_count' => count($cartContent)
        ]);

        if (!empty($cartContent)) {
            foreach ($cartContent as $item) {
                \Log::info('Debug TurnoController - Procesando item del carrito', [
                    'item' => $item,
                    'has_category' => isset($item['category']),
                    'has_quantity' => isset($item['quantity']),
                    'category' => $item['category'] ?? 'NULL',
                    'quantity' => $item['quantity'] ?? 'NULL'
                ]);

                if (isset($item['category']) && isset($item['quantity'])) {
                    if (strtolower($item['category']) === 'hamburguesas') {
                        $cartHamburguesas += $item['quantity'];
                        \Log::info('Debug TurnoController - Sumando hamburguesa', [
                            'quantity' => $item['quantity'],
                            'total_hamburguesas' => $cartHamburguesas
                        ]);
                    } elseif (strtolower($item['category']) === 'acompañamientos') {
                        $cartAcompañamientos += $item['quantity'];
                        \Log::info('Debug TurnoController - Sumando acompañamiento', [
                            'quantity' => $item['quantity'],
                            'total_acompañamientos' => $cartAcompañamientos
                        ]);
                    }
                }
            }
        }

        \Log::info('Debug TurnoController - Totales del carrito', [
            'cart_hamburguesas' => $cartHamburguesas,
            'cart_acompañamientos' => $cartAcompañamientos
        ]);

        // Obtener TODOS los microturnos dinámicos para la fecha (disponibles y no disponibles)
        $microturnos = \App\Models\DynamicMicroturno::generarParaFecha($fecha);

        return response()->json([
            'success' => true,
            'microturnos' => $microturnos->map(function ($microturno) use ($cartHamburguesas, $cartAcompañamientos, $fecha) {
                $productosActivos = $microturno->getProductosActivos();
                $config = \App\Models\WeeklyTurnoConfig::getConfigForDay($microturno->getDayOfWeek());

                // Verificar si el turno ya pasó (solo aplica para hoy)
                $now = Carbon::now();
                $horaInicioTurno = Carbon::createFromTimeString($microturno->getHoraInicioAttribute());
                $isPast = ($fecha === $now->format('Y-m-d')) && $now->gte($horaInicioTurno);

                // Calcular si el carrito cabe en este microturno
                $hamburguesasDespues = $productosActivos['hamburguesas'] + $cartHamburguesas;
                $acompañamientosDespues = $productosActivos['acompañamientos'] + $cartAcompañamientos;

                $disponibleParaCarrito = !$isPast &&
                    $hamburguesasDespues <= $config->max_hamburguesas &&
                    $acompañamientosDespues <= $config->max_acompañamientos;

                return [
                    'sort_order' => $microturno->getSortOrderAttribute(),
                    'hora_inicio' => $microturno->getHoraInicioAttribute(),
                    'hora_fin' => $microturno->getHoraFinAttribute(),
                    'formatted_time' => $microturno->getFormattedTimeAttribute(),
                    'capacidad_maxima' => $microturno->getCapacidadMaximaAttribute(),
                    'pedidos_activos' => $microturno->getPedidosActivosAttribute(),
                    'capacidad_restante' => $microturno->getCapacidadRestanteAttribute(),
                    'is_disponible' => $microturno->getIsDisponibleAttribute(),
                    'is_past' => $isPast,
                    'disponible_para_carrito' => $disponibleParaCarrito,
                    'productos_activos' => [
                        'hamburguesas' => $productosActivos['hamburguesas'],
                        'acompañamientos' => $productosActivos['acompañamientos']
                    ],
                    'capacidad_config' => [
                        'max_hamburguesas' => $config->max_hamburguesas,
                        'max_acompañamientos' => $config->max_acompañamientos
                    ],
                    'cart_content' => [
                        'hamburguesas' => $cartHamburguesas,
                        'acompañamientos' => $cartAcompañamientos
                    ]
                ];
            })
        ]);
    }

    /**
     * Verificar disponibilidad de un microturno específico
     */
    public function verificarDisponibilidad(Request $request): JsonResponse
    {
        $request->validate([
            'microturno_id' => 'required|integer',
            'fecha' => 'required|date',
        ]);

        $fecha = $request->fecha;
        $microturnoId = $request->microturno_id;

        // Obtener microturnos dinámicos para la fecha
        $microturnos = \App\Models\DynamicMicroturno::generarParaFecha($fecha);
        $microturno = $microturnos->first(function ($m) use ($microturnoId) {
            return $m->getSortOrderAttribute() == $microturnoId;
        });

        if (!$microturno) {
            return response()->json([
                'success' => false,
                'error' => 'El microturno no existe para esta fecha.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'disponible' => $microturno->getIsDisponibleAttribute(),
            'capacidad_restante' => $microturno->getCapacidadRestanteAttribute(),
            'microturno' => [
                'id' => $microturno->getSortOrderAttribute(),
                'formatted_time' => $microturno->getFormattedTimeAttribute(),
                'capacidad_maxima' => $microturno->getCapacidadMaximaAttribute(),
                'pedidos_activos' => $microturno->getPedidosActivosAttribute(),
            ]
        ]);
    }

    /**
     * Obtener estadísticas de turnos para una fecha
     */
    public function getEstadisticas(Request $request): JsonResponse
    {
        $fecha = $request->get('fecha', Carbon::today()->format('Y-m-d'));

        $microturnos = \App\Models\DynamicMicroturno::generarParaFecha($fecha);
        $totalPedidos = $microturnos->sum(function ($m) {
            return $m->getPedidosActivosAttribute();
        });
        $totalCapacidad = $microturnos->sum(function ($m) {
            return $m->getCapacidadMaximaAttribute();
        });
        $microturnosDisponibles = $microturnos->filter(function ($m) {
            return $m->getIsDisponibleAttribute();
        })->count();
        $microturnosLlenos = $microturnos->filter(function ($m) {
            return !$m->getIsDisponibleAttribute();
        })->count();

        return response()->json([
            'success' => true,
            'estadisticas' => [
                'fecha' => $fecha,
                'total_microturnos' => $microturnos->count(),
                'microturnos_disponibles' => $microturnosDisponibles,
                'microturnos_llenos' => $microturnosLlenos,
                'total_pedidos' => $totalPedidos,
                'total_capacidad' => $totalCapacidad,
                'porcentaje_ocupacion' => $totalCapacidad > 0 ? round(($totalPedidos / $totalCapacidad) * 100, 2) : 0,
            ]
        ]);
    }

    /**
     * Generar microturnos para un rango de fechas
     */
    public function generarMicroturnos(Request $request): JsonResponse
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $fechaInicio = Carbon::parse($request->fecha_inicio);
        $fechaFin = Carbon::parse($request->fecha_fin);
        $config = TurnoConfig::getCurrentConfig();

        $microturnosCreados = 0;
        $fechas = [];

        while ($fechaInicio->lte($fechaFin)) {
            $fecha = $fechaInicio->format('Y-m-d');

            // Verificar si ya existen microturnos para esta fecha
            $existentes = Microturno::paraFecha($fecha)->count();
            if ($existentes === 0) {
                Microturno::crearParaFecha($fecha);
                $microturnosCreados++;
                $fechas[] = $fecha;
            }

            $fechaInicio->addDay();
        }

        return response()->json([
            'success' => true,
            'message' => 'Los microturnos ahora se calculan dinámicamente basándose en la configuración semanal. No es necesario generar microturnos manualmente.',
            'fechas_procesadas' => [],
            'total_fechas' => 0
        ]);
    }
}
