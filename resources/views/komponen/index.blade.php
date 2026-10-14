@extends('layouts.app')

@section('title', 'Komponen Penilaian')

@php
    $persen = fn ($bobot) => rtrim(rtrim(number_format($bobot * 100, 2, '.', ''), '0'), '.');
    $totalAwal = $mataKuliah->komponens->sum('bobot') * 100;
@endphp

@section('content')
<div class="page-head">
    <div>
        <h1>Komponen Penilaian</h1>
        <p>{{ $mataKuliah->kode }} — {{ $mataKuliah->nama }} · Semester {{ $mataKuliah->semester }}</p>
    </div>
    @if ($mataKuliahs->count() > 1)
        <div class="form-group mb-0">
            <label for="pilih-mk">Ganti Mata Kuliah</label>
            <select id="pilih-mk" class="form-control" onchange="if(this.value)window.location=this.value">
                @foreach ($mataKuliahs as $mk)
                    <option value="{{ route('komponen.index', $mk) }}" @selected($mk->id === $mataKuliah->id)>
                        {{ $mk->kode }} — {{ $mk->nama }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif
</div>

<form id="form-komponen" method="POST" action="{{ route('komponen.simpan', $mataKuliah) }}">
    @csrf
    <div class="card">
        <div class="card-header">
            <div>
                <h2>Komponen &amp; Bobot</h2>
                <p class="section-note mb-0">Bobot dinyatakan dalam persen. Total seluruh bobot harus tepat 100%.</p>
            </div>
            <span id="total-bobot" class="total-bobot">Total bobot: 0%</span>
        </div>
        <div class="card-body no-pad">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:50px">#</th>
                            <th>Nama Komponen</th>
                            <th style="width:160px">Bobot (%)</th>
                            <th style="width:110px" class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="komponen-list">
                        @forelse ($mataKuliah->komponens as $i => $komponen)
                            <tr class="komponen-row">
                                <td class="row-number">{{ $i + 1 }}</td>
                                <td>
                                    <input type="hidden" class="id-input" data-name="komponens[__INDEX__][id]"
                                           name="komponens[{{ $i }}][id]" value="{{ $komponen->id }}">
                                    <input type="text" class="form-control nama-input" data-name="komponens[__INDEX__][nama]"
                                           name="komponens[{{ $i }}][nama]" value="{{ $komponen->nama }}" maxlength="100" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0.01" max="100" class="form-control bobot-input"
                                           data-name="komponens[__INDEX__][bobot]" name="komponens[{{ $i }}][bobot]"
                                           value="{{ $persen($komponen->bobot) }}" required>
                                </td>
                                <td class="text-right">
                                    <button type="button" class="btn btn-danger btn-sm btn-hapus-komponen">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr class="komponen-row">
                                <td class="row-number">1</td>
                                <td>
                                    <input type="hidden" class="id-input" data-name="komponens[__INDEX__][id]" name="komponens[0][id]">
                                    <input type="text" class="form-control nama-input" data-name="komponens[__INDEX__][nama]"
                                           name="komponens[0][nama]" placeholder="Contoh: Tugas 1" maxlength="100" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0.01" max="100" class="form-control bobot-input"
                                           data-name="komponens[__INDEX__][bobot]" name="komponens[0][bobot]" placeholder="20" required>
                                </td>
                                <td class="text-right">
                                    <button type="button" class="btn btn-danger btn-sm btn-hapus-komponen">Hapus</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-body" style="border-top:1px solid var(--border)">
            <div class="d-flex justify-between align-center flex-wrap gap-2">
                <button type="button" id="btn-tambah-komponen" class="btn btn-outline">+ Tambah Komponen</button>
                <div class="d-flex align-center gap-2 flex-wrap">
                    <span id="pesan-bobot" class="alert alert-warning mb-0" style="display:none;margin:0">
                        ⚠️ Total seluruh bobot harus 100%.
                    </span>
                    <button type="submit" id="btn-simpan-komponen" class="btn btn-primary">Simpan Komponen</button>
                </div>
            </div>
        </div>
    </div>
</form>

<template id="komponen-row-template">
    <tr class="komponen-row">
        <td class="row-number"></td>
        <td>
            <input type="hidden" class="id-input" data-name="komponens[__INDEX__][id]">
            <input type="text" class="form-control nama-input" data-name="komponens[__INDEX__][nama]"
                   placeholder="Nama komponen" maxlength="100" required>
        </td>
        <td>
            <input type="number" step="0.01" min="0.01" max="100" class="form-control bobot-input"
                   data-name="komponens[__INDEX__][bobot]" placeholder="0" required>
        </td>
        <td class="text-right">
            <button type="button" class="btn btn-danger btn-sm btn-hapus-komponen">Hapus</button>
        </td>
    </tr>
</template>

<script>
    // Penomoran baris mengikuti urutan di layar.
    document.addEventListener('DOMContentLoaded', function () {
        var list = document.getElementById('komponen-list');
        if (!list) return;
        var observer = new MutationObserver(function () {
            list.querySelectorAll('.komponen-row').forEach(function (row, i) {
                var cell = row.querySelector('.row-number');
                if (cell) cell.textContent = i + 1;
            });
        });
        observer.observe(list, { childList: true });
    });
</script>
@endsection
