@extends('layouts.master')

@section('title', 'Dashboard')

@section('css')
@endsection

@section('breadcrumb-items')
    {{-- <li class="breadcrumb-item active">{{ __('Dashboard') }}</li> --}}
@endsection

@section('content')
    @section('content')
<div class="row g-4">

    <!-- Welcome Card -->
    <div class="col-12">
        <div class="card border-0 shadow-lg welcome-card">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h3 class="mb-1 fw-bold text-primary">
                        Welcome back, {{ auth()->user()->name ?? 'Admin' }} 👋
                    </h3>
                    <p class="mb-0 text-muted">
                        Here’s what’s happening in Happy Plush today.
                    </p>
                </div>
                <div>
                    <img src="{{ asset(\App\Helpers\Helper::getLogoLight()) }}" alt="Happy Plush" height="70">
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="col-md-3">
        <div class="card stat-card total-users shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-muted">Total Users</h6>
                <h2 class="fw-bold text-primary">
                    {{ $totalUsers ?? 0 }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card active-users shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-muted">Active Users</h6>
                <h2 class="fw-bold text-success">
                    {{ $activeUsers ?? 0 }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card archived-users shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-muted">Archived Users</h6>
                <h2 class="fw-bold text-warning">
                    {{ $archivedUsers ?? 0 }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card new-users shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-muted">New This Month</h6>
                <h2 class="fw-bold text-info">
                    {{ $monthlyUsers ?? 0 }}
                </h2>
            </div>
        </div>
    </div>

</div>
@endsection
@endsection

@section('script')
@endsection