<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Pedido #{{ $order->order_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 200);
        });
    </script>
</head>

<body class="p-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Pedido #{{ $order->order_number }}</h4>
            <button class="btn btn-sm btn-secondary no-print" onclick="window.print()">Imprimir</button>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-6">
                <div><strong>Cliente:</strong> {{ $order->contact_name ?: $order->user->name ?? 'Invitado' }}</div>
                <div><strong>Tel:</strong> {{ $order->contact_phone ?: $order->user->phone ?? '' }}</div>
            </div>
            <div class="col-6 text-end">
                <div><strong>Fecha:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</div>
                <div><strong>Estado:</strong> {{ ucfirst($order->status) }}</div>
            </div>
        </div>

        @if ($order->address)
            <div class="mb-3">
                <strong>Entrega:</strong>
                {{ trim(($order->address->street ?? '') . ' ' . ($order->address->number ?? '')) }},
                {{ trim(($order->address->city ?? '') . ' ' . ($order->address->postal_code ?? '')) }}
            </div>
        @endif

        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Detalles</th>
                    <th class="text-end">Cant.</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td class="small">
                            @php
                                $variants = is_array($item->selected_variants)
                                    ? $item->selected_variants
                                    : (json_decode($item->selected_variants, true) ?:
                                    []);
                                $options = is_array($item->selected_options)
                                    ? $item->selected_options
                                    : (json_decode($item->selected_options, true) ?:
                                    []);
                            @endphp
                            @foreach (collect($variants)->groupBy('name') as $name => $vals)
                                <div><strong>{{ $name }}:</strong>
                                    {{ collect($vals)->pluck('value')->join(', ') }}</div>
                            @endforeach
                            @foreach (collect($options)->groupBy('name') as $name => $vals)
                                <div><strong>{{ $name }}:</strong>
                                    {{ collect($vals)->pluck('value')->join(', ') }}</div>
                            @endforeach
                        </td>
                        <td class="text-end">{{ $item->quantity }}</td>
                        <td class="text-end">${{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-3">
            <div class="d-flex justify-content-between small">
                <span>Subtotal</span><span>${{ number_format($order->subtotal, 2) }}</span></div>
            @if ($order->delivery_fee > 0)
                <div class="d-flex justify-content-between small">
                    <span>Envío</span><span>${{ number_format($order->delivery_fee, 2) }}</span></div>
            @endif
            @if ($order->discount_amount > 0)
                <div class="d-flex justify-content-between small"><span>Descuento</span><span>-
                        ${{ number_format($order->discount_amount, 2) }}</span></div>
            @endif
            <div class="d-flex justify-content-between fw-semibold border-top pt-2">
                <span>Total</span><span>${{ number_format($order->total_amount, 2) }}</span></div>
        </div>

        @if ($order->notes)
            <div class="mt-3">
                <strong>Notas:</strong>
                <div class="small">{{ $order->notes }}</div>
            </div>
        @endif
    </div>
</body>

</html>
