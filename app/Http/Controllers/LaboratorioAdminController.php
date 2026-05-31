<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\LabBundle;
use App\Models\LabChangelogEntry;
use App\Models\LabCreditWallet;
use App\Models\LabImprovement;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\UserNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LaboratorioAdminController extends Controller
{
    public function index()
    {
        $improvements = LabImprovement::orderByDesc('es_destacada')
            ->orderByDesc('created_at')
            ->get();

        return view('laboratorio.admin.index', compact('improvements'));
    }

    public function create()
    {
        return view('laboratorio.admin.form', [
            'improvement' => new LabImprovement(['estado' => 'borrador', 'categoria' => 'visual']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateImprovement($request);

        DB::transaction(function () use (&$improvement, $data, $request) {
            if (!empty($data['es_destacada'])) {
                LabImprovement::where('es_destacada', true)->update(['es_destacada' => false]);
            }

            $improvement = LabImprovement::create($data);

            $this->handleImages($request, $improvement);
        });

        return redirect()->route('laboratorio.admin.index')
            ->with('success', 'Mejora creada.');
    }

    public function edit(LabImprovement $labImprovement)
    {
        return view('laboratorio.admin.form', ['improvement' => $labImprovement]);
    }

    public function update(Request $request, LabImprovement $labImprovement)
    {
        $data = $this->validateImprovement($request);

        DB::transaction(function () use ($data, $labImprovement, $request) {
            if (!empty($data['es_destacada']) && !$labImprovement->es_destacada) {
                LabImprovement::where('es_destacada', true)->update(['es_destacada' => false]);
            }

            $labImprovement->update($data);

            $this->handleImages($request, $labImprovement);
        });

        return redirect()->route('laboratorio.admin.index')
            ->with('success', 'Mejora actualizada.');
    }

    public function toggleEstado(Request $request, LabImprovement $labImprovement)
    {
        $estado = $request->input('estado');
        abort_unless(in_array($estado, LabImprovement::ESTADOS, true), 422);

        $labImprovement->update(['estado' => $estado]);

        return back()->with('success', "Mejora marcada como '{$estado}'.");
    }

    public function destroy(LabImprovement $labImprovement)
    {
        $labImprovement->delete();
        return redirect()->route('laboratorio.admin.index')
            ->with('success', 'Mejora eliminada.');
    }

    public function orders()
    {
        $orders = LabOrder::with('user', 'items')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('laboratorio.admin.orders', compact('orders'));
    }

    public function confirmarPago(LabOrder $labOrder)
    {
        $labOrder->update([
            'estado'        => 'confirmado',
            'confirmado_at' => now(),
        ]);

        foreach ($labOrder->items as $item) {
            if ($item->estado === 'pendiente') {
                $item->update(['estado' => 'en_proceso']);
            }
        }

        UserNotification::create([
            'user_id'     => $labOrder->user_id,
            'type'        => 'success',
            'title'       => '✅ Pago confirmado · Laboratorio',
            'message'     => "BLStudio confirmó tu pago del pedido {$labOrder->order_number}. Empezamos a trabajar.",
            'action_url'  => route('laboratorio.historial'),
            'action_text' => 'Ver Laboratorio',
            'meta'        => ['lab_order_id' => $labOrder->id],
        ]);

        return back()->with('success', 'Pago confirmado. Cliente notificado.');
    }

    public function activarItem(LabOrder $labOrder, LabOrderItem $labOrderItem)
    {
        abort_unless($labOrderItem->lab_order_id === $labOrder->id, 404);

        $labOrderItem->update([
            'estado'      => 'activo',
            'activado_at' => now(),
        ]);

        $labOrder->refresh()->recomputeEstado();

        UserNotification::create([
            'user_id'     => $labOrder->user_id,
            'type'        => 'success',
            'title'       => '🚀 Una mejora ya está activa',
            'message'     => "“{$labOrderItem->nombre_snapshot}” ya está publicada en tu web.",
            'action_url'  => route('laboratorio.historial'),
            'action_text' => 'Ver Laboratorio',
            'meta'        => ['lab_order_id' => $labOrder->id, 'item_id' => $labOrderItem->id],
        ]);

        return back()->with('success', 'Mejora marcada como activa. Cliente notificado.');
    }

    public function configuracion()
    {
        $settings = [
            'whatsapp' => BusinessSetting::get('lab_developer_whatsapp', ''),
            'email'    => BusinessSetting::get('lab_developer_email', 'grandesligasarg@gmail.com'),
            'alias'    => BusinessSetting::get('lab_developer_alias', 'biglstudio'),
            'cbu'      => BusinessSetting::get('lab_developer_cbu', '0110383830038320470947'),
            'banco'    => BusinessSetting::get('lab_developer_banco', 'Banco Nación'),
            'titular'  => BusinessSetting::get('lab_developer_titular', 'BLStudio (Juan Ignacio Ibarlucia)'),
        ];

        return view('laboratorio.admin.configuracion', compact('settings'));
    }

    public function updateConfiguracion(Request $request)
    {
        $data = $request->validate([
            'whatsapp' => ['nullable', 'string', 'max:32'],
            'email'    => ['required', 'email', 'max:120'],
            'alias'    => ['nullable', 'string', 'max:60'],
            'cbu'      => ['nullable', 'string', 'max:32'],
            'banco'    => ['nullable', 'string', 'max:60'],
            'titular'  => ['nullable', 'string', 'max:120'],
        ]);

        foreach ($data as $key => $value) {
            BusinessSetting::set("lab_developer_{$key}", $value ?? '', 'string', "BLStudio Laboratorio · {$key}");
        }

        return back()->with('success', 'Configuración actualizada.');
    }

    private function validateImprovement(Request $request): array
    {
        return $request->validate([
            'nombre'             => ['required', 'string', 'max:120'],
            'descripcion_corta'  => ['required', 'string', 'max:160'],
            'descripcion_larga'  => ['nullable', 'string', 'max:2000'],
            'categoria'          => ['required', Rule::in(LabImprovement::CATEGORIAS)],
            'precio_usd'         => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'precio_descuento_usd' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'descuento_hasta'    => ['nullable', 'date'],
            'icono'              => ['nullable', 'string', 'max:16'],
            'es_destacada'       => ['nullable', 'boolean'],
            'es_popular'         => ['nullable', 'boolean'],
            'tiempo_estimado_horas' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'roi_estimado'       => ['nullable', 'string', 'max:120'],
            'estado'             => ['required', Rule::in(LabImprovement::ESTADOS)],
            'diferencias'        => ['nullable', 'array'],
            'diferencias.*.color' => ['nullable', 'string', 'max:16'],
            'diferencias.*.texto' => ['nullable', 'string', 'max:200'],
            'imagen_antes_file'  => ['nullable', 'image', 'max:4096'],
            'imagen_despues_file' => ['nullable', 'image', 'max:4096'],
        ]);
    }

    // ────────── CHANGELOG ──────────

    public function changelogIndex()
    {
        $entries = LabChangelogEntry::with('improvement')->orderByDesc('publicado_en')->paginate(20);
        return view('laboratorio.admin.changelog.index', compact('entries'));
    }

    public function changelogStore(Request $request)
    {
        $data = $request->validate([
            'tipo'   => ['required', Rule::in(array_keys(LabChangelogEntry::TIPOS))],
            'titulo' => ['required', 'string', 'max:160'],
            'cuerpo' => ['nullable', 'string', 'max:2000'],
            'icono'  => ['nullable', 'string', 'max:16'],
            'color'  => ['nullable', 'string', 'max:16'],
            'lab_improvement_id' => ['nullable', 'integer', 'exists:lab_improvements,id'],
            'publicado_en' => ['nullable', 'date'],
        ]);

        $data['visible'] = true;
        $data['publicado_en'] = $data['publicado_en'] ?? now();

        LabChangelogEntry::create($data);

        return back()->with('success', 'Entry del changelog publicado.');
    }

    public function changelogDestroy(LabChangelogEntry $entry)
    {
        $entry->delete();
        return back()->with('success', 'Entry eliminado.');
    }

    // ────────── BUNDLES ──────────

    public function bundlesIndex()
    {
        $bundles = LabBundle::with('improvements')->orderByDesc('created_at')->get();
        return view('laboratorio.admin.bundles.index', compact('bundles'));
    }

    public function bundleCreate()
    {
        $allImprovements = LabImprovement::publicada()->orderBy('nombre')->get();
        $bundle = new LabBundle(['estado' => 'borrador']);
        return view('laboratorio.admin.bundles.form', compact('bundle', 'allImprovements'));
    }

    public function bundleStore(Request $request)
    {
        $data = $this->validateBundle($request);

        $bundle = DB::transaction(function () use ($data) {
            $bundle = LabBundle::create([
                'nombre'             => $data['nombre'],
                'descripcion_corta'  => $data['descripcion_corta'] ?? null,
                'icono'              => $data['icono'] ?? null,
                'precio_bundle_usd'  => $data['precio_bundle_usd'],
                'estado'             => $data['estado'],
            ]);
            $bundle->improvements()->sync($data['improvement_ids']);
            return $bundle;
        });

        return redirect()->route('laboratorio.admin.bundles')->with('success', 'Bundle creado.');
    }

    public function bundleEdit(LabBundle $labBundle)
    {
        $allImprovements = LabImprovement::publicada()->orderBy('nombre')->get();
        $bundle = $labBundle->load('improvements');
        return view('laboratorio.admin.bundles.form', compact('bundle', 'allImprovements'));
    }

    public function bundleUpdate(Request $request, LabBundle $labBundle)
    {
        $data = $this->validateBundle($request);

        DB::transaction(function () use ($data, $labBundle) {
            $labBundle->update([
                'nombre'             => $data['nombre'],
                'descripcion_corta'  => $data['descripcion_corta'] ?? null,
                'icono'              => $data['icono'] ?? null,
                'precio_bundle_usd'  => $data['precio_bundle_usd'],
                'estado'             => $data['estado'],
            ]);
            $labBundle->improvements()->sync($data['improvement_ids']);
        });

        return redirect()->route('laboratorio.admin.bundles')->with('success', 'Bundle actualizado.');
    }

    public function bundleDestroy(LabBundle $labBundle)
    {
        $labBundle->delete();
        return back()->with('success', 'Bundle eliminado.');
    }

    private function validateBundle(Request $request): array
    {
        return $request->validate([
            'nombre'            => ['required', 'string', 'max:120'],
            'descripcion_corta' => ['nullable', 'string', 'max:200'],
            'icono'             => ['nullable', 'string', 'max:16'],
            'precio_bundle_usd' => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'estado'            => ['required', Rule::in(LabBundle::ESTADOS)],
            'improvement_ids'   => ['required', 'array', 'min:2'],
            'improvement_ids.*' => ['integer', 'exists:lab_improvements,id'],
        ]);
    }

    // ────────── CRÉDITOS ──────────

    public function creditsIndex()
    {
        $wallets = LabCreditWallet::with(['user', 'movements' => fn ($q) => $q->latest()->limit(5)])
            ->orderByDesc('balance_usd')->get();
        $clients = User::whereIn('role', ['admin', 'cajero'])->orderBy('name')->get();
        return view('laboratorio.admin.creditos.index', compact('wallets', 'clients'));
    }

    public function creditsGrant(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'monto_usd' => ['required', 'numeric', 'min:0.01', 'max:99999'],
            'descripcion' => ['required', 'string', 'max:200'],
        ]);

        $wallet = LabCreditWallet::forUser($data['user_id']);
        $wallet->credit((float) $data['monto_usd'], $data['descripcion'], auth()->id());

        UserNotification::create([
            'user_id'     => $data['user_id'],
            'type'        => 'success',
            'title'       => '💎 BLStudio te dio créditos del Lab',
            'message'     => "USD " . number_format($data['monto_usd'], 2) . " · {$data['descripcion']}",
            'action_url'  => route('laboratorio.index'),
            'action_text' => 'Ver Laboratorio',
        ]);

        return back()->with('success', 'Créditos otorgados y cliente notificado.');
    }

    private function handleImages(Request $request, LabImprovement $imp): void
    {
        $disk = Storage::disk('public');

        if ($request->hasFile('imagen_antes_file')) {
            if ($imp->imagen_antes && $disk->exists($imp->imagen_antes)) {
                $disk->delete($imp->imagen_antes);
            }
            $path = $request->file('imagen_antes_file')->store('lab/improvements', 'public');
            $imp->update(['imagen_antes' => $path]);
        }

        if ($request->hasFile('imagen_despues_file')) {
            if ($imp->imagen_despues && $disk->exists($imp->imagen_despues)) {
                $disk->delete($imp->imagen_despues);
            }
            $path = $request->file('imagen_despues_file')->store('lab/improvements', 'public');
            $imp->update(['imagen_despues' => $path]);
        }

        if (!empty($request->input('diferencias'))) {
            $diferencias = collect($request->input('diferencias'))
                ->filter(fn ($d) => !empty($d['texto']))
                ->values()
                ->all();
            $imp->update(['diferencias' => $diferencias]);
        }
    }
}
