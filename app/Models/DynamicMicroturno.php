<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class DynamicMicroturno
{
    protected $fecha;
    protected $horaInicio;
    protected $horaFin;
    protected $duracionMinutos;
    protected $capacidadMaxima;
    protected $sortOrder;

    public function __construct($fecha, $horaInicio, $horaFin, $duracionMinutos, $capacidadMaxima, $sortOrder = 1)
    {
        $this->fecha = $fecha;
        $this->horaInicio = $horaInicio;
        $this->horaFin = $horaFin;
        $this->duracionMinutos = $duracionMinutos;
        $this->capacidadMaxima = $capacidadMaxima;
        $this->sortOrder = $sortOrder;
    }

    // Accessors dinámicos
    public function getFormattedTimeAttribute(): string
    {
        $inicio = Carbon::createFromTimeString($this->horaInicio);
        $fin = Carbon::createFromTimeString($this->horaFin);
        return $inicio->format('H:i') . ' - ' . $fin->format('H:i');
    }

    public function getCapacidadRestanteAttribute(): int
    {
        $productos = $this->getProductosActivos();
        $config = \App\Models\WeeklyTurnoConfig::getConfigForDay($this->getDayOfWeek());

        $hamburguesasRestantes = max(0, $config->max_hamburguesas - $productos['hamburguesas']);
        $acompañamientosRestantes = max(0, $config->max_acompañamientos - $productos['acompañamientos']);

        // Retornar el mínimo de los dos (el más restrictivo)
        return min($hamburguesasRestantes, $acompañamientosRestantes);
    }

    public function getPedidosActivosAttribute(): int
    {
        $productos = $this->getProductosActivos();
        return $productos['hamburguesas'] + $productos['acompañamientos'];
    }

    public function getIsDisponibleAttribute(): bool
    {
        // Si es hoy y la hora de inicio ya pasó, el turno no está disponible
        $now = Carbon::now();
        if ($this->fecha === $now->format('Y-m-d')) {
            $horaInicioTurno = Carbon::createFromTimeString($this->horaInicio);
            if ($now->gte($horaInicioTurno)) {
                return false;
            }
        }

        $productos = $this->getProductosActivos();
        $config = \App\Models\WeeklyTurnoConfig::getConfigForDay($this->getDayOfWeek());

        return $productos['hamburguesas'] < $config->max_hamburguesas &&
            $productos['acompañamientos'] < $config->max_acompañamientos;
    }

    // Método para obtener el día de la semana
    public function getDayOfWeek(): string
    {
        return strtolower(Carbon::parse($this->fecha)->format('l'));
    }

    // Método para obtener productos activos de este microturno
    public function getProductosActivos(): array
    {
        $horaInicio = Carbon::createFromTimeString($this->horaInicio);
        $horaFin = Carbon::createFromTimeString($this->horaFin);

        $hamburguesas = 0;
        $acompañamientos = 0;

        // Debug: Log de información
        \Log::info('Debug getProductosActivos', [
            'fecha' => $this->fecha,
            'horaInicio' => $this->horaInicio,
            'horaFin' => $this->horaFin,
            'sortOrder' => $this->sortOrder
        ]);

        // Debug específico para sortOrder 1
        if ($this->sortOrder <= 3) {
            \Log::info('Debug ESPECÍFICO para Microturno 1', [
                'fecha' => $this->fecha,
                'horaInicio' => $this->horaInicio,
                'horaFin' => $this->horaFin,
                'sortOrder' => $this->sortOrder
            ]);
        }

        // Obtener pedidos que coinciden por sort_order
        $orders = Order::whereDate('created_at', $this->fecha)
            ->whereIn('status', Order::ACTIVE_STATUSES)
            ->where('microturno_sort_order', $this->sortOrder)
            ->with('items.product.category')
            ->get();

        // Debug: Log de pedidos encontrados
        \Log::info('Pedidos encontrados', [
            'count' => $orders->count(),
            'orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                    'microturno_sort_order' => $order->microturno_sort_order,
                    'items_count' => $order->items->count(),
                    'items_details' => $order->items->map(function ($item) {
                        return [
                            'product_name' => $item->product->name,
                            'category' => $item->product->category->name,
                            'quantity' => $item->quantity
                        ];
                    })
                ];
            })
        ]);

        // Debug específico para sortOrder 1
        if ($this->sortOrder <= 3) {
            // Debug: Ver TODOS los pedidos del día (sin filtros)
            $todosLosPedidos = Order::whereDate('created_at', $this->fecha)->get();

            \Log::info('Debug ESPECÍFICO - TODOS los pedidos del día', [
                'fecha' => $this->fecha,
                'count_total' => $todosLosPedidos->count(),
                'pedidos' => $todosLosPedidos->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'created_at' => $order->created_at,
                        'microturno_sort_order' => $order->microturno_sort_order
                    ];
                })
            ]);

            \Log::info('Debug ESPECÍFICO - Pedidos encontrados para Microturno 1', [
                'count' => $orders->count(),
                'orders' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'created_at' => $order->created_at,
                        'microturno_sort_order' => $order->microturno_sort_order,
                        'items' => $order->items->map(function ($item) {
                            return [
                                'product_name' => $item->product->name,
                                'category' => $item->product->category->name,
                                'quantity' => $item->quantity
                            ];
                        })
                    ];
                })
            ]);
        }

        // Contar productos por categoría
        \Log::info('Debug - Iniciando conteo de productos', [
            'orders_count' => $orders->count(),
            'hamburguesas_antes' => $hamburguesas,
            'acompañamientos_antes' => $acompañamientos
        ]);

        foreach ($orders as $order) {
            \Log::info('Debug - Procesando order', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'items_count' => $order->items->count()
            ]);

            foreach ($order->items as $item) {
                \Log::info('Debug - Procesando item', [
                    'item_id' => $item->id,
                    'product_name' => $item->product->name,
                    'category_name' => $item->product->category->name,
                    'quantity' => $item->quantity,
                    'is_hamburguesas' => strtolower($item->product->category->name) === 'hamburguesas',
                    'is_acompañamientos' => strtolower($item->product->category->name) === 'acompañamientos'
                ]);

                if (strtolower($item->product->category->name) === 'hamburguesas') {
                    $hamburguesas += $item->quantity;
                    \Log::info('Debug - Sumando hamburguesas', [
                        'quantity' => $item->quantity,
                        'total_hamburguesas' => $hamburguesas
                    ]);
                } elseif (strtolower($item->product->category->name) === 'acompañamientos') {
                    $acompañamientos += $item->quantity;
                    \Log::info('Debug - Sumando acompañamientos', [
                        'quantity' => $item->quantity,
                        'total_acompañamientos' => $acompañamientos
                    ]);
                }
            }
        }

        // Debug: Log del resultado
        \Log::info('Resultado getProductosActivos', [
            'hamburguesas' => $hamburguesas,
            'acompañamientos' => $acompañamientos
        ]);

        return [
            'hamburguesas' => $hamburguesas,
            'acompañamientos' => $acompañamientos
        ];
    }

    // Método para verificar si un pedido pertenece a este microturno
    public function contienePedido(Order $order): bool
    {
        // Si el pedido tiene microturno_sort_order, verificar por sort_order
        if ($order->microturno_sort_order) {
            return $order->microturno_sort_order == $this->sortOrder;
        }

        // Si no, verificar por hora de creación
        $horaInicio = Carbon::createFromTimeString($this->horaInicio);
        $horaFin = Carbon::createFromTimeString($this->horaFin);
        $horaPedido = Carbon::parse($order->created_at);

        return $horaPedido->between($horaInicio, $horaFin);
    }

    // Método estático para generar microturnos dinámicos para una fecha
    public static function generarParaFecha($fecha): Collection
    {
        $dayOfWeek = strtolower(Carbon::parse($fecha)->format('l'));
        $config = WeeklyTurnoConfig::getConfigForDay($dayOfWeek);

        if (!$config || !$config->is_enabled) {
            return collect();
        }

        $microturnos = collect();
        $horaInicio = Carbon::createFromTimeString($config->hora_inicio);
        $horaFin = Carbon::createFromTimeString($config->hora_fin);
        $currentTime = $horaInicio->copy();
        $sortOrder = 1;

        while ($currentTime->lt($horaFin)) {
            $horaInicioMicroturno = $currentTime->copy();
            $horaFinMicroturno = $currentTime->copy()->addMinutes($config->duracion_microturno_minutos);

            // Si el microturno se extiende más allá del horario de fin, ajustar
            if ($horaFinMicroturno->gt($horaFin)) {
                $horaFinMicroturno = $horaFin->copy();
            }

            $microturno = new self(
                $fecha,
                $horaInicioMicroturno->format('H:i:s'),
                $horaFinMicroturno->format('H:i:s'),
                $config->duracion_microturno_minutos,
                $config->max_pedidos_por_microturno,
                $sortOrder
            );

            $microturnos->push($microturno);
            $currentTime->addMinutes($config->duracion_microturno_minutos);
            $sortOrder++;
        }

        return $microturnos;
    }

    // Método para obtener microturnos disponibles
    public static function getDisponiblesParaFecha($fecha): Collection
    {
        return self::generarParaFecha($fecha)->filter(function ($microturno) {
            return $microturno->getIsDisponibleAttribute();
        });
    }

    // Método para encontrar el microturno correspondiente a un pedido
    public static function encontrarParaPedido(Order $order): ?self
    {
        $fecha = $order->created_at->format('Y-m-d');
        $microturnos = self::generarParaFecha($fecha);

        return $microturnos->first(function ($microturno) use ($order) {
            return $microturno->contienePedido($order);
        });
    }

    // Getters para compatibilidad
    public function getFechaAttribute(): string
    {
        return $this->fecha;
    }

    public function getHoraInicioAttribute(): string
    {
        return $this->horaInicio;
    }

    public function getHoraFinAttribute(): string
    {
        return $this->horaFin;
    }

    public function getCapacidadMaximaAttribute(): int
    {
        return $this->capacidadMaxima;
    }

    public function getSortOrderAttribute(): int
    {
        return $this->sortOrder;
    }

    // Método para convertir a array (para JSON)
    public function toArray(): array
    {
        $productos = $this->getProductosActivos();
        $config = \App\Models\WeeklyTurnoConfig::getConfigForDay($this->getDayOfWeek());

        return [
            'fecha' => $this->fecha,
            'hora_inicio' => $this->horaInicio,
            'hora_fin' => $this->horaFin,
            'capacidad_maxima' => $this->capacidadMaxima,
            'formatted_time' => $this->getFormattedTimeAttribute(),
            'capacidad_restante' => $this->getCapacidadRestanteAttribute(),
            'pedidos_activos' => $this->getPedidosActivosAttribute(),
            'is_disponible' => $this->getIsDisponibleAttribute(),
            'sort_order' => $this->sortOrder,
            'productos_activos' => $productos,
            'capacidad_productos' => [
                'hamburguesas' => $config->max_hamburguesas,
                'acompañamientos' => $config->max_acompañamientos
            ]
        ];
    }
}
