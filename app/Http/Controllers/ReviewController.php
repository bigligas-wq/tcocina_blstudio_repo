<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Store a new review
     */
    public function store(Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        $userId = Auth::id();
        $user = Auth::user();

        // Check if user already left a review (one review per user about the web)
        if ($userId) {
            $existingReview = Review::where('user_id', $userId)->first();
            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya has dejado una reseña sobre la web.',
                ], 400);
            }
        }

        // Resolve order_id: use provided one, or fallback to latest user order
        $orderId = $request->order_id;
        if (!$orderId && $userId) {
            $latestOrder = Order::where('user_id', $userId)->latest('id')->first();
            $orderId = $latestOrder?->id;
        }

        // Verify order ownership if order_id is resolved
        if ($orderId && $userId) {
            $order = Order::findOrFail($orderId);
            if ($order->user_id !== $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para reseñar este pedido.',
                ], 403);
            }
        }

        $review = Review::create([
            'user_id' => $userId,
            'order_id' => $orderId,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'customer_name' => $user?->name ?? 'Cliente',
        ]);

        return response()->json([
            'success' => true,
            'message' => '¡Gracias por tu reseña!',
            'review' => $review,
        ]);
    }

    /**
     * Display all reviews for admin
     */
    public function index(Request $request)
    {
        $query = Review::with(['user', 'order']);

        // Filter by rating
        if ($request->has('rating') && $request->rating) {
            $query->where('rating', $request->rating);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.reviews', compact('reviews'));
    }
}
