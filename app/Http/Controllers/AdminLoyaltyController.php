<?php

namespace App\Http\Controllers;

use App\Mail\LoyaltyRedemptionApprovedMail;
use App\Mail\LoyaltyRedemptionDeliveredMail;
use App\Models\LoyaltyRedemption;
use App\Models\LoyaltySetting;
use App\Models\UserLoyaltyMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AdminLoyaltyController extends Controller
{
    public function index()
    {
        $setting = LoyaltySetting::active();
        $pendingRedemptions = LoyaltyRedemption::with('user')
            ->whereIn('status', ['pending', 'approved'])
            ->latest('id')
            ->paginate(20);

        $recentMovements = UserLoyaltyMovement::with('user', 'order')
            ->latest('id')
            ->take(30)
            ->get();

        return view('admin.loyalty.index', compact('setting', 'pendingRedemptions', 'recentMovements'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'target_stickers' => 'required|integer|min:1|max:500',
            'reward_value' => 'required|string|max:255',
            'reward_description' => 'nullable|string|max:1000',
            'album_help_message' => 'nullable|string|max:1500',
            'redemption_instructions' => 'nullable|string|max:2000',
            'coupon_code' => 'nullable|string|max:50',
            'reward_category' => 'required|in:coupon,physical,other',
            'reward_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $currentSetting = LoyaltySetting::active();
        $rewardImagePath = $currentSetting->reward_image;

        if ($request->hasFile('reward_image')) {
            $rewardImagePath = $request->file('reward_image')->store('rewards', 'public_images');
        }

        DB::transaction(function () use ($data, $rewardImagePath) {
            LoyaltySetting::query()->update(['is_active' => false]);
            LoyaltySetting::create([
                'target_stickers' => $data['target_stickers'],
                'reward_type' => 'custom',
                'reward_value' => $data['reward_value'],
                'reward_description' => $data['reward_description'] ?? null,
                'album_help_message' => $data['album_help_message'] ?? null,
                'redemption_instructions' => $data['redemption_instructions'] ?? null,
                'coupon_code' => $data['coupon_code'] ?? null,
                'reward_category' => $data['reward_category'],
                'reward_image' => $rewardImagePath,
                'is_active' => true,
            ]);
        });

        return back()->with('success', 'Configuracion de fidelizacion actualizada.');
    }

    public function approveRedemption(Request $request, int $id)
    {
        $redemption = LoyaltyRedemption::with('user')->findOrFail($id);
        if ($redemption->status !== 'pending') {
            return back()->with('error', 'Solo se pueden aprobar canjes pendientes.');
        }

        $redemption->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        // Enviar email de aprobación al cliente
        if ($redemption->user && $redemption->user->email) {
            try {
                Mail::to($redemption->user->email)->send(new LoyaltyRedemptionApprovedMail($redemption));
            } catch (\Throwable $e) {
                \Log::warning('No se pudo enviar email de canje aprobado', [
                    'redemption_id' => $redemption->id,
                    'user_id' => $redemption->user_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Notificación web al cliente
        if ($redemption->user) {
            \App\Services\NotificationService::notifyRedemptionApproved($redemption->user, $redemption);
        }

        return back()->with('success', 'Canje aprobado.');
    }

    public function deliverRedemption(int $id)
    {
        $redemption = LoyaltyRedemption::with('user')->findOrFail($id);
        if (!in_array($redemption->status, ['approved', 'pending'], true)) {
            return back()->with('error', 'El canje ya fue procesado.');
        }

        $redemption->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        // Marcar como visto por el cliente (entregado ya lo "vio" implícitamente al retirar)
        if (!$redemption->client_seen_delivered_at) {
            $redemption->update(['client_seen_delivered_at' => now()]);
        }

        // Enviar email de entrega al cliente
        if ($redemption->user && $redemption->user->email) {
            try {
                Mail::to($redemption->user->email)->send(new LoyaltyRedemptionDeliveredMail($redemption));
            } catch (\Throwable $e) {
                \Log::warning('No se pudo enviar email de canje entregado', [
                    'redemption_id' => $redemption->id,
                    'user_id' => $redemption->user_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Notificación web al cliente
        if ($redemption->user) {
            \App\Services\NotificationService::notifyRedemptionDelivered($redemption->user, $redemption);
        }

        return back()->with('success', 'Canje marcado como entregado.');
    }
}
