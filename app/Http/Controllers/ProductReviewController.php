<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductReviewImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductReviewController extends Controller
{
    /**
     * Display a listing of reviews for a specific product.
     */
    public function index($productId)
    {
        $product = Product::findOrFail($productId);
        $reviews = $product->reviews()->with('images')->latest()->paginate(10);

        return response()->json([
            'product' => $product,
            'reviews' => $reviews,
            'avg_rating' => $product->avg_rating,
            'review_count' => $product->review_count,
        ]);
    }

    /**
     * Display the specified review.
     */
    public function show($id)
    {
        $review = ProductReview::with(['images', 'history', 'user:id,name'])->findOrFail($id);

        return response()->json($review);
    }

    /**
     * Store a newly created review in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'order_id' => 'required|exists:orders,id',
            'order_item_id' => 'nullable|exists:order_items,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $product = Product::findOrFail($request->product_id);
        $order = Order::findOrFail($request->order_id);

        // Check if user owns the order
        if ($order->user_id !== $user->id) {
            return response()->json(['error' => 'No tienes permiso para reseñar este pedido'], 403);
        }

        // Check if user already reviewed this product for this order
        $existingReview = ProductReview::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->where('order_id', $request->order_id)
            ->first();

        if ($existingReview) {
            return response()->json(['error' => 'Ya has reseñado este producto para este pedido'], 400);
        }

        // Create the review
        $review = ProductReview::create([
            'user_id' => $user->id,
            'product_id' => $request->product_id,
            'order_id' => $request->order_id,
            'order_item_id' => $request->order_item_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'status' => 'pending',
        ]);

        // Create history entry
        $review->history()->create([
            'rating' => $review->rating,
            'comment' => $review->comment,
            'change_type' => 'created',
            'changed_by' => 'user',
            'changed_by_user_id' => $user->id,
        ]);

        // Mark order item as reviewed if provided
        if ($request->order_item_id) {
            $orderItem = OrderItem::find($request->order_item_id);
            if ($orderItem) {
                $orderItem->update(['reviewed_at' => now()]);
            }
        }

        return response()->json($review, 201);
    }

    /**
     * Update the specified review.
     */
    public function update(Request $request, $id)
    {
        $review = ProductReview::findOrFail($id);
        $user = Auth::user();

        // Check if user owns the review
        if ($review->user_id !== $user->id) {
            return response()->json(['error' => 'No tienes permiso para editar esta reseña'], 403);
        }

        // Check if review can be edited (only pending reviews)
        if (!$review->canEdit()) {
            return response()->json(['error' => 'Esta reseña ya no puede editarse'], 400);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create history entry before updating
        $review->history()->create([
            'rating' => $review->rating,
            'comment' => $review->comment,
            'change_type' => 'edited',
            'changed_by' => 'user',
            'changed_by_user_id' => $user->id,
        ]);

        // Update the review
        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_edited' => true,
            'edited_at' => now(),
        ]);

        return response()->json($review);
    }

    /**
     * Remove the specified review.
     */
    public function destroy($id)
    {
        $review = ProductReview::findOrFail($id);
        $user = Auth::user();

        // Check if user owns the review
        if ($review->user_id !== $user->id) {
            return response()->json(['error' => 'No tienes permiso para eliminar esta reseña'], 403);
        }

        // Check if review can be deleted (only pending reviews)
        if (!$review->canEdit()) {
            return response()->json(['error' => 'Esta reseña ya no puede eliminarse'], 400);
        }

        $review->delete();

        return response()->json(['message' => 'Reseña eliminada']);
    }

    /**
     * Get current user's reviews.
     */
    public function myReviews()
    {
        $user = Auth::user();
        $reviews = ProductReview::where('user_id', $user->id)
            ->with(['product:id,name,image', 'images'])
            ->latest()
            ->paginate(10);

        return response()->json($reviews);
    }

    /**
     * Get pending reviews for current user.
     */
    public function pendingReviews()
    {
        $user = Auth::user();

        // Get delivered orders that haven't been fully reviewed
        $deliveredOrders = Order::where('user_id', $user->id)
            ->where('status', 'delivered')
            ->with(['orderItems' => function ($query) {
                $query->whereNull('reviewed_at');
            }, 'orderItems.product:id,name,image'])
            ->whereHas('orderItems', function ($query) {
                $query->whereNull('reviewed_at');
            })
            ->latest()
            ->get();

        return response()->json([
            'orders' => $deliveredOrders,
            'pending_count' => $deliveredOrders->sum(function ($order) {
                return $order->orderItems->whereNull('reviewed_at')->count();
            }),
        ]);
    }

    /**
     * Upload image for review.
     */
    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'review_id' => 'required|exists:product_reviews,id',
            'image' => 'required|image|max:5120', // Max 5MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $review = ProductReview::findOrFail($request->review_id);
        $user = Auth::user();

        // Check if user owns the review
        if ($review->user_id !== $user->id) {
            return response()->json(['error' => 'No tienes permiso para agregar imágenes a esta reseña'], 403);
        }

        // Check if review can be edited
        if (!$review->canEdit()) {
            return response()->json(['error' => 'Esta reseña ya no puede editarse'], 400);
        }

        // Check max images (5)
        if ($review->images()->count() >= 5) {
            return response()->json(['error' => 'Máximo 5 imágenes por reseña'], 400);
        }

        // Store the image
        $path = $request->file('image')->store('review-images', 'public');

        $image = ProductReviewImage::create([
            'product_review_id' => $review->id,
            'image_path' => $path,
        ]);

        return response()->json($image, 201);
    }

    /**
     * Delete image from review.
     */
    public function deleteImage($imageId)
    {
        $image = ProductReviewImage::findOrFail($imageId);
        $review = $image->review;
        $user = Auth::user();

        // Check if user owns the review
        if ($review->user_id !== $user->id) {
            return response()->json(['error' => 'No tienes permiso para eliminar esta imagen'], 403);
        }

        // Check if review can be edited
        if (!$review->canEdit()) {
            return response()->json(['error' => 'Esta reseña ya no puede editarse'], 400);
        }

        // Delete the file
        Storage::disk('public')->delete($image->image_path);

        // Delete the record
        $image->delete();

        return response()->json(['message' => 'Imagen eliminada']);
    }

    /**
     * Report a review.
     */
    public function report(Request $request, $id)
    {
        $review = ProductReview::findOrFail($id);
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:255',
            'details' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if user already reported this review
        $existingReport = $review->reports()
            ->where('reporter_id', $user->id)
            ->first();

        if ($existingReport) {
            return response()->json(['error' => 'Ya has reportado esta reseña'], 400);
        }

        // Create the report
        $review->reports()->create([
            'reporter_id' => $user->id,
            'reason' => $request->reason,
            'details' => $request->details,
            'status' => 'pending',
        ]);

        // Increment report count on review
        $review->increment('report_count');

        return response()->json(['message' => 'Reseña reportada'], 201);
    }
}
