@extends('layouts.app')

@section('title', 'Detail Penilaian')

@php
    $persen = fn ($bobot) => rtrim(rtrim(number_format($bobot * 100, 2, '.', ''), '0'), '.');
    $fmt = fn ($n) => number_format((float) $n, 2, ',', '.');
@endphp

@section('content')
<div class="page-head">
    <div>
        <h1>Detail Penilaian</h1>
        <p>{{ $mataKuliah->kode }} — {{ $mataKuliah->nama }}</p>
    </div>
    <a href="{{ route('nilai.index', ['mataKuliah' => $mataKuliah, 'kelas' => $mahasiswa->kelas_id]) }}" class="btn btn-outline">
        ← Kembali ke Input Nilai
    </a>
</div>

<div class="grid grid-3">
    <div class="card" style="grid-column: span 2">
        <div class="card-header">
            <h2>Data Mahasiswa</h2>
        </div>
        <div class="card-body">
            <dl class="identity-list">
                <dt>NIM</dt><dd class="mono">{{ $mahasiswa->nim }}</dd>
                <dt>Nama</dt><dd>{{ $mahasiswa->nama }}</dd>
                <dt>Program Studi</dt><dd>{{ $mahasiswa->program_studi }}</dd>
                <dt>Kelas</dt><dd>{{ $mahasiswa->kelas->nama_kelas }}</dd>
                <dt>Mata Kuliah</dt><dd>{{ $mataKuliah->nama }} ({{ $mataKuliah->kode }})</dd>
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Grade &amp; Status</h2>
        </div>
        <div class="card-body text-center">
            <div class="grade-circle grade-{{ $ringkasan['grade'] }}" style="margin:0 auto 12px">
                {{ $ringkasan['grade'] }}
            </div>
            <div style="font-size:28px;font-weight:800">{{ $fmt($ringkasan['nilai_akhir']) }}</div>
            <div class="text-muted" style="font-size:13px;margin-bottom:12px">Nilai Akhir</div>
            @if ($ringkasan['status'] === 'Lulus')
                <span class="badge badge-lulus">● Lulus</span>
            @else
                <span class="badge badge-tidak">● Tidak Lulus</span>
            @endif
            @unless ($ringkasan['lengkap'])
                <div class="text-muted mt-2" style="font-size:12px">* Sebagian komponen belum dinilai.</div>
            @endunless
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Nilai dan Perhitungan</h2>
        <span class="section-note">Nilai Akhir = Σ (Nilai × Bobot)</span>
    </div>
    <div class="card-body no-pad">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Komponen</th>
                        <th class="text-center">Nilai</th>
                        <th class="text-center">Bobot</th>
                        <th class="text-center">Nilai × Bobot</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ringkasan['detail'] as $baris)
                        <tr>
                            <td>{{ $baris['komponen'] }}</td>
                            <td class="text-center mono">{{ $baris['nilai'] !== null ? $fmt($baris['nilai']) : '—' }}</td>
                            <td class="text-center mono">{{ $persen($baris['bobot']) }}%</td>
                            <td class="text-center mono">{{ $fmt($baris['kontribusi']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right">Nilai Akhir</td>
                        <td class="text-center mono">{{ $fmt($ringkasan['nilai_akhir']) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Feedback Dosen</h2>
    </div>
    <form method="POST" action="{{ route('nilai.feedback', $mahasiswa) }}">
        @csrf
        <div class="card-body">
            <textarea name="isi" class="form-control" rows="4" maxlength="2000"
                      placeholder="Tulis arahan perbaikan atau umpan balik untuk mahasiswa...">{{ old('isi', $feedback->isi ?? '') }}</textarea>
            @error('isi')
                <div class="field-error">{{ $message }}</div>
            @enderror
            @if ($feedback)
                <p class="section-note mt-1">Terakhir diperbarui: {{ $feedback->updated_at->format('d M Y H:i') }}</p>
            @endif
        </div>
        <div class="card-body" style="border-top:1px solid var(--border)">
            <button type="submit" class="btn btn-primary">Simpan Feedback</button>
        </div>
    </form>
</div>
@endsection
