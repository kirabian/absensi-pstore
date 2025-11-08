@extends('layout.master')

@section('title')
    Edit User
@endsection

@section('heading')
    Edit User: {{ $user->name }}
@endsection

@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Form Edit User</h4>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="forms-sample" action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="name">Nama Lengkap</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <small class="text-muted">Kosongkan jika tidak ingin mengganti password.</small>
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                    </div>

                    {{-- DROPDOWN CABANG (BARU) --}}
                    <div class="form-group">
                        <label for="branch_id">Cabang</label>
                        {{-- Jika Admin Cabang, dia tidak bisa pindah cabang user --}}
                        @if (Auth::user()->role == 'admin' && Auth::user()->branch_id != null)
                            <input type="text" class="form-control" value="{{ $branches->first()->name }}" readonly>
                            <input type="hidden" name="branch_id" value="{{ $branches->first()->id }}">
                        @else
                        {{-- Jika Super Admin, dia bisa pindah-pindah --}}
                            <select class="form-control" id="branch_id" name="branch_id">
                                <option value="">Super Admin (Tidak Punya Cabang)</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih "Super Admin" jika role-nya 'admin' dan dia bisa melihat semua cabang.</small>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="role">Role</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin (Cabang atau Super)</option>
                            <option value="audit" {{ old('role', $user->role) == 'audit' ? 'selected' : '' }}>Audit</option>
                            <option value="leader" {{ old('role', $user->role) == 'leader' ? 'selected' : '' }}>Leader</option> {{-- <-- ROLE BARU --}}
                            <option value="security" {{ old('role', $user->role) == 'security' ? 'selected' : '' }}>Security</option>
                            <option value="user_biasa" {{ old('role', $user->role) == 'user_biasa' ? 'selected' : '' }}>User Biasa</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="division_id">Divisi / Tim</label>
                        <select class="form-control" id="division_id" name="division_id">
                            <option value="">-- Pilih Divisi (jika user/leader/audit) --</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}" {{ old('division_id', $user->division_id) == $division->id ? 'selected' : '' }}>
                                    {{ $division->name }} (Cabang: {{ $division->branch->name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Pilih divisi yang sesuai dengan cabang user.</small>
                    </div>

                    <button type="submit" class="btn btn-primary me-2">Update</button>
                    <a href="{{ route('users.index') }}" class="btn btn-light">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
