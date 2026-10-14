@extends('layouts.app')

@section('title', 'Input Nilai')

@php
    $persen = fn ($bobot) => rtrim(rtrim(number_format($bobot * 100, 2, '.', ''), '0'), '.');
    $fmt = fn ($n) => number_format((float) $n, 2, ',', '.');
@endphp

@section('content')
<div class="page-head">
    <div>
        <h1>Input Nilai</h1>
        <p>{{ $mataKuliah->kode }} — {{ $mataKuliah->nama }} · Total bobot {{ $persen($mataKuliah->komponens->sum('bobot')) }}%</p>
    </div>
    @if ($mataKuliahs ?? false)
    @endif
</div>

@if ($mataKuliah->komponens->isEmpty())
    <div class="alert alert-warning">
        ⚠️ Mata kuliah ini belum memiliki komponen penilaian.
        <a href="{{ route('komponen.index', $mataKuliah) }}">Atur komponen &amp; bobot terlebih dahulu</a>.
    </div>
@endif

@if (! empty($importGagal))
    <div class="alert alert-error">
        <strong>Baris yang gagal diimpor ({{ count($importGagal) }}):</strong>
        <ul>
            @foreach ($importGagal as $gagal)
                <li>Baris {{ $gagal['baris'] }}: {{ $gagal['pesan'] }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('nilai.index', $mataKuliah) }}" class="form-inline">
            <div class="form-group mb-0">
                <label for="kelas">Kelas</label>
                <select name="kelas" id="kelas" class="form-control" onchange="this.form.submit()">
                    @forelse ($kelasList as $k)
                        <option value="{{ $k->id }}" @selected($kelas && $k->id === $kelas->id)>{{ $k->nama_kelas }}</option>
                    @empty
                        <option value="">Belum ada kelas</option>
                    @endforelse
                </select>
            </div>
        </form>

        <div class="d-flex gap-1 flex-wrap align-center">
            <input type="text" id="search-mahasiswa" class="form-control" placeholder="🔍 Cari NIM / nama..." style="width:220px">
            @if ($mataKuliah->komponens->isNotEmpty())
                <a href="{{ route('nilai.template', $mataKuliah) }}" class="btn btn-outline btn-sm">Unduh Template</a>
                <button type="button" class="btn btn-outline btn-sm" data-open-import>Import Excel</button>
            @endif
        </div>
    </div>

    @if ($kelas && $mahasiswas->isNotEmpty() && $mataKuliah->komponens->isNotEmpty())
        <form id="form-nilai" method="POST" action="{{ route('nilai.simpan', $mataKuliah) }}">
            @csrf
            <input type="hidden" name="mata_kuliah_id" value="{{ $mataKuliah->id }}">
            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
            <div class="card-body no-pad">
                <div class="table-wrap">
                    <table class="table" id="tabel-nilai">
                        <thead>
                            <tr>
                                <th style="width:44px">#</th>
                                <th>NIM</th>
                                <th>Nama</th>
                                @foreach ($mataKuliah->komponens as $komponen)
                                    <th class="text-center nowrap">
                                        {{ $komponen->nama }}
                                        <div class="text-muted" style="font-weight:400;text-transform:none">{{ $persen($komponen->bobot) }}%</div>
                                    </th>
                                @endforeach
                                <th class="text-center">Nilai Akhir</th>
                                <th class="text-center">Grade</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mahasiswas as $i => $mahasiswa)
                                @php $r = $ringkasan[$mahasiswa->id]; @endphp
                                <tr data-nim="{{ $mahasiswa->nim }}" data-nama="{{ $mahasiswa->nama }}">
                                    <td>{{ $i + 1 }}</td>
                                    <td class="mono">{{ $mahasiswa->nim }}</td>
                                    <td>{{ $mahasiswa->nama }}</td>
                                    @foreach ($mataKuliah->komponens as $komponen)
                                        @php $nilaiMhs = $mahasiswa->nilaiKomponen($komponen->id); @endphp
                                        <td class="text-center">
                                            <input type="number" step="0.01" min="0" max="100"
                                                   class="nilai-input @error("nilai.{$mahasiswa->id}.{$komponen->id}") is-invalid @enderror"
                                                   name="nilai[{{ $mahasiswa->id }}][{{ $komponen->id }}]"
                                                   value="{{ $nilaiMhs !== null ? rtrim(rtrim(number_format($nilaiMhs, 2, '.', ''), '0'), '.') : '' }}"
                                                   placeholder="-">
                                        </td>
                                    @endforeach
                                    <td class="text-center mono"><strong>{{ $fmt($r['nilai_akhir']) }}</strong></td>
                                    <td class="text-center"><span class="badge badge-grade">{{ $r['grade'] }}</span></td>
                                    <td class="text-center">
                                        @if ($r['status'] === 'Lulus')
                                            <span class="badge badge-lulus">Lulus</span>
                                        @else
                                            <span class="badge badge-tidak">Tidak Lulus</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('nilai.detail', $mahasiswa) }}" class="btn btn-outline btn-sm">Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-body" style="border-top:1px solid var(--border)">
                <div class="d-flex justify-between align-center flex-wrap gap-2">
                    <span class="section-note">Nilai kosong dianggap belum dinilai (dihitung 0 pada nilai akhir).</span>
                    <button type="submit" class="btn btn-primary">Simpan Nilai</button>
                </div>
            </div>
        </form>
    @else
        <div class="card-body">
            <div class="empty-state">
                <div class="empty-icon">📝</div>
                <p>
                    @if (! $kelas)
                        Belum ada kelas pada mata kuliah ini.
                    @elseif ($mahasiswas->isEmpty())
                        Belum ada mahasiswa pada kelas ini.
                    @else
                        Atur komponen penilaian terlebih dahulu.
                    @endif
                </p>
            </div>
        </div>
    @endif
</div>

{{-- Modal Import Excel --}}
<div class="modal-backdrop" id="modal-import">
    <div class="modal">
        <form method="POST" action="{{ route('nilai.import', $mataKuliah) }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="kelas_id" value="{{ $kelas->id ?? '' }}">
            <div class="modal-header">
                <h3>Import Nilai dari Excel</h3>
                <button type="button" class="modal-close" data-close-import>&times;</button>
            </div>
            <div class="modal-body">
                <p class="section-note">
                    Format: kolom <strong>nim</strong>, kolom <strong>nama</strong> (opsional), lalu satu kolom per komponen
                    ({{ $mataKuliah->komponens->pluck('nama')->join(', ') ?: 'belum ada komponen' }}).
                    Gunakan tombol <em>Unduh Template</em> untuk contoh.
                </p>
                <div class="form-group">
                    <label for="berkas">Berkas Excel (.xlsx/.xls/.csv)</label>
                    <input type="file" id="berkas" name="berkas" class="form-control" accept=".xlsx,.xls,.csv" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-close-import>Batal</button>
                <button type="submit" class="btn btn-primary">Import</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var modal = document.getElementById('modal-import');
        if (!modal) return;
        document.querySelectorAll('[data-open-import]').forEach(function (b) {
            b.addEventListener('click', function () { modal.classList.add('open'); });
        });
        modal.querySelectorAll('[data-close-import]').forEach(function (b) {
            b.addEventListener('click', function () { modal.classList.remove('open'); });
        });
        modal.addEventListener('click', function (e) { if (e.target === modal) modal.classList.remove('open'); });
    })();
</script>
@endpush
