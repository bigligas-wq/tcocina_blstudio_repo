<?php

namespace App\Http\Controllers;

use App\Mail\LabIdeaProposedMail;
use App\Mail\LabOrderReceivedMail;
use App\Models\BusinessSetting;
use App\Models\LabBundle;
use App\Models\LabChangelogEntry;
use App\Models\LabCreditWallet;
use App\Models\LabImprovement;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabWishlistItem;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LaboratorioController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $improvements = LabImprovement::publicada()
            ->orderByDesc('es_destacada')
            ->orderByDesc('es_popular')
            ->orderByDesc('created_at')
            ->get();

        $featured = $improvements->firstWhere('es_destacada', true);
        $catalog  = $improvements->where('id', '!=', optional($featured)->id)->values();

        $activeImprovementIds = LabOrderItem::where('estado', 'activo')
            ->whereHas('order', fn ($q) => $q->where('user_id', $userId))
            ->pluck('lab_improvement_id')->filter()->unique()->values()->all();

        $inProgressItems = LabOrderItem::with('order', 'improvement')
            ->whereIn('estado', ['pendiente', 'en_proceso'])
            ->whereHas('order', fn ($q) => $q->where('user_id', $userId)
                ->whereNotIn('estado', ['cancelado']))
            ->orderByDesc('created_at')
            ->get();

        $inProgressImprovementIds = $inProgressItems->pluck('lab_improvement_id')->filter()->unique()->values()->all();

        $wishlistIds = LabWishlistItem::where('user_id', $userId)
            ->pluck('lab_improvement_id')->all();

        $bundles = LabBundle::publicado()->with('improvements')->orderByDesc('created_at')->get();

        $changelog = LabChangelogEntry::visible()
            ->with('improvement')
            ->orderByDesc('publicado_en')
            ->limit(8)
            ->get();

        $wallet = LabCreditWallet::forUser($userId);

        $stats = [
            'activas'     => count($activeImprovementIds),
            'disponibles' => $catalog->count() + ($featured ? 1 : 0),
            'en_proceso'  => $inProgressItems->count(),
            'creditos'    => (float) $wallet->balance_usd,
        ];

        $labSettings = $this->getBusinessSettings();

        return view('laboratorio.cliente.index', compact(
            'featured', 'catalog', 'activeImprovementIds', 'inProgressImprovementIds',
            'inProgressItems', 'wishlistIds', 'bundles', 'changelog', 'wallet', 'stats', 'labSettings'
        ));
    }

    public function historial()
    {
        $orders = LabOrder::with('items.improvement')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(10);

        $wallet = LabCreditWallet::forUser(auth()->id());
        $movements = $wallet->movements()->orderByDesc('created_at')->limit(30)->get();

        $labSettings = $this->getBusinessSettings();

        return view('laboratorio.cliente.historial', compact('orders', 'labSettings', 'wallet', 'movements'));
    }

    public function crearOrden(Request $request)
    {
        $data = $request->validate([
            'items'              => ['required', 'array', 'min:1'],
            'items.*.improvement_id' => ['required', 'integer', 'exists:lab_improvements,id'],
            'items.*.nota'       => ['nullable', 'string', 'max:400'],
            'comprobante'        => ['nullable', 'image', 'max:4096'],
            'usar_creditos'      => ['nullable', 'boolean'],
        ]);

        $improvements = LabImprovement::publicada()
            ->whereIn('id', collect($data['items'])->pluck('improvement_id'))
            ->get()
            ->keyBy('id');

        if ($improvements->count() !== collect($data['items'])->pluck('improvement_id')->unique()->count()) {
            return response()->json(['error' => 'Una o más mejoras no están disponibles.'], 422);
        }

        $order = DB::transaction(function () use ($data, $improvements, $request) {
            $total = 0;
            foreach ($data['items'] as $item) {
                $total += $improvements[$item['improvement_id']]->precio_efectivo;
            }

            $creditsAplicados = 0;
            if (!empty($data['usar_creditos'])) {
                $wallet = LabCreditWallet::forUser(auth()->id());
                $creditsAplicados = min($wallet->balance_usd, $total);
            }

            $order = LabOrder::create([
                'user_id'             => auth()->id(),
                'estado'              => $creditsAplicados >= $total ? 'confirmado' : 'pendiente_pago',
                'total_usd'           => $total,
                'credits_aplicados_usd' => $creditsAplicados,
                'confirmado_at'       => $creditsAplicados >= $total ? now() : null,
            ]);

            foreach ($data['items'] as $item) {
                $imp = $improvements[$item['improvement_id']];
                LabOrderItem::create([
                    'lab_order_id'        => $order->id,
                    'lab_improvement_id'  => $imp->id,
                    'nombre_snapshot'     => $imp->nombre,
                    'precio_usd_snapshot' => $imp->precio_efectivo,
                    'nota'                => $item['nota'] ?? null,
                    'estado'              => $creditsAplicados >= $total ? 'en_proceso' : 'pendiente',
                ]);
            }

            if ($creditsAplicados > 0) {
                LabCreditWallet::forUser(auth()->id())->debit(
                    $creditsAplicados,
                    "Pedido {$order->order_number}",
                    $order->id
                );
            }

            if ($request->hasFile('comprobante')) {
                $path = $request->file('comprobante')->store('lab/comprobantes', 'public');
                $order->update(['comprobante_path' => $path]);
            }

            // Limpiar wishlist de los items pedidos
            LabWishlistItem::where('user_id', auth()->id())
                ->whereIn('lab_improvement_id', $improvements->pluck('id'))
                ->delete();

            return $order;
        });

        $this->notifyDeveloper($order);

        return response()->json([
            'order_number' => $order->order_number,
            'whatsapp_url' => $this->buildWhatsappUrl($order),
            'redirect'     => route('laboratorio.historial'),
            'estado'       => $order->estado,
        ]);
    }

    public function marcarWhatsappEnviado(LabOrder $labOrder)
    {
        abort_unless($labOrder->user_id === auth()->id(), 403);

        if (!$labOrder->whatsapp_enviado_at) {
            $labOrder->update(['whatsapp_enviado_at' => now()]);
        }

        return response()->json(['ok' => true]);
    }

    public function proponerIdea(Request $request)
    {
        $data = $request->validate([
            'idea'   => ['nullable', 'string', 'max:1000'],
            'imagen' => ['nullable', 'image', 'max:5120'],
        ]);

        $devEmail = BusinessSetting::get('lab_developer_email', 'grandesligasarg@gmail.com');
        $whatsapp = BusinessSetting::get('lab_developer_whatsapp', '');

        $imagenPath = null;
        if ($request->hasFile('imagen')) {
            $imagenPath = $request->file('imagen')->store('lab/ideas', 'public');
        }

        try {
            Mail::to($devEmail)->send(new LabIdeaProposedMail(auth()->user(), $data['idea'] ?? '', $imagenPath));
        } catch (\Throwable $e) {
            Log::warning('Lab idea email failed: ' . $e->getMessage());
        }

        $waUrl = null;
        if ($whatsapp) {
            $waText = "💡 *Nueva idea para el Laboratorio*\n\nDe: " . auth()->user()->name . "\n\n" . ($data['idea'] ?? '');
            $waUrl = 'https://wa.me/' . preg_replace('/\D/', '', $whatsapp) . '?text=' . urlencode($waText);
        }

        return response()->json(['ok' => true, 'whatsapp_url' => $waUrl]);
    }

    public function toggleWishlist(Request $request)
    {
        $data = $request->validate([
            'improvement_id' => ['required', 'integer', 'exists:lab_improvements,id'],
        ]);

        $existing = LabWishlistItem::where('user_id', auth()->id())
            ->where('lab_improvement_id', $data['improvement_id'])
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['saved' => false]);
        }

        LabWishlistItem::create([
            'user_id' => auth()->id(),
            'lab_improvement_id' => $data['improvement_id'],
        ]);

        return response()->json(['saved' => true]);
    }

    public function wishlist()
    {
        $items = LabWishlistItem::with('improvement')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->get()
            ->pluck('improvement')
            ->filter()
            ->values();

        $labSettings = $this->getBusinessSettings();

        return view('laboratorio.cliente.wishlist', compact('items', 'labSettings'));
    }

    public function comprarBundle(Request $request, LabBundle $labBundle)
    {
        abort_unless($labBundle->estado === 'publicado', 404);

        $items = [];
        foreach ($labBundle->improvements as $imp) {
            $items[] = ['improvement_id' => $imp->id];
        }

        // Reusa el flujo de crearOrden pero con el precio de bundle
        return DB::transaction(function () use ($labBundle, $items, $request) {
            $order = LabOrder::create([
                'user_id'   => auth()->id(),
                'estado'    => 'pendiente_pago',
                'total_usd' => $labBundle->precio_bundle_usd,
            ]);

            foreach ($labBundle->improvements as $imp) {
                $precioProporcional = $labBundle->precio_bundle_usd * ((float) $imp->precio_usd / $labBundle->precio_original);
                LabOrderItem::create([
                    'lab_order_id'        => $order->id,
                    'lab_improvement_id'  => $imp->id,
                    'nombre_snapshot'     => $imp->nombre,
                    'precio_usd_snapshot' => round($precioProporcional, 2),
                    'nota'                => "Parte del bundle: {$labBundle->nombre}",
                    'estado'              => 'pendiente',
                ]);
            }

            $this->notifyDeveloper($order);

            return response()->json([
                'order_number' => $order->order_number,
                'whatsapp_url' => $this->buildWhatsappUrl($order),
                'redirect'     => route('laboratorio.historial'),
            ]);
        });
    }

    private function notifyDeveloper(LabOrder $order): void
    {
        $devEmail = BusinessSetting::get('lab_developer_email', 'grandesligasarg@gmail.com');

        try {
            Mail::to($devEmail)->send(new LabOrderReceivedMail($order));
        } catch (\Throwable $e) {
            Log::warning('Lab order email failed: ' . $e->getMessage());
        }

        $developer = User::where('role', 'developer')->first();
        if ($developer) {
            UserNotification::create([
                'user_id'     => $developer->id,
                'type'        => 'success',
                'title'       => '🚨 Nuevo pedido del Laboratorio',
                'message'     => "{$order->user->name} pidió " . $order->items->count() . ' mejora(s) — USD ' . number_format($order->total_usd, 2),
                'action_url'  => route('laboratorio.admin.orders'),
                'action_text' => 'Ver pedido',
                'meta'        => ['lab_order_id' => $order->id],
            ]);
        }
    }

    private function buildWhatsappUrl(LabOrder $order): ?string
    {
        $whatsapp = BusinessSetting::get('lab_developer_whatsapp', '');
        if (!$whatsapp) {
            return null;
        }

        $lines = [];
        $lines[] = '🚨 *Nuevo pedido — Laboratorio TCocina*';
        $lines[] = '';
        $lines[] = "Pedido: *{$order->order_number}*";
        $lines[] = '';
        $lines[] = '📋 *Mejoras solicitadas:*';
        foreach ($order->items as $item) {
            $lines[] = "• {$item->nombre_snapshot} — USD " . number_format($item->precio_usd_snapshot, 2);
            if ($item->nota) {
                $lines[] = "   ↳ _\"{$item->nota}\"_";
            }
        }
        $lines[] = '';
        $lines[] = '💰 *Total:* USD ' . number_format($order->total_usd, 2);
        if ($order->credits_aplicados_usd > 0) {
            $lines[] = '💎 Créditos aplicados: USD ' . number_format($order->credits_aplicados_usd, 2);
            $resto = max(0, $order->total_usd - $order->credits_aplicados_usd);
            $lines[] = '💵 *A transferir:* USD ' . number_format($resto, 2);
        }

        if ($order->comprobante_url) {
            $lines[] = "📎 Comprobante: {$order->comprobante_url}";
        }

        $lines[] = '';
        $lines[] = '✅ Transferencia realizada — esperando confirmación';

        $text = implode("\n", $lines);
        return 'https://wa.me/' . preg_replace('/\D/', '', $whatsapp) . '?text=' . urlencode($text);
    }

    private function getBusinessSettings(): array
    {
        return [
            'whatsapp' => BusinessSetting::get('lab_developer_whatsapp', ''),
            'email'    => BusinessSetting::get('lab_developer_email', 'grandesligasarg@gmail.com'),
            'alias'    => BusinessSetting::get('lab_developer_alias', 'biglstudio'),
            'cbu'      => BusinessSetting::get('lab_developer_cbu', '0110383830038320470947'),
            'banco'    => BusinessSetting::get('lab_developer_banco', 'Banco Nación'),
            'titular'  => BusinessSetting::get('lab_developer_titular', 'BLStudio (Juan Ignacio Ibarlucia)'),
        ];
    }
}
