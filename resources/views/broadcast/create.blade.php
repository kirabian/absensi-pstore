@extends('layout.master')

@section('title')
    Buat Broadcast Baru
@endsection

@section('heading')
    Buat Broadcast Baru
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Form Broadcast Baru</h4>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('broadcast.store') }}" method="POST">
                        @csrf
                        
                        <div class="form-group">
                            <label for="title">Judul Broadcast *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="{{ old('title') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="priority">Prioritas *</label>
                            <select class="form-control" id="priority" name="priority" required>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Rendah</option>
                                <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Sedang</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Tinggi</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="message">Pesan *</label>
                            <textarea class="form-control" id="message" name="message" rows="6" 
                                      required>{{ old('message') }}</textarea>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="mdi mdi-send"></i> Kirim Broadcast
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