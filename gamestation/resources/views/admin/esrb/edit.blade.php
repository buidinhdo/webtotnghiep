@extends('admin.layout')

@section('title', 'Sửa ESRB')
@section('page_title', 'Sửa ESRB')
@section('breadcrumb', 'Sửa ESRB')

@section('content')
<div class="card">
    <form action="{{ route('admin.esrb.update', ['esrb' => $esrbRating->id]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="form-group mb-3">
                <label for="code" class="form-label">Mã ESRB <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $esrbRating->code) }}" required>
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mb-3">
                <label for="name" class="form-label">Tên ESRB <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $esrbRating->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mb-3">
                <label for="age_group" class="form-label">Nhóm tuổi</label>
                <input type="text" class="form-control @error('age_group') is-invalid @enderror" id="age_group" name="age_group" value="{{ old('age_group', $esrbRating->age_group) }}">
                @error('age_group')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mb-3">
                <label for="description" class="form-label">Mô tả</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $esrbRating->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $esrbRating->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">
                    Kích hoạt
                </label>
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Cập nhật
            </button>
            <a href="{{ route('admin.esrb.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Hủy
            </a>
        </div>
    </form>
</div>
@endsection
