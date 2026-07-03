@extends('admin.layout')

@section('title', 'Sửa nhà phát hành')
@section('page_title', 'Sửa nhà phát hành')
@section('breadcrumb', 'Sửa nhà phát hành')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.publishers.update', $publisher) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Tên nhà phát hành <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required value="{{ old('name', $publisher->name) }}">
                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="mt-3">
                <button class="btn btn-primary">Lưu</button>
                <a href="{{ route('admin.publishers.index') }}" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection
