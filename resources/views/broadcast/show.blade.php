@extends('layout.master')

@section('title')
    {{ $broadcast->title }}
@endsection

@section('heading')
    Detail Broadcast
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">{{ $broadcast->title }}</h4>
                        <span class="badge 
                            @if($broadcast->priority == 'high') badge-danger
                            @elseif($broadcast->priority == 'medium') badge-warning
                            @else badge-info @endif">
                            {{ ucfirst($broadcast->priority) }}
                        </span>
                    </div>

                    <div class="mb-4">
                        <small class="text-muted">
                            <i class="mdi mdi-calendar"></i> 
                            {{ $broadcast->published_at->format('d M Y H:i') }} 
                            oleh {{ $broadcast->creator->name }}
                        </small>
                    </div>

                    <div class="broadcast-content bg-light p-4 rounded">
                        {!! nl2br(e($broadcast->message)) !!}
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('broadcast.index') }}" class="btn btn-light">
                            <i class="mdi mdi-arrow-left"></i> Kembali ke Daftar
                        </a>
                        
                        @if(auth()->user()->role == 'admin')
                        <div class="float-right">
                            <a href="{{ route('broadcast.edit', $broadcast->id) }}" 
                               class="btn btn-warning mr-2">
                                <i class="mdi mdi-pencil"></i> Edit
                            </a>
                            <form action="{{ route('broadcast.destroy', $broadcast->id) }}" 
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Yakin ingin menghapus broadcast ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="mdi mdi-delete"></i> Hapus
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .broadcast-content {
            font-size: 16px;
            line-height: 1.6;
            white-space: pre-line;
        }
    </style>
@endsection