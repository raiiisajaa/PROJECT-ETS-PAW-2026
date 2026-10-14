@extends('layouts.app')

@section('title', 'Rekap Nilai')

@php
    $fmt = fn ($n) => number_format((float) $n, 2, ',', '.');
    $tampilkanMk = ($filters['mata_kuliah'] === 'all' || $filters['mata_kuliah'] === '');
@endphp

@section('content')
<div class="page-head">
    <div>
        <h1>Rekapitulasi Nilai</h1>
        <p>Ringkasan nilai akhir, grade, dan status kelulusan seluruh mahasiswa.</p>
    </div>
    <a href="{{ route('rekap.export', request()->query()) }}" class="btn btn-success">⬇ Export Excel</a>
</div>

<div class="card">
    <div class="card-body">
        <form id="form-filter-rekap" method="GET" action="{{ route('rekap.index') }}" class="form-inline">
            <div class="form-group mb-0">
                <label for="mata_kuliah">Mata Kuliah</label>
                <select name="mata_kuliah" id="mata_kuliah" class="form-control">
                    <option value="all">Semua Mata Kuliah</option>
                    @foreach ($mataKuliahs as $mk)
                        <option value="{{ $mk->id }}" @selected((string) $filters['mata_kuliah'] === (string) $mk->id)>{{ $mk->kode }} — {{ $mk->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-0">
                <label for="kelas">Kelas</label>
                <select name="kelas" id="kelas" class="form-control">
                    <option value="all">Semua Kelas</option>
                    @foreach ($kelasFilter as $k)
                        <option value="{{ $k->id }}" @selected((string) $filters['kelas'] === (string) $k->id)>
                            {{ $k->mataKuliah->kode }} — {{ $k->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-0">
                <label for="q">Cari</label>
                <input type="text" name="q" id="q" class="form-control" value="{{ $filters['q'] }}" placeholder="NIM / nama">
            </div>
            <button type="submit" class="btn btn-primary">Terapkan Filter</button>
        </form>
    </div>
</div>

<div class="grid grid-4">
    <div class="stat">
        <div class="stat-label">Jumlah Mahasiswa</div>
        <div class="stat-value">{{ $ringkasan['jumlah'] }}</div>
    </div>
    <div class="stat">
        <div class="stat-label">Rata-rata Nilai</div>
        <div class="stat-value">{{ $fmt($ringkasan['rata_rata']) }}</div>
    </div>
    <div class="stat">
        <div class="stat-label">Lulus</div>
        <div class="stat-value" style="color:var(--success)">{{ $ringkasan['lulus'] }}</div>
    </div>
    <div class="stat">
        <div class="stat-label">Tidak Lulus</div>
        <div class="stat-value" style="color:var(--danger)">{{ $ringkasan['tidak_lulus'] }}</div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h2>Daftar Nilai</h2>
    </div>
    <div class="card-body no-pad">
        @if ($rows->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">📊</div>
                <p>Tidak ada data sesuai filter.</p>
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:44px">No</th>
                            <th>NIM</th>
                            <th>Nama</th>
                            @if ($tampilkanMk)
                                <th>Mata Kuliah</th>
                            @endif
                            <th>Kelas</th>
                            <th class="text-center">Nilai Akhir</th>
                            <th class="text-center">Grade</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="mono">{{ $row['nim'] }}</td>
                                <td>{{ $row['nama'] }}</td>
                                @if ($tampilkanMk)
                                    <td>{{ $row['mata_kuliah'] }}</td>
                                @endif
                                <td>{{ $row['kelas'] }}</td>
                                <td class="text-center mono"><strong>{{ $fmt($row['nilai_akhir']) }}</strong></td>
                                <td class="text-center"><span class="badge badge-grade">{{ $row['grade'] }}</span></td>
                                <td class="text-center">
                                    @if ($row['status'] === 'Lulus')
                                        <span class="badge badge-lulus">Lulus</span>
                                    @else
                                        <span class="badge badge-tidak">Tidak Lulus</span>
                                    @endif
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
