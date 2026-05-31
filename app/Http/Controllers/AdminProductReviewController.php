<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductReviewReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminProductReviewController extends Controller
{
    /**
     * Display a listing of all reviews.
     */
    public function index(Request $request)
    {
        $query = ProductReview::with(['user:id,name', 'product:id,name', 'images']);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by rating
        if ($request->has('rating') && $request->rating !== '') {
            $query->where('rating', $request->rating);
        }

        // Filter by product
        if ($request->has('product_id') && $request->product_id !== '') {
            $query->where('product_id', $request->product_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $reviews = $query->latest()->paginate(20);

        return response()->json($reviews);
    }

    /**
     * Display the specified review with history.
     */
    public function show($id)
    {
        $review = ProductReview::with([
            'user:id,name',
            'product:id,name,image',
            'order:id,order_number',
            'orderItem:id',
            'images',
            'history' => function ($query) {
                $query->with('changedByUser:id,name')->orderBy('created_at', 'desc');
            },
            'reports' => function ($query) {
                $query->with('reporter:id,name')->latest();
            }
        ])->findOrFail($id);

        return response()->json($review);
    }

    /**
     * Approve the specified review.
     */
    public function approve(Request $request, $id)
    {
        $review = ProductReview::findOrFail($id);

        $review->approve($request->notes);

        return response()->json(['message' => 'Reseña aprobada']);
    }

    /**
     * Reject the specified review.
     */
    public function reject(Request $request, $id)
    {
        $review = ProductReview::findOrFail($id);

        $review->reject($request->notes);

        return response()->json(['message' => 'Reseña rechazada']);
    }

    /**
     * Remove the specified review.
     */
    public function destroy($id)
    {
        $review = ProductReview::findOrFail($id);

        // Update product stats before deleting
        $product = $review->product;
        $review->delete();
        $product->updateReviewStats();

        return response()->json(['message' => 'Reseña eliminada']);
    }

    /**
     * Get review statistics.
     */
    public function getStats()
    {
        $stats = [
            'total' => ProductReview::count(),
            'avg_rating' => ProductReview::avg('rating'),
            'approved' => ProductReview::where('status', 'approved')->count(),
            'pending' => ProductReview::where('status', 'pending')->count(),
            'rejected' => ProductReview::where('status', 'rejected')->count(),
            'rating_distribution' => [
                '5' => ProductReview::where('rating', 5)->count(),
                '4' => ProductReview::where('rating', 4)->count(),
                '3' => ProductReview::where('rating', 3)->count(),
                '2' => ProductReview::where('rating', 2)->count(),
                '1' => ProductReview::where('rating', 1)->count(),
            ],
            'pending_reports' => ProductReviewReport::where('status', 'pending')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Display a listing of review reports.
     */
    public function reports(Request $request)
    {
        $query = ProductReviewReport::with([
            'review:id,rating,comment,product_id',
            'review.product:id,name',
            'review.user:id,name',
            'reporter:id,name'
        ]);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by reason
        if ($request->has('reason') && $request->reason !== '') {
            $query->where('reason', $request->reason);
        }

        $reports = $query->latest()->paginate(20);

        return response()->json($reports);
    }

    /**
     * Review a report.
     */
    public function reviewReport(Request $request, $reportId)
    {
        $report = ProductReviewReport::findOrFail($reportId);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'status' => 'required|in:reviewed,dismissed',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $report->update([
            'status' => $request->status,
        ]);

        // If dismissing, decrement report count
        if ($request->status === 'dismissed') {
            $report->review->decrement('report_count');
        }

        return response()->json(['message' => 'Reporte revisado']);
    }
}
