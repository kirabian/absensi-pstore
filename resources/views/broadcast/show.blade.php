@extends('layout.master')

@section('title', $broadcast->title)
@section('heading', $broadcast->title)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">{{ $broadcast->title }}</h5>
                        <span
                            class="badge bg-{{ $broadcast->priority == 'high' ? 'danger' : ($broadcast->priority == 'medium' ? 'warning' : 'secondary') }}">
                            {{ ucfirst($broadcast->priority) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <p class="text-muted mb-2">
                            <i class="mdi mdi-calendar me-2"></i>
                            {{ $broadcast->published_at->format('d F Y H:i') }}
                        </p>
                        <p class="text-muted mb-0">
                            <i class="mdi mdi-account me-2"></i>
                            Oleh: {{ $broadcast->creator->name ?? 'Unknown' }}
                        </p>
                    </div>

                    <div class="broadcast-content">
                        {!! nl2br(e($broadcast->message)) !!}
                    </div>
                </div>
                <div class="card-footer">
                    @if (auth()->user()->role == 'admin')
                        <a href="{{ route('broadcast.index') }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left me-2"></i>Kembali ke Daftar
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left me-2"></i>Kembali ke Dashboard
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
