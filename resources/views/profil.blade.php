@extends('layouts.app')

@section('title', 'Profil')

@section('content')
<div class="page-head">
    <div>
        <h1>Profil</h1>
        <p>Informasi akun dosen yang sedang login.</p>
    </div>
</div>

<div class="grid grid-2">
    <div class="card">
        <div class="card-header">
            <h2>Akun</h2>
        </div>
        <div class="card-body">
            <dl class="identity-list">
                <dt>Nama</dt><dd>{{ $user->name }}</dd>
                <dt>Email</dt><dd>{{ $user->email }}</dd>
                <dt>Peran</dt><dd><span class="badge badge-grade">Dosen</span></dd>
                <dt>Mata Kuliah</dt><dd>{{ $jumlahMataKuliah }} mata kuliah</dd>
            </dl>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h2>Keamanan</h2>
        </div>
        <div class="card-body">
            <p class="section-note">
                Password tersimpan dalam bentuk hash. Untuk mengubah password atau memulihkan akun,
                hubungi administrator program studi.
            </p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline">Keluar dari akun</button>
            </form>
        </div>
    </div>
</div>
@endsection
