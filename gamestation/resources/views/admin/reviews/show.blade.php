@extends('admin.layout')

@section('title', 'Chi tiết đánh giá')
@section('page_title', 'Chi tiết đánh giá')
@section('breadcrumb', 'Chi tiết đánh giá')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Nội dung đánh giá</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label><strong>Sản phẩm:</strong></label>
                    <div class="d-flex align-items-center">
                        @php
                            $productImage = $review->product?->primaryImage?->image_path
                                ?? $review->product?->images?->first()?->image_path;
                        @endphp
                        <img
                            src="{{ $productImage ? asset($productImage) : 'https://placehold.co/80x80' }}"
                            alt="{{ $review->product->name }}"
                            style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #dee2e6; margin-right: 15px;"
                        >
                        <div>
                            <p class="mb-0 font-weight-bold">{{ $review->product->name }}</p>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label><strong>Người dùng:</strong></label>
                    <p>{{ $review->user->name ?? 'N/A' }}</p>
                </div>
                <div class="form-group">
                    <label><strong>Đánh giá:</strong></label>
                    <p>
                        <span class="badge badge-warning">{{ $review->rating }}/5</span>
                    </p>
                </div>
                <div class="form-group">
                    <label><strong>Bình luận:</strong></label>
                    <div class="card bg-light">
                        <div class="card-body" style="white-space: pre-wrap; word-wrap: break-word; overflow-wrap: break-word;">
                            {{ $review->comment }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(!$review->admin_reply)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Phản hồi từ admin</h3>
            </div>
            <form action="{{ route('admin.reviews.reply', $review) }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>Phản hồi <span class="text-danger">*</span></label>
                        <textarea name="reply" class="form-control @error('reply') is-invalid @enderror" rows="4" required></textarea>
                        @error('reply')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Gửi phản hồi</button>
                </div>
            </form>
        </div>
        @else
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Phản hồi từ admin</h3>
            </div>
            <div class="card-body">
                <div class="card bg-light mb-3">
                    <div class="card-body" style="white-space: pre-wrap; word-wrap: break-word; overflow-wrap: break-word;">
                        {{ $review->admin_reply }}
                    </div>
                </div>
                <p class="text-muted text-sm">Phản hồi lúc: {{ $review->admin_replied_at?->format('d/m/Y H:i') }}</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
