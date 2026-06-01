<?php

namespace Database\Seeders;

use App\Models\LabImprovement;
use Illuminate\Database\Seeder;

class LabImprovementSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar mejoras existentes antes de reinsertar
        LabImprovement::query()->delete();

        $mejoras = [
            [
                'nombre'           => 'Control de equipo y accesos',
                'descripcion_corta'=> 'Creá usuarios para tu equipo con permisos específicos para cada rol.',
                'descripcion_larga'=> 'Hoy entrás solo vos con una cuenta. Esta mejora te permite crear usuarios para tu equipo (cajero, cocina, encargado) y asignarle a cada uno exactamente los accesos que necesita. Tu nombre y rol aparecen siempre en el panel, y podés cerrar sesión con un clic desde cualquier pantalla.',
                'categoria'        => 'admin',
                'precio_usd'       => 120.00,
                'icono'            => '👥',
                'es_destacada'     => false,
                'es_popular'       => false,
                'tiempo_estimado_horas' => 14,
                'roi_estimado'     => 'Delegá sin perder control. Tu equipo trabaja, cada uno en lo suyo.',
                'estado'           => 'publicada',
                'diferencias'      => [
                    ['texto' => 'Antes: una sola cuenta para todo el equipo', 'color' => '#ef4444'],
                    ['texto' => 'Ahora: un usuario por persona, con sus propios accesos', 'color' => '#10b981'],
                    ['texto' => 'Antes: no se podía cerrar sesión desde el panel', 'color' => '#ef4444'],
                    ['texto' => 'Ahora: tu nombre y botón de cerrar sesión siempre visible', 'color' => '#10b981'],
                ],
            ],
            [
                'nombre'           => 'Checkout paso a paso',
                'descripcion_corta'=> 'Tus clientes completan el pedido guiados por pasos claros. Menos abandono, más ventas.',
                'descripcion_larga'=> 'Rediseño completo del proceso de compra en pasos visuales: método de entrega → datos de contacto → confirmación. Cada paso se resume y se puede editar sin perder lo anterior. Menos confusión del cliente = más pedidos completados.',
                'categoria'        => 'ux',
                'precio_usd'       => 180.00,
                'icono'            => '🛒',
                'es_destacada'     => true,
                'es_popular'       => true,
                'tiempo_estimado_horas' => 20,
                'roi_estimado'     => 'Menos clientes que abandonan el carrito = más facturación directa.',
                'estado'           => 'publicada',
                'diferencias'      => [
                    ['texto' => 'Antes: formulario largo en una sola pantalla', 'color' => '#ef4444'],
                    ['texto' => 'Ahora: pasos guiados con progreso visual', 'color' => '#10b981'],
                    ['texto' => 'Antes: si te equivocabas, volvías a empezar', 'color' => '#ef4444'],
                    ['texto' => 'Ahora: editás cualquier paso sin perder lo que escribiste', 'color' => '#10b981'],
                ],
            ],
            [
                'nombre'           => 'Pedidos grandes por WhatsApp',
                'descripcion_corta'=> 'Cuando un pedido es muy grande, el sistema lo deriva automáticamente a WhatsApp para coordinarlo.',
                'descripcion_larga'=> 'Cuando un pedido supera la capacidad de un turno normal, en lugar de bloquearlo el sistema muestra una pantalla clara que guía al cliente a coordinar la entrega por WhatsApp con la cocina. Capturás el pedido grande sin desarmar la logística de los demás.',
                'categoria'        => 'ux',
                'precio_usd'       => 80.00,
                'icono'            => '💬',
                'es_destacada'     => false,
                'es_popular'       => false,
                'tiempo_estimado_horas' => 8,
                'roi_estimado'     => 'No perdés más pedidos grandes por falta de turnos disponibles.',
                'estado'           => 'publicada',
                'diferencias'      => [
                    ['texto' => 'Antes: pedido grande bloqueado, venta perdida', 'color' => '#ef4444'],
                    ['texto' => 'Ahora: se deriva a WhatsApp y se coordina directo', 'color' => '#10b981'],
                ],
            ],
            [
                'nombre'           => 'Pantalla de cocina mejorada',
                'descripcion_corta'=> 'La pantalla de cocina filtra los pedidos por turno y muestra solo lo que hay que cocinar ahora.',
                'descripcion_larga'=> 'La pantalla que usa el equipo de cocina (KDS) ahora tiene filtros por microturno con un clic, contador de pedidos por turno y botones directos para "Iniciar preparación" y "Marcar entregado". Cocina más ordenada, menos errores y pedidos traspapelados.',
                'categoria'        => 'admin',
                'precio_usd'       => 90.00,
                'icono'            => '🍳',
                'es_destacada'     => false,
                'es_popular'       => false,
                'tiempo_estimado_horas' => 10,
                'roi_estimado'     => 'Cocina más organizada = menos errores y reclamos de clientes.',
                'estado'           => 'publicada',
                'diferencias'      => [
                    ['texto' => 'Antes: todos los pedidos mezclados en pantalla', 'color' => '#ef4444'],
                    ['texto' => 'Ahora: filtro por turno con contador de pedidos', 'color' => '#10b981'],
                    ['texto' => 'Antes: no quedaba claro qué cocinar primero', 'color' => '#ef4444'],
                    ['texto' => 'Ahora: botones directos iniciar / entregar', 'color' => '#10b981'],
                ],
            ],
            [
                'nombre'           => 'Historial de pedidos mejorado',
                'descripcion_corta'=> 'Accedé a los pedidos del día anterior y filtrá todo desde el panel de administración.',
                'descripcion_larga'=> 'El panel de administración de pedidos suma la vista de pedidos del día anterior para no perder trazabilidad, opción de ver todos los pedidos juntos y mejoras de legibilidad. Gestionás el día con más control y menos clics.',
                'categoria'        => 'admin',
                'precio_usd'       => 70.00,
                'icono'            => '📋',
                'es_destacada'     => false,
                'es_popular'       => false,
                'tiempo_estimado_horas' => 7,
                'roi_estimado'     => 'Trazabilidad completa de pedidos para revisar cualquier día sin complicaciones.',
                'estado'           => 'publicada',
                'diferencias'      => [
                    ['texto' => 'Antes: solo veías el día actual en el panel', 'color' => '#ef4444'],
                    ['texto' => 'Ahora: acceso al histórico del día anterior', 'color' => '#10b981'],
                    ['texto' => 'Antes: paginación rígida y listado denso', 'color' => '#ef4444'],
                    ['texto' => 'Ahora: ver "Todos" o por cantidad con un clic', 'color' => '#10b981'],
                ],
            ],
        ];

        foreach ($mejoras as $data) {
            LabImprovement::create($data);
        }

        $this->command->info('✅ ' . count($mejoras) . ' mejoras del Laboratorio cargadas.');
    }
}
