<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <p class="brand-title">{{ config('app.name') }}</p>
            <span class="brand-sub">Program Studi Teknik Informatika</span>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Menu</div>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="icon">🏠</span> Dashboard
            </a>
            <a href="{{ route('mata-kuliah.index') }}" class="{{ request()->routeIs('mata-kuliah.*') ? 'active' : '' }}">
                <span class="icon">📚</span> Mata Kuliah
            </a>
            <a href="{{ route('komponen.pilih') }}" class="{{ request()->routeIs('komponen.*') ? 'active' : '' }}">
                <span class="icon">⚖️</span> Komponen Penilaian
            </a>
            <a href="{{ route('nilai.pilih') }}" class="{{ request()->routeIs('nilai.*') ? 'active' : '' }}">
                <span class="icon">📝</span> Penilaian
            </a>
            <a href="{{ route('mahasiswa.index') }}" class="{{ request()->routeIs('mahasiswa.*') ? 'active' : '' }}">
                <span class="icon">🎓</span> Mahasiswa
            </a>
            <a href="{{ route('rekap.index') }}" class="{{ request()->routeIs('rekap.*') ? 'active' : '' }}">
                <span class="icon">📊</span> Rekap Nilai
            </a>
            <div class="nav-label">Akun</div>
            <a href="{{ route('profil') }}" class="{{ request()->routeIs('profil') ? 'active' : '' }}">
                <span class="icon">👤</span> Profil
            </a>
        </nav>
        <div class="sidebar-footer">
            Login sebagai<br><strong style="color:#e2e8f0">{{ auth()->user()->name }}</strong>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">
            <h1>@yield('title', 'Dashboard')</h1>
            <div class="user-chip">
                <div class="avatar">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>
                <div>
                    <div style="font-weight:600;line-height:1.1">{{ auth()->user()->name }}</div>
                    <div class="text-muted" style="font-size:12px">Dosen</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm">Keluar</button>
                </form>
            </div>
        </header>

        <main class="content">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</div>
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
