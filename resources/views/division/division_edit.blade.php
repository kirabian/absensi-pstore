@extends('layout.master')

@section('title')
    Edit Divisi
@endsection

@section('heading')
    Edit Divisi: {{ $division->name }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Form Edit Divisi</h4>

                    {{-- Menampilkan Error Validasi --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="forms-sample" action="{{ route('divisions.update', $division->id) }}" method="POST">
                        @csrf
                        @method('PUT') {{-- PENTING untuk update --}}

                        <div class="form-group">
                            <label for="name">Nama Divisi</label>
                            {{-- Mengisi value dengan data lama atau data dari database --}}
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name', $division->name) }}">
                        </div>
                        <button type="submit" class="btn btn-primary me-2">Update</button>
                        <a href="{{ route('divisions.index') }}" class="btn btn-light">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
