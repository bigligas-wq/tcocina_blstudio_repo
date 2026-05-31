@extends('layouts.admin')

@section('title', 'Reseñas - TCocina Admin')
@section('page-title', 'Reseñas de Clientes')

@section('content')
    <!-- Stats Cards -->
    <div class="row mb-4 g-2">
        <div class="col-6 col-md-3">
            <div class="card stat-card-modern border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-white-50 small mb-1">Total Reseñas</div>
                            <div class="h3 mb-0 fw-bold">{{ $reviews->total() }}</div>
                        </div>
                        <div class="text-white-50">
                            <i data-lucide="message-square" style="width: 2rem; height: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card-modern border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-white-50 small mb-1">Promedio</div>
                            <div class="h3 mb-0 fw-bold">{{ number_format($reviews->avg('rating') ?? 0, 1) }} <i class="fas fa-star" style="font-size: 1rem;"></i></div>
                        </div>
                        <div class="text-white-50">
                            <i data-lucide="star" style="width: 2rem; height: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card-modern border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-white-50 small mb-1">5 Estrellas</div>
                            <div class="h3 mb-0 fw-bold">{{ $reviews->where('rating', 5)->count() }}</div>
                        </div>
                        <div class="text-white-50">
                            <i data-lucide="thumbs-up" style="width: 2rem; height: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card-modern border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-white-50 small mb-1">1-2 Estrellas</div>
                            <div class="h3 mb-0 fw-bold">{{ $reviews->whereIn('rating', [1, 2])->count() }}</div>
                        </div>
                        <div class="text-white-50">
                            <i data-lucide="alert-circle" style="width: 2rem; height: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reviews') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Calificación</label>
                    <select name="rating" class="form-select">
                        <option value="">Todas</option>
                        <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 Estrellas</option>
                        <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 Estrellas</option>
                        <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 Estrellas</option>
                        <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 Estrellas</option>
                        <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 Estrella</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Desde</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Hasta</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100" style="background: linear-gradient(135deg, #0096c7 0%, #0c6568 100%); border: none;">
                        <i data-lucide="filter" style="width: 1rem; height: 1rem;"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reviews Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0">
            <h6 class="m-0 fw-bold text-dark">
                <i data-lucide="message-square" style="width: 1.2rem; height: 1.2rem;"></i>
                Todas las Reseñas
            </h6>
        </div>
        <div class="card-body">
            @if ($reviews->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Pedido #</th>
                                <th>Calificación</th>
                                <th>Comentario</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reviews as $review)
                                <tr>
                                    <td>
                                        <small class="text-muted">{{ $review->created_at->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $review->customer_name }}</div>
                                        @if ($review->user)
                                            <small class="text-muted">{{ $review->user->email }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($review->order_id && $review->order)
                                            <a href="{{ route('admin.order.details', $review->order_id) }}" class="text-decoration-none" style="color: #0096c7;">
                                                #{{ $review->order->order_number }}
                                            </a>
                                        @else
                                            <span class="text-muted"><i class="fas fa-minus"></i></span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-warning">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $review->rating)
                                                    <i class="fas fa-star"></i>
                                                @else
                                                    <i class="far fa-star text-muted"></i>
                                                @endif
                                            @endfor
                                            <span class="ms-1 fw-bold">{{ $review->rating }}/5</span>
                                        </div>
                                    </td>
                                    <td style="max-width:260px">
                                        @if ($review->comment)
                                            @if (strlen($review->comment) > 90)
                                                <small class="text-muted">{{ Str::limit($review->comment, 90) }}</small>
                                                <button class="btn btn-link btn-sm p-0 ms-1 review-expand-btn"
                                                    style="font-size:.75rem;color:#0096c7;vertical-align:baseline"
                                                    data-name="{{ e($review->customer_name) }}"
                                                    data-rating="{{ $review->rating }}"
                                                    data-comment="{{ e($review->comment) }}"
                                                    data-bs-toggle="modal" data-bs-target="#reviewCommentModal">
                                                    ver todo
                                                </button>
                                            @else
                                                <small class="text-muted">{{ $review->comment }}</small>
                                            @endif
                                        @else
                                            <small class="text-muted fst-italic">Sin comentario</small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $reviews->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="text-center py-5">
                    <i data-lucide="message-square" style="width: 4rem; height: 4rem; color: #dadce0;"></i>
                    <p class="text-muted mt-3">No hay reseñas aún.</p>
                </div>
            @endif
        </div>
    </div>

<!-- Modal: comentario completo -->
<div class="modal fade" id="reviewCommentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#0f1626;border:1px solid #1a2840;border-radius:14px;">
            <div class="modal-header" style="border-color:#1a2840;">
                <div>
                    <h6 class="modal-title fw-bold mb-0" id="modalReviewName"></h6>
                    <div id="modalReviewStars" class="text-warning mt-1" style="font-size:.9rem;"></div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="modalReviewComment" style="color:#c8d6e8;line-height:1.7;margin:0;white-space:pre-wrap;"></p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.review-expand-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var name    = this.dataset.name;
        var rating  = parseInt(this.dataset.rating);
        var comment = this.dataset.comment;

        document.getElementById('modalReviewName').textContent = name;

        var stars = '';
        for (var i = 1; i <= 5; i++) {
            stars += i <= rating
                ? '<i class="fas fa-star"></i>'
                : '<i class="far fa-star" style="opacity:.4"></i>';
        }
        document.getElementById('modalReviewStars').innerHTML = stars;
        document.getElementById('modalReviewComment').textContent = comment;
    });
});
</script>
@endpush

@endsection
