<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    public function edit(): View
    {
        $user = Auth::user();
        $addresses = $user->addresses()->orderByDesc('is_default')->latest()->get();
        [$firstName, $lastName] = $this->splitName($user->name);

        return view('profile.edit', compact('user', 'addresses', 'firstName', 'lastName'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'required|string|max:30',
        ]);

        $user = Auth::user();
        $fullName = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));

        $user->update([
            'name' => $fullName ?: $user->name,
            'phone' => $data['phone'],
        ]);

        return back()->with('success', 'Tus datos fueron actualizados.');
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $data = $this->validateAddress($request);
        $user = Auth::user();

        DB::transaction(function () use ($user, $data) {
            $makeDefault = (bool) ($data['is_default'] ?? false);

            if ($makeDefault) {
                $user->addresses()->update(['is_default' => false]);
            }

            $user->addresses()->create([
                'name' => $data['name'],
                'street' => $data['street'],
                'number' => $data['number'],
                'neighborhood' => $data['neighborhood'] ?? '',
                'city' => $data['city'] ?? 'Olavarría',
                'state' => $data['state'] ?? 'Buenos Aires',
                'postal_code' => $data['postal_code'] ?? '',
                'reference' => $data['reference'] ?? null,
                'is_default' => $makeDefault || !$user->addresses()->exists(),
            ]);
        });

        return back()->with('success', 'Dirección guardada correctamente.');
    }

    public function updateAddress(Request $request, Address $address): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($address->user_id === $user->id, 403);

        $data = $this->validateAddress($request);

        DB::transaction(function () use ($user, $address, $data) {
            $makeDefault = (bool) ($data['is_default'] ?? false);

            if ($makeDefault) {
                $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            }

            $address->update([
                'name' => $data['name'],
                'street' => $data['street'],
                'number' => $data['number'],
                'neighborhood' => $data['neighborhood'] ?? '',
                'city' => $data['city'] ?? 'Olavarría',
                'state' => $data['state'] ?? 'Buenos Aires',
                'postal_code' => $data['postal_code'] ?? '',
                'reference' => $data['reference'] ?? null,
                'is_default' => $makeDefault ? true : $address->is_default,
            ]);
        });

        return back()->with('success', 'Dirección actualizada.');
    }

    public function destroyAddress(Address $address): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($address->user_id === $user->id, 403);

        DB::transaction(function () use ($user, $address) {
            $wasDefault = $address->is_default;
            $address->delete();

            if ($wasDefault) {
                $next = $user->addresses()->first();
                if ($next) {
                    $next->update(['is_default' => true]);
                }
            }
        });

        return back()->with('success', 'Dirección eliminada.');
    }

    public function setDefaultAddress(Address $address): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($address->user_id === $user->id, 403);

        DB::transaction(function () use ($user, $address) {
            $user->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return back()->with('success', 'Dirección predeterminada actualizada.');
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:80',
            'street' => 'required|string|max:120',
            'number' => 'required|string|max:30',
            'neighborhood' => 'nullable|string|max:120',
            'city' => 'nullable|string|max:120',
            'state' => 'nullable|string|max:120',
            'postal_code' => 'nullable|string|max:20',
            'reference' => 'nullable|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);
    }

    private function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];
        $first = $parts[0] ?? '';
        $last = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';

        return [$first, $last];
    }
}
