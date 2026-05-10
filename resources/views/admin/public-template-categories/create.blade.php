@extends('layouts.admin')

@section('title', __('Create Template Category'))
@section('page-title', __('Create Template Category'))

@section('content')
<div class="space-y-6">
    <nav aria-label="Breadcrumb">
        <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <li><a href="{{ route('admin.dashboard') }}" class="font-medium text-gray-600 transition hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400">{{ __('Home') }}</a></li>
            <li aria-hidden="true">/</li>
            <li><a href="{{ route('admin.public-template-categories.index') }}" class="font-medium text-gray-600 transition hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400">{{ __('Template Categories') }}</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-gray-900 dark:text-gray-100">{{ __('Create') }}</li>
        </ol>
    </nav>

    <x-card>
        <form method="POST" action="{{ route('admin.public-template-categories.store') }}" class="space-y-6">
            @include('admin.public-template-categories.form', ['category' => $category])
            <div class="flex justify-end gap-2">
                <x-button href="{{ route('admin.public-template-categories.index') }}" variant="secondary">{{ __('Cancel') }}</x-button>
                <x-button type="submit" variant="primary">{{ __('Save') }}</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
