@extends('layout.master')
@section('title')
    Edit User
@endsection
@section('heading')
    Edit User: {{ $user->name }}
@endsection

@section('content')
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

        <div class="row">
            {{-- KARTU DATA LOGIN & ROLE --}}
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Data Login & Role</h4>

                        <div class="form-group">
                            <label for="name">Nama Lengkap</label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="login_id">ID Login</label>
                            <input type="text" class="form-control" id="login_id" name="login_id"
                                value="{{ old('login_id', $user->login_id) }}" required>
                            <small class="text-muted">ID Login ini yang digunakan untuk masuk ke sistem</small>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="{{ old('email', $user->email) }}">
                            <small class="text-muted">Opsional, hanya untuk kontak</small>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <small class="text-muted">Kosongkan jika tidak ingin mengganti password.</small>
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="password_confirmation"
                                name="password_confirmation">
                        </div>
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Super
                                    Admin</option>
                                <option value="audit" {{ old('role', $user->role) == 'audit' ? 'selected' : '' }}>Audit
                                </option>
                                <option value="leader" {{ old('role', $user->role) == 'leader' ? 'selected' : '' }}>Leader
                                </option>
                                <option value="security" {{ old('role', $user->role) == 'security' ? 'selected' : '' }}>
                                    Security</option>
                                <option value="user_biasa"
                                    {{ old('role', $user->role) == 'user_biasa' ? 'selected' : '' }}>Karyawan</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KARTU DATA KARYAWAN & SOSMED --}}
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Data Karyawan & Kontak</h4>

                        <div class="form-group">
                            <label for="hire_date">Tanggal Masuk PStore</label>
                            <input type="date" class="form-control" id="hire_date" name="hire_date"
                                value="{{ old('hire_date', $user->hire_date ? $user->hire_date->format('Y-m-d') : '') }}">
                        </div>

                        <div class="form-group">
                            <label for="branch_id">Cabang</label>
                            @if ($branches->count() == 1 && Auth::user()->branch_id != null)
                                <input type="text" class="form-control" value="{{ $branches->first()->name }}" readonly>
                                <input type="hidden" name="branch_id" value="{{ $branches->first()->id }}">
                            @else
                                <select class="form-control" id="branch_id" name="branch_id">
                                    <option value="">Super Admin (Tidak Punya Cabang)</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Pilih "Super Admin" jika role-nya 'admin'.</small>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="division_id">Divisi / Tim</label>
                            <select class="form-control" id="division_id" name="division_id">
                                <option value="">-- Pilih Divisi --</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}"
                                        {{ old('division_id', $user->division_id) == $division->id ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <h5 class="mt-4">Info Kontak (Opsional)</h5>
                        <div class="form-group">
                            <label for="whatsapp">Nomor WhatsApp</label>
                            <input type="text" class="form-control" id="whatsapp" name="whatsapp" placeholder="62812..."
                                value="{{ old('whatsapp', $user->whatsapp) }}">
                        </div>
                        <div class="form-group">
                            <label for="instagram">Instagram</label>
                            <input type="text" class="form-control" id="instagram" name="instagram"
                                placeholder="username" value="{{ old('instagram', $user->instagram) }}">
                        </div>
                        <div class="form-group">
                            <label for="tiktok">TikTok</label>
                            <input type="text" class="form-control" id="tiktok" name="tiktok"
                                placeholder="username" value="{{ old('tiktok', $user->tiktok) }}">
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary me-2">Update User</button>
                <a href="{{ route('users.index') }}" class="btn btn-light">Batal</a>
            </div>
        </div>
    </form>
@endsection