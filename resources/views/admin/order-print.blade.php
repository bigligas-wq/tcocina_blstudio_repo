<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Térmico - Pedido #{{ $order->order_number }}</title>
    @php
        // Debug: Log del pedido en Laravel logs
        try {
            \Log::info('DEBUG Ticket Print - Order payload from view', [
                'order' => $order->toArray(),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('DEBUG Ticket Print - Error serializando order a array from view', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    @endphp
    <script>
        // Debug: imprimir el contenido de $order en la consola del navegador
        // Nota: toArray() para evitar funciones/relaciones perezosas en el log
        window.__ORDER_DEBUG__ = @json($order->toArray());
        console.debug('ORDER DEBUG:', window.__ORDER_DEBUG__);
    </script>
    <script>
        // Auto-print al cargar la página
        window.onload = function() {
            window.print();
        };
    </script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 13pt;
            /* +1pt para mejor legibilidad en cocina */
            line-height: 1.3;
            color: #000;
            background: #fff;
            padding: 0;
            margin: 0;
            font-weight: bold;
            /* Todo en negrita por defecto */
        }

        .item {
            font-size: 13px;
            text-transform: uppercase;
        }

        .ticket-container {
            max-width: 56mm;
            margin: 0 auto;
            padding: 1mm;
            background: #fff;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        .header {
            text-align: center;
            margin-bottom: 2mm;
            padding-bottom: 1mm;
        }
        .header-icon { width: 80px; height: 80px; filter: grayscale(100%); margin: 0 auto 0.5mm; }

        .separator {
            border-bottom: 1px dashed #000;
            margin: 1mm 0;
            width: 100%;
        }

        .section {
            margin-bottom: 1mm;
            padding-bottom: 1mm;
            text-align: left;
        }

        .section-header {
            text-align: center;
            margin-bottom: 1mm;
        }

        .delivery-info {
            text-transform: uppercase;
        }

        .section:last-of-type {
            border-bottom: none;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* Ajuste de tamaños para mejor legibilidad en cocina (+1pt en todos) */
        .small-text {
            font-size: 11pt;
        }

        .medium-text {
            font-size: 13pt;
        }

        .large-text {
            font-size: 15pt;
        }

        .product-details {
            font-size: 12pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1mm;
            margin-bottom: 1mm;
        }

        th,
        td {
            padding: 0.3mm 0;
            vertical-align: top;
        }

        .product-ingredients {
            font-size: 11pt;
            line-height: 1.2;
        }

        /* Tabla productos: cabezas y precios en una línea; que todo entre en 56mm */
        .products-table { font-size: 11pt; }
        .products-table th { white-space: nowrap; padding: 0 0.5mm 0 0; }
        .products-table td { padding: 0 0.5mm 0 0; }
        .product-row td:first-child { width: 12%; text-align: left; white-space: nowrap; }
        .product-row td:nth-child(2) { width: 58%; text-align: left; padding-left: 0.5mm; min-width: 0; overflow-wrap: break-word; }
        .product-row td:last-child { width: 30%; text-align: right; white-space: nowrap; }

        .subtotal-line {
            display: flex;
            justify-content: space-between;
            margin-top: 2mm;
            font-size: 12pt;
        }

        .product-details {
            font-size: 12pt;
            margin-top: 0.2mm;
            padding-left: 0;
        }

        .product-details-row td {
            padding: 0.2mm 0;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.2mm;
            font-size: 12pt;
        }

        .total-line.final {
            border-top: 1px solid #000;
            padding-top: 0.3mm;
            margin-top: 0.3mm;
            font-size: 13pt;
        }

        @media print {

            html,
            body {
                width: 48mm;
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .ticket-container {
                width: 56mm;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <div class="ticket-container w-100">
        <div class="header">
            <img src="{{ asset('productos/fondo/tcocinapng.png') }}" alt="T Cocina" class="header-icon">
            @if (!empty($microturnoTimeRange))
                <div class="medium-text">{{ $microturnoTimeRange }}</div>
            @else
                <div class="small-text">Sin horario asignado</div>
            @endif
            <div class="separator"></div>
        </div>

        <div class="section">
            <div class="medium-text section-header">CLIENTE</div>
            <div>Nombre: {{ $order->contact_name ?: $order->user->name ?? 'Invitado' }}</div>
            <div>Tel: {{ $order->contact_phone ?: $order->user->phone ?? '' }}</div>
            <div class="separator"></div>
        </div>

        <div class="section">
            @php
                // Debug: Log para verificar la dirección
                \Log::info('DEBUG Ticket Print - Address Info', [
                    'order_id' => $order->id,
                    'address_id' => $order->address_id,
                    'has_address_relation' => $order->address ? 'yes' : 'no',
                    'address_data' => $order->address ? $order->address->toArray() : null
                ]);
                
                // Determinar si es delivery o pickup
                $isDelivery = $order->address_id && $order->address;
                $deliveryAddress = '';
                $deliveryNotes = '';
                
                if ($isDelivery) {
                    $street = trim($order->address->street ?? '');
                    $number = trim($order->address->number ?? '');
                    $deliveryAddress = trim($street . ' ' . $number);
                    $deliveryNotes = $order->address->reference ?? '';
                }
            @endphp
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                <div class="medium-text section-header" style="text-align:left;">
                    ENTREGA:
                </div>
                @if ($isDelivery)
                    <div class="medium-text section-header" style="text-align:right;">DELIVERY</div>
                @else
                    <div class="medium-text section-header" style="text-align:right;">RETIRO</div>
                @endif
            </div>
            @if ($isDelivery)
                @if (!empty($deliveryAddress))
                    <div class="text-center" style="margin-top: 2mm;">{{ $deliveryAddress }}</div>
                @endif
                @if (!empty($order->address->neighborhood))
                    <div class="small-text text-center">{{ $order->address->neighborhood }}</div>
                @endif
                @if (!empty($deliveryNotes))
                    <div class="small-text" style="margin-top:1mm;">NOTAS DE ENTREGA: {{ $deliveryNotes }}</div>
                @endif
            @else
                <div class="text-center" style="margin-top: 2mm;">RETIRO POR EL LOCAL</div>
                @if (!empty($deliveryNotes))
                    <div class="small-text" style="margin-top:1mm;">NOTAS DE ENTREGA: {{ $deliveryNotes }}</div>
                @endif
            @endif
            <div class="separator"></div>
        </div>

        <div class="section">
            <div class="medium-text section-header">PRODUCTOS</div>
            <table class="products-table">
                <thead>
                    <tr>
                        <th class="text-left">Cant.</th>
                        <th class="text-left">Producto</th>
                        <th class="text-right">$</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $subtotalCents = 0;
                    @endphp
                    @foreach ($order->items as $item)
                        @php
                            $unit = $item->price ?? $item->unit_price ?? ($item->product->base_price ?? 0);
                            $lineTotal = ($unit * $item->quantity);
                            $subtotalCents += $lineTotal;
                        @endphp
                        <tr class="product-row">
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->product->name }}</td>
                            <td>$ {{ number_format((int) round($lineTotal), 0, ',', '.') }}</td>
                        </tr>
                        @if ($item->configuration_text)
                            <tr>
                                 <td colspan="3" class="product-ingredients kv-container">
                                    @php
                                        // Solo aplicar lógica de hamburguesas a productos de categoría 1
                                        $isBurger = isset($item->product->category_id) && $item->product->category_id == 1;
                                    @endphp
                                    @if ($isBurger)
                                        @php
                                            $configParts = explode(' | ', $item->configuration_text);
                                            $pairs = [];

                                            foreach ($configParts as $part) {
                                                $part = trim($part);
                                                if ($part === '' || $part === '|') { continue; }

                                                // Normalizador de etiquetas a claves canónicas
                                                $normalize = function ($label) {
                                                    $l = strtolower(trim($label));
                                                    return match($l) {
                                                        'medallon', 'medallones' => 'Medallones',
                                                        'tipo' => 'Tipo',
                                                        'aderezo', 'aderezos' => 'Aderezos',
                                                        'dip', 'dips' => 'Dips',
                                                        'dip extra', 'dip-extra', 'extra dip' => 'Dip Extra',
                                                        'extra', 'extras' => 'Extras',
                                                        default => ucwords(trim($label)),
                                                    };
                                                };

                                                if (strpos($part, ':') !== false) {
                                                    [$label, $value] = explode(':', $part, 2);
                                                    $label = $normalize($label);
                                                    $value = trim($value);
                                                } else {
                                                    // Map valores sueltos
                                                    if (in_array($part, ['Simple', 'Doble', 'Triple', 'Cuádruple', 'Quintuple'])) {
                                                        $label = 'Medallones';
                                                        $value = $part;
                                                    } elseif (in_array($part, ['Carne', 'Pollo', 'Vegetariano', 'Veggie Tomate Seco Aduki (Rúcula, Albahaca y Oliva)', 'Veggie Zanahoria Romero (Arvejas, Yamaní y Chía)'])) {
                                                        $label = 'Tipo';
                                                        $value = $part;
                                                    } else {
                                                        $label = 'Extras'; // cualquier extra sin etiqueta clara va dentro de Extras
                                                        $value = $part;
                                                    }
                                                }

                                                if ($value === '') { continue; }
                                                $pairs[$label] = isset($pairs[$label]) && $pairs[$label] !== ''
                                                    ? $pairs[$label] . ', ' . $value
                                                    : $value;
                                            }

                                            // Orden deseado: Medallones > Tipo > Aderezos > Extras > Dips > Dip Extra
                                            $orderKeys = ['Medallones', 'Tipo', 'Aderezos', 'Extras', 'Dips', 'Dip Extra'];
                                            $orderedKeys = [];
                                            foreach ($orderKeys as $k) { if (isset($pairs[$k])) $orderedKeys[] = $k; }
                                            // Añadir el resto al final
                                            foreach ($pairs as $k => $_) { if (!in_array($k, $orderedKeys)) $orderedKeys[] = $k; }
                                        @endphp

                                         <style>
                                             .kv-container{padding:0;}
                                             .kv-row{display:flex;justify-content:space-between;gap:6px;width:100%;}
                                             .kv-row .kv-k{font-weight:bold;}
                                         </style>

                                        @foreach ($orderedKeys as $k)
                                            <div class="kv-row"><span class="kv-k">{{ strtoupper($k) }}:</span><span>{{ $pairs[$k] }}</span></div>
                                        @endforeach
                                    @else
                                        {{-- Para postres, acompañamientos, etc.: mostrar configuración tal cual sin normalización --}}
                                        <div class="product-ingredients">{{ $item->configuration_text }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
            @php
                // Fallbacks robustos para montos del pedido
                $storedSubtotal = (float) data_get($order, 'subtotal', 0);
                $storedDiscount = (float) data_get($order, 'discount_amount', 0);
                $deliveryFeeVal = (float) data_get($order, 'delivery_fee', 0);
                $paymentMethodVal = (string) data_get($order, 'payment_method', 'cash');

                // Calcular subtotal a partir de ítems si el almacenado no está o es 0
                $computedSubtotal = 0;
                $itemsForCalc = is_array($order) ? ($order['items'] ?? []) : ($order->items ?? []);
                foreach ($itemsForCalc as $it) {
                    $lineTotal = (float) data_get($it, 'total_price');
                    if ($lineTotal <= 0) {
                        $unit = (float) data_get($it, 'unit_price', 0);
                        $qty = (int) data_get($it, 'quantity', 0);
                        $lineTotal = $unit * $qty;
                    }
                    $computedSubtotal += $lineTotal;
                }

                $subtotalToUse = $storedSubtotal > 0 ? $storedSubtotal : $computedSubtotal;

                // Descuento: usar el guardado; si no hay, calcular si corresponde por efectivo
                $discountToUse = $storedDiscount > 0 ? $storedDiscount : 0;
                if ($discountToUse <= 0 && $paymentMethodVal === 'cash') {
                    $percent = (float) \App\Models\BusinessSetting::get('cash_discount_percentage', 0);
                    if ($percent > 0) {
                        $discountToUse = (int) round($subtotalToUse * ($percent / 100));
                    }
                }

                // Total: usar almacenado; si es 0, recalcular
                $storedTotal = (float) data_get($order, 'total_amount', 0);
                $totalToUse = $storedTotal > 0 ? $storedTotal : max(0, $subtotalToUse + $deliveryFeeVal - $discountToUse);

                // Debug en logs para validar valores usados en el ticket
                try {
                    \Log::info('DEBUG Ticket Print - Totals used in view', [
                        'order_id' => data_get($order, 'id'),
                        'storedSubtotal' => $storedSubtotal,
                        'computedSubtotal' => $computedSubtotal,
                        'subtotalToUse' => $subtotalToUse,
                        'storedDiscount' => $storedDiscount,
                        'discountToUse' => $discountToUse,
                        'deliveryFee' => $deliveryFeeVal,
                        'storedTotal' => $storedTotal,
                        'totalToUse' => $totalToUse,
                        'payment_method' => $paymentMethodVal,
                    ]);
                } catch (\Throwable $e) {}
            @endphp
            <div class="subtotal-line">
                <span>Subtotal:</span>
                <span class="text-right">$ {{ number_format((int) $subtotalToUse, 0, ',', '.') }}</span>
            </div>
            @if ((int) $deliveryFeeVal > 0)
            <div class="total-line">
                <span>Envío:</span>
                <span class="text-right">$ {{ number_format((int) $deliveryFeeVal, 0, ',', '.') }}</span>
            </div>
            @endif
            @if ((int) $discountToUse > 0)
            <div class="total-line">
                <span>Descuento:</span>
                <span class="text-right">- $ {{ number_format((int) $discountToUse, 0, ',', '.') }}</span>
            </div>
            @endif
        </div>

        <div class="section">
            <div class="total-line final">
                <span><strong>TOTAL</strong></span>
                <span class="text-right"><strong>$ {{ number_format((int) $totalToUse, 0, ',', '.') }}</strong></span>
            </div>
            <div class="total-line">
                <span><strong>PAGO:</strong></span>
                <span class="text-right"><strong>
                    @php
                        $paymentMethod = $order->payment_method ?? 'cash';
                        $paymentLabel = match($paymentMethod) {
                            'cash' => 'EFECTIVO',
                            'card' => 'TARJETA',
                            'transfer' => 'TRANSFERENCIA',
                            default => 'EFECTIVO'
                        };
                    @endphp
                    {{ $paymentLabel }}
                </strong></span>
            </div>
            <div class="separator"></div>
        </div>

        @php
            $rawNotes = (string) ($order->notes ?? '');
            // Remover datos automáticos antiguos: Cliente, Teléfono, Pago, Lo antes posible, Programado para
            $cleanNotes = preg_replace('/\s*\|?\s*(Cliente\s*:\s*[^|]*|Tel[eé]fono\s*:\s*[^|]*|Pago\s*:\s*[^|]*|Lo\s+antes\s+posible|Programado\s+para\s*:\s*[^|]*)\s*(\|\s*)?/i', '', $rawNotes);
            $cleanNotes = trim(trim($cleanNotes, '|'));
        @endphp
        @if (!empty($cleanNotes))
            <div class="section">
                <div class="medium-text" style="text-align: left;">NOTAS:</div>
                <div style="text-align: center; padding: 2mm 0;">{{ $cleanNotes }}</div>
                <div class="separator"></div>
            </div>
        @endif

        <div class="text-center" style="margin-top: 0.2mm; margin-bottom: 0.2mm;">
            <img src="{{ asset('productos/fondo/tiketgrandesligas.png') }}" alt="Grandes Ligas" style="width: 120px; height: auto; filter: grayscale(100%);" />
        </div>

        <div class="text-center" style="margin-top: 2mm;">
            <img src="{{ asset('productos/fondo/qr-tcocina.png') }}" alt="QR T Cocina" width="120" height="120">
            <div style="font-size: 10pt; margin-top: 1mm;">Escaneá y visitá nuestra web</div>
        </div>

        <div class="text-center small-text" style="margin-top: 2mm;">DISFRUTALA</div>
        <div class="text-center small-text">T cocina</div>
    </div>
</body>

</html>
