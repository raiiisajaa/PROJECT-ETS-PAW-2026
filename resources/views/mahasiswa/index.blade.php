@extends('layouts.app')

@section('title', 'Mahasiswa')

@section('content')
<div class="page-head">
    <div>
        <h1>Data Mahasiswa</h1>
        <p>Kelola data mahasiswa pada kelas yang Anda ampu.</p>
    </div>
    <button type="button" class="btn btn-primary" data-open-mahasiswa>+ Tambah Mahasiswa</button>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('mahasiswa.index') }}" class="form-inline">
            <div class="form-group mb-0">
                <label for="filter-mk">Mata Kuliah</label>
                <select name="mata_kuliah" id="filter-mk" class="form-control" onchange="this.form.submit()">
                    <option value="all">Semua Mata Kuliah</option>
                    @foreach ($mataKuliahs as $mk)
                        <option value="{{ $mk->id }}" @selected((string) $filterMk === (string) $mk->id)>{{ $mk->kode }} — {{ $mk->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-0">
                <label for="filter-kelas">Kelas</label>
                <select name="kelas" id="filter-kelas" class="form-control" onchange="this.form.submit()">
                    <option value="all">Semua Kelas</option>
                    @foreach ($kelasFilter as $k)
                        <option value="{{ $k->id }}" @selected((string) $filterKelas === (string) $k->id)>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-0">
                <label for="q">Cari</label>
                <input type="text" name="q" id="q" class="form-control" value="{{ $keyword }}" placeholder="NIM / nama">
            </div>
            <button type="submit" class="btn btn-outline">Terapkan</button>
        </form>
    </div>
    <div class="card-body no-pad">
        @if ($mahasiswas->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">🎓</div>
                <p>Belum ada data mahasiswa yang cocok.</p>
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:44px">#</th>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Program Studi</th>
                            <th>Kelas</th>
                            <th>Mata Kuliah</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mahasiswas as $i => $mhs)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="mono">{{ $mhs->nim }}</td>
                                <td>{{ $mhs->nama }}</td>
                                <td>{{ $mhs->program_studi }}</td>
                                <td>{{ $mhs->kelas->nama_kelas }}</td>
                                <td>{{ $mhs->kelas->mataKuliah->nama }}</td>
                                <td class="text-right">
                                    <div class="table-actions" style="justify-content:flex-end">
                                        <a href="{{ route('nilai.detail', $mhs) }}" class="btn btn-outline btn-sm">Nilai</a>
                                        <button type="button" class="btn btn-outline btn-sm btn-edit-mahasiswa"
                                                data-id="{{ $mhs->id }}"
                                                data-nim="{{ $mhs->nim }}"
                                                data-nama="{{ $mhs->nama }}"
                                                data-prodi="{{ $mhs->program_studi }}"
                                                data-mk="{{ $mhs->kelas->mata_kuliah_id }}"
                                                data-kelas="{{ $mhs->kelas_id }}">Ubah</button>
                                        <form method="POST" action="{{ route('mahasiswa.destroy', $mhs) }}" data-confirm="Hapus data mahasiswa {{ $mhs->nama }}? Nilai terkait juga akan terhapus." style="display:inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
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

{{-- Modal tambah/ubah mahasiswa --}}
<div class="modal-backdrop" id="modal-mahasiswa">
    <div class="modal">
        <form id="form-mahasiswa" method="POST" action="{{ route('mahasiswa.store') }}"
              data-store-url="{{ route('mahasiswa.store') }}"
              data-update-url="{{ route('mahasiswa.update', ['mahasiswa' => '__ID__']) }}">
            @csrf
            <input type="hidden" name="_method" id="form-mahasiswa-method" value="POST">
            <div class="modal-header">
                <h3 id="modal-mahasiswa-title">Tambah Mahasiswa</h3>
                <button type="button" class="modal-close" data-close-modal>&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="form-mk-select">Mata Kuliah</label>
                    <select id="form-mk-select" class="form-control">
                        <option value="">— Pilih Mata Kuliah —</option>
                        @foreach ($mataKuliahs as $mk)
                            <option value="{{ $mk->id }}">{{ $mk->kode }} — {{ $mk->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="form-kelas-select">Kelas</label>
                    <select name="kelas_id" id="form-kelas-select" class="form-control" required>
                        <option value="">— Pilih Kelas —</option>
                        @foreach ($mataKuliahs as $mk)
                            @foreach ($mk->kelas as $k)
                                <option value="{{ $k->id }}" data-mk="{{ $mk->id }}">{{ $mk->kode }} — {{ $k->nama_kelas }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="form-nim">NIM</label>
                    <input type="text" name="nim" id="form-nim" class="form-control" maxlength="20" required>
                </div>
                <div class="form-group">
                    <label for="form-nama">Nama</label>
                    <input type="text" name="nama" id="form-nama" class="form-control" maxlength="100" required>
                </div>
                <div class="form-group">
                    <label for="form-prodi">Program Studi</label>
                    <input type="text" name="program_studi" id="form-prodi" class="form-control" maxlength="100"
                           value="Teknik Informatika" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-close-modal>Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
