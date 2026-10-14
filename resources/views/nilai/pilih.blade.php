@extends('layouts.app')

@section('title', 'Penilaian')

@section('content')
<div class="page-head">
    <div>
        <h1>Input Nilai</h1>
        <p>Pilih mata kuliah untuk memasukkan atau mengimpor nilai mahasiswa.</p>
    </div>
</div>

<div class="card">
    <div class="card-body no-pad">
        @if ($mataKuliahs->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">📝</div>
                <p>Belum ada mata kuliah.</p>
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Mata Kuliah</th>
                            <th class="text-center">Semester</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mataKuliahs as $mk)
                            <tr>
                                <td><span class="pill">{{ $mk->kode }}</span></td>
                                <td><strong>{{ $mk->nama }}</strong></td>
                                <td class="text-center">{{ $mk->semester }}</td>
                                <td class="text-right">
                                    <a href="{{ route('nilai.index', $mk) }}" class="btn btn-primary btn-sm">Input Nilai</a>
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
