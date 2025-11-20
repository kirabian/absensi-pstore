@extends('layout.master')

@section('title')
    Edit Broadcast
@endsection

@section('heading')
    Edit Broadcast
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Edit Broadcast</h4>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('broadcast.update', $broadcast->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group">
                            <label for="title">Judul Broadcast *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="{{ old('title', $broadcast->title) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="priority">Prioritas *</label>
                            <select class="form-control" id="priority" name="priority" required>
                                <option value="low" {{ old('priority', $broadcast->priority) == 'low' ? 'selected' : '' }}>Rendah</option>
                                <option value="medium" {{ old('priority', $broadcast->priority) == 'medium' ? 'selected' : '' }}>Sedang</option>
                                <option value="high" {{ old('priority', $broadcast->priority) == 'high' ? 'selected' : '' }}>Tinggi</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="message">Pesan *</label>
                            <textarea class="form-control" id="message" name="message" rows="6" 
                                      required>{{ old('message', $broadcast->message) }}</textarea>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="mdi mdi-content-save"></i> Update Broadcast
                            </button>
                            <a href="{{ route('broadcast.index') }}" class="btn btn-light">
                                <i class="mdi mdi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection