@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
<div class="login-page">
    <div class="login-card">
        <div class="login-logo">🎓</div>
        <h1>{{ config('app.name') }}</h1>
        <p class="sub">Masuk untuk mengelola komponen penilaian, nilai mahasiswa, dan rekapitulasi.</p>

        @if ($errors->any())
            <div class="alert alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" placeholder="nama@kampus.ac.id" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror"
                       placeholder="••••••••" required>
            </div>
            <div class="form-group" style="display:flex;align-items:center;justify-content:space-between">
                <label style="display:flex;align-items:center;gap:8px;font-weight:500;margin:0">
                    <input type="checkbox" name="remember" value="1"> Ingat saya
                </label>
                <a href="#" onclick="alert('Silakan hubungi administrator program studi untuk reset password.');return false;"
                   style="font-size:13px">Lupa password?</a>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>

        <div class="demo-cred">
            <strong>Akun demo (hasil seeder):</strong><br>
            Email: <code>raiiisaja@gmail.com</code><br>
            Password: <code>password</code>
        </div>
    </div>
</div>
@endsection
