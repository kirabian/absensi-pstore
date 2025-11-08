@extends('layout.master')

@section('title')
    Edit Cabang
@endsection

@section('heading')
    Edit Cabang: {{ $branch->name }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Form Edit Cabang</h4>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="forms-sample" action="{{ route('branches.update', $branch->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">Nama Cabang</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name', $branch->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="address">Alamat Cabang</label>
                            <textarea class="form-control" id="address" name="address"
                                rows="3">{{ old('address', $branch->address) }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary me-2">Update</button>
                        <a href="{{ route('branches.index') }}" class="btn btn-light">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
