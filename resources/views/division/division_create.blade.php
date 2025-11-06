@extends('layout.master')

@section('title')
    Tambah Divisi
@endsection

@section('heading')
    Tambah Divisi Baru
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Form Divisi Baru</h4>
                    <p class="card-description">
                        Masukkan nama untuk divisi baru.
                    </p>

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

                    <form class="forms-sample" action="{{ route('divisions.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name">Nama Divisi</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Contoh: Marketing"
                                value="{{ old('name') }}">
                        </div>
                        <button type="submit" class="btn btn-primary me-2">Simpan</button>
                        <a href="{{ route('divisions.index') }}" class="btn btn-light">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
