@extends('admin.layout')

@section('title', 'Thêm nhà phát hành')
@section('page_title', 'Thêm nhà phát hành')
@section('breadcrumb', 'Thêm nhà phát hành')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.publishers.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Tên nhà phát hành <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required value="{{ old('name') }}">
                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="mt-3">
                <button class="btn btn-primary">Thêm</button>
                <a href="{{ route('admin.publishers.index') }}" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection
