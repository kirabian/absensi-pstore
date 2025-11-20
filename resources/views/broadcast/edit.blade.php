@extends('layout.master')

@section('title', 'Edit Broadcast')
@section('heading', 'Edit Broadcast')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('broadcast.update', $broadcast) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Judul Broadcast</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title', $broadcast->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="message" class="form-label">Pesan</label>
                        <textarea class="form-control @error('message') is-invalid @enderror" 
                                  id="message" name="message" rows="5" required>{{ old('message', $broadcast->message) }}</textarea>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="priority" class="form-label">Prioritas</label>
                        <select class="form-select @error('priority') is-invalid @enderror" 
                                id="priority" name="priority" required>
                            <option value="low" {{ old('priority', $broadcast->priority) == 'low' ? 'selected' : '' }}>Rendah</option>
                            <option value="medium" {{ old('priority', $broadcast->priority) == 'medium' ? 'selected' : '' }}>Sedang</option>
                            <option value="high" {{ old('priority', $broadcast->priority) == 'high' ? 'selected' : '' }}>Tinggi</option>
                        </select>
                        @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save me-2"></i>Update Broadcast
                        </button>
                        <a href="{{ route('broadcast.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection