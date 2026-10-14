@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-head">
    <div>
        <h1>Selamat datang, {{ $user->name }} 👋</h1>
        <p>Ringkasan beban mengajar dan status penilaian Anda.</p>
    </div>
</div>

<div class="grid grid-4">
    <div class="stat">
        <div class="stat-icon">📚</div>
        <div class="stat-value">{{ $jumlahMataKuliah }}</div>
        <div class="stat-label">Mata Kuliah</div>
    </div>
    <div class="stat">
        <div class="stat-icon">🎓</div>
        <div class="stat-value">{{ $jumlahMahasiswa }}</div>
        <div class="stat-label">Mahasiswa</div>
    </div>
    <div class="stat">
        <div class="stat-icon">🏫</div>
        <div class="stat-value">{{ $jumlahKelas }}</div>
        <div class="stat-label">Kelas</div>
    </div>
    <div class="stat">
        <div class="stat-icon">⚖️</div>
        <div class="stat-value">{{ $jumlahKomponen }}</div>
        <div class="stat-label">Komponen Penilaian</div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h2>Mata Kuliah Saya</h2>
        <a href="{{ route('rekap.index') }}" class="btn btn-outline btn-sm">Lihat Rekap Nilai</a>
    </div>
    <div class="card-body no-pad">
        @if ($mataKuliahs->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">📚</div>
                <p>Belum ada mata kuliah. Hubungi administrator atau jalankan seeder.</p>
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Mata Kuliah</th>
                            <th class="text-center">Semester</th>
                            <th class="text-center">SKS</th>
                            <th class="text-center">Kelas</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mataKuliahs as $mk)
                            <tr>
                                <td><span class="pill">{{ $mk->kode }}</span></td>
                                <td><strong>{{ $mk->nama }}</strong></td>
                                <td class="text-center">{{ $mk->semester }}</td>
                                <td class="text-center">{{ $mk->sks }}</td>
                                <td class="text-center">{{ $mk->kelas_count }}</td>
                                <td class="text-right">
                                    <div class="table-actions" style="justify-content:flex-end">
                                        <a href="{{ route('komponen.index', $mk) }}" class="btn btn-outline btn-sm">Kelola Komponen</a>
                                        <a href="{{ route('nilai.index', $mk) }}" class="btn btn-primary btn-sm">Input Nilai</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
