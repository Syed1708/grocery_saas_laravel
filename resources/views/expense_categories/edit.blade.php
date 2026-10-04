@extends('tyro-dashboard::layouts.admin')

@section('title', 'Edit Expense Category')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('expense-categories.index') }}">Categories</a>
<span class="breadcrumb-separator">/</span>
<span>Edit</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Expense Category</h1>
            <p class="page-description">Update category information.</p>
        </div>
        <a href="{{ route('expense-categories.index') }}" class="btn btn-secondary">← Back</a>
    </div>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-body">
        <form action="{{ route('expense-categories.update', ['expense_category' => $expenseCategory->id]) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label" for="name">Category Name <span style="color: var(--danger, #ef4444);">*</span></label>
                <input type="text" id="name" name="name" class="form-input" value="{{ old('name', $expenseCategory->name) }}" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="code">Category Code <span style="color: var(--danger, #ef4444);">*</span></label>
                    <input type="text" id="code" name="code" class="form-input" value="{{ old('code', $expenseCategory->code) }}" required style="font-family: monospace;">
                </div>
                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="active" {{ $expenseCategory->status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $expenseCategory->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" class="form-textarea" rows="2">{{ old('description', $expenseCategory->description) }}</textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                <a href="{{ route('expense-categories.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Category</button>
            </div>
        </form>
    </div>
</div>
@endsection