<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\LoyaltyRedemption;
use App\Models\LoyaltySetting;
use App\Models\UserLoyaltyWallet;
use App\Services\LoyaltyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $loyaltyOffline = (bool) BusinessSetting::get('loyalty_offline', false);
        $loyaltyOfflineMessage = (string) BusinessSetting::get(
            'loyalty_offline_message',
            'Por el momento Mi álbum no está disponible. Intenta nuevamente más tarde.'
        );

        if ($loyaltyOffline) {
            return view('loyalty.dashboard', compact('loyaltyOffline', 'loyaltyOfflineMessage'));
        }

        $setting = LoyaltySetting::active();
        $wallet = UserLoyaltyWallet::firstOrCreate(
            ['user_id' => $user->id],
            ['current_stickers' => 0, 'total_earned' => 0, 'total_redeemed' => 0]
        );

        $pendingRedemption = $user->loyaltyRedemptions()
            ->whereIn('status', ['pending', 'approved'])
            ->latest('id')
            ->first();

        // Historial de canjes completados (entregados)
        $redemptionHistory = $user->loyaltyRedemptions()
            ->where('status', 'delivered')
            ->orderBy('delivered_at', 'desc')
            ->get();

        $canRedeem = $wallet->current_stickers >= $setting->target_stickers && !$pendingRedemption;

        return view('loyalty.dashboard', compact(
            'wallet',
            'setting',
            'canRedeem',
            'pendingRedemption',
            'redemptionHistory',
            'loyaltyOffline',
            'loyaltyOfflineMessage'
        ));
    }

    public function requestRedemption(Request $request, LoyaltyService $loyaltyService): RedirectResponse
    {
        if ((bool) BusinessSetting::get('loyalty_offline', false)) {
            $message = (string) BusinessSetting::get(
                'loyalty_offline_message',
                'Por el momento Mi álbum no está disponible. Intenta nuevamente más tarde.'
            );

            return back()->with('error', $message);
        }

        try {
            $loyaltyService->requestRedemption($request->user());
            return back()->with('success', 'Canje solicitado correctamente.');
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        } catch (\Throwable $exception) {
            return back()->with('error', 'No se pudo solicitar el canje. Intenta nuevamente.');
        }
    }

    /**
     * Retorna notificaciones pendientes para el cliente (canjes aprobados/entregados no vistos).
     */
    public function pendingNotifications(Request $request): JsonResponse
    {
        $user = $request->user();

        // Redemptions aprobadas que el cliente aún no vio
        $approved = LoyaltyRedemption::where('user_id', $user->id)
            ->whereNotNull('approved_at')
            ->whereNull('client_seen_approved_at')
            ->where('status', '!=', 'cancelled')
            ->select('id', 'status', 'approved_at', 'reward_snapshot')
            ->get();

        // Redemptions entregadas que el cliente aún no vio
        $delivered = LoyaltyRedemption::where('user_id', $user->id)
            ->whereNotNull('delivered_at')
            ->whereNull('client_seen_delivered_at')
            ->where('status', 'delivered')
            ->select('id', 'status', 'delivered_at', 'reward_snapshot')
            ->get();

        return response()->json([
            'success' => true,
            'approved' => $approved,
            'delivered' => $delivered,
        ]);
    }

    /**
     * Marca una notificación de canje como vista por el cliente.
     */
    public function markNotificationSeen(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $redemption = LoyaltyRedemption::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        $updates = [];

        if ($redemption->approved_at && !$redemption->client_seen_approved_at) {
            $updates['client_seen_approved_at'] = now();
        }
        if ($redemption->delivered_at && !$redemption->client_seen_delivered_at) {
            $updates['client_seen_delivered_at'] = now();
        }

        if (!empty($updates)) {
            $redemption->update($updates);
        }

        return response()->json(['success' => true]);
    }
}
