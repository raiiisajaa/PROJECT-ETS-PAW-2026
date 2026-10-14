@extends('layouts.app')

@section('title', 'Mata Kuliah')

@section('content')
<div class="page-head">
    <div>
        <h1>Mata Kuliah</h1>
        <p>Daftar mata kuliah yang Anda ampu beserta jumlah kelas, mahasiswa, dan komponen.</p>
    </div>
</div>

<div class="card">
    <div class="card-body no-pad">
        @if ($mataKuliahs->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">📚</div>
                <p>Belum ada mata kuliah.</p>
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th class="text-center">Semester</th>
                            <th class="text-center">SKS</th>
                            <th class="text-center">Kelas</th>
                            <th class="text-center">Mahasiswa</th>
                            <th class="text-center">Komponen</th>
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
                                <td class="text-center">{{ $mk->mahasiswas_count }}</td>
                                <td class="text-center">
                                    @if ($mk->komponens_count === 0)
                                        <span class="badge badge-tidak">Belum diatur</span>
                                    @else
                                        {{ $mk->komponens_count }}
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="table-actions" style="justify-content:flex-end">
                                        <a href="{{ route('komponen.index', $mk) }}" class="btn btn-outline btn-sm">Komponen</a>
                                        <a href="{{ route('nilai.index', $mk) }}" class="btn btn-primary btn-sm">Nilai</a>
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
