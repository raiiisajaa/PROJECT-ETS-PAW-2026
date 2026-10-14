/**
 * Sistem Penilaian Tugas Mahasiswa — skrip antarmuka.
 * Berisi validasi klien (JavaScript) untuk melengkapi validasi server (Laravel).
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initKomponen();
        initNilai();
        initMahasiswa();
        initRekap();
        initConfirm();
        initFlashAutoHide();
    });

    /* ------------------------------------------------------------------ *
     * Pengaturan komponen: tambah/hapus baris + total bobot langsung
     * (FR-12, FR-13)
     * ------------------------------------------------------------------ */
    function initKomponen() {
        var list = document.getElementById('komponen-list');
        if (!list) return;

        var template = document.getElementById('komponen-row-template');
        var btnTambah = document.getElementById('btn-tambah-komponen');
        var btnSimpan = document.getElementById('btn-simpan-komponen');
        var totalEl = document.getElementById('total-bobot');
        var pesanEl = document.getElementById('pesan-bobot');
        var form = document.getElementById('form-komponen');

        function rows() {
            return list.querySelectorAll('.komponen-row');
        }

        function refresh() {
            var total = 0;
            rows().forEach(function (row) {
                var input = row.querySelector('.bobot-input');
                var val = parseFloat(input.value);
                total += isNaN(val) ? 0 : val;
            });

            var totalBulat = Math.round(total * 100) / 100;
            var valid = totalBulat === 100;

            if (totalEl) {
                totalEl.textContent = 'Total bobot: ' + formatAngka(totalBulat) + '%';
                totalEl.classList.toggle('valid', valid);
                totalEl.classList.toggle('invalid', !valid);
            }

            if (pesanEl) {
                pesanEl.style.display = valid ? 'none' : 'block';
            }

            if (btnSimpan) {
                btnSimpan.disabled = !valid;
                btnSimpan.classList.toggle('disabled', !valid);
            }
        }

        function reindex() {
            rows().forEach(function (row, i) {
                row.querySelectorAll('[data-name]').forEach(function (el) {
                    el.name = el.getAttribute('data-name').replace('__INDEX__', i);
                });
            });
        }

        function tambahBaris(data) {
            if (!template) return;
            var row = template.content.firstElementChild.cloneNode(true);
            list.appendChild(row);

            if (data) {
                var namaInput = row.querySelector('.nama-input');
                var bobotInput = row.querySelector('.bobot-input');
                var idInput = row.querySelector('.id-input');
                if (namaInput && data.nama) namaInput.value = data.nama;
                if (bobotInput && data.bobot !== undefined) bobotInput.value = data.bobot;
                if (idInput && data.id) idInput.value = data.id;
            }

            reindex();
            refresh();
        }

        if (btnTambah) {
            btnTambah.addEventListener('click', function () {
                tambahBaris();
            });
        }

        list.addEventListener('click', function (e) {
            var hapus = e.target.closest('.btn-hapus-komponen');
            if (!hapus) return;
            e.preventDefault();
            if (rows().length <= 1) {
                alert('Minimal harus ada satu komponen penilaian.');
                return;
            }
            hapus.closest('.komponen-row').remove();
            reindex();
            refresh();
        });

        list.addEventListener('input', function (e) {
            if (e.target.classList.contains('bobot-input')) refresh();
        });

        if (form) {
            form.addEventListener('submit', function (e) {
                var total = 0;
                rows().forEach(function (row) {
                    var v = parseFloat(row.querySelector('.bobot-input').value);
                    total += isNaN(v) ? 0 : v;
                });
                if (Math.round(total * 100) / 100 !== 100) {
                    e.preventDefault();
                    alert('Total seluruh bobot harus 100%. Periksa kembali bobot tiap komponen.');
                }
            });
        }

        refresh();
    }

    /* ------------------------------------------------------------------ *
     * Input nilai: pencarian mahasiswa + validasi 0..100 (FR-18, FR-21)
     * ------------------------------------------------------------------ */
    function initNilai() {
        var search = document.getElementById('search-mahasiswa');
        if (search) {
            search.addEventListener('input', function () {
                var kata = search.value.toLowerCase().trim();
                document.querySelectorAll('#tabel-nilai tbody tr[data-nim]').forEach(function (tr) {
                    var teks = (tr.getAttribute('data-nim') + ' ' + tr.getAttribute('data-nama')).toLowerCase();
                    tr.style.display = teks.indexOf(kata) !== -1 ? '' : 'none';
                });
            });
        }

        document.querySelectorAll('.nilai-input').forEach(function (input) {
            input.addEventListener('input', function () {
                validasiNilai(input);
            });
            input.addEventListener('blur', function () {
                validasiNilai(input);
            });
        });

        var formNilai = document.getElementById('form-nilai');
        if (formNilai) {
            formNilai.addEventListener('submit', function (e) {
                var invalid = false;
                document.querySelectorAll('.nilai-input').forEach(function (input) {
                    if (!validasiNilai(input)) invalid = true;
                });
                if (invalid) {
                    e.preventDefault();
                    alert('Terdapat nilai yang tidak valid. Nilai harus angka antara 0 sampai 100.');
                }
            });
        }
    }

    function validasiNilai(input) {
        var raw = input.value.trim();
        input.classList.remove('is-invalid');

        if (raw === '') return true;

        var val = Number(raw);
        if (isNaN(val) || val < 0 || val > 100) {
            input.classList.add('is-invalid');
            return false;
        }
        return true;
    }

    /* ------------------------------------------------------------------ *
     * Data mahasiswa: modal tambah/edit + kelas dependen
     * ------------------------------------------------------------------ */
    function initMahasiswa() {
        var modal = document.getElementById('modal-mahasiswa');
        if (!modal) return;

        var form = document.getElementById('form-mahasiswa');
        var title = document.getElementById('modal-mahasiswa-title');
        var methodInput = document.getElementById('form-mahasiswa-method');
        var mkSelect = document.getElementById('form-mk-select');
        var kelasSelect = document.getElementById('form-kelas-select');
        var nimInput = form.querySelector('[name="nim"]');
        var namaInput = form.querySelector('[name="nama"]');
        var prodiInput = form.querySelector('[name="program_studi"]');
        var storeUrl = form.getAttribute('data-store-url');
        var updateUrlTemplate = form.getAttribute('data-update-url');

        function filterKelas() {
            if (!mkSelect || !kelasSelect) return;
            var mk = mkSelect.value;
            var current = kelasSelect.value;
            kelasSelect.querySelectorAll('option[data-mk]').forEach(function (opt) {
                var cocok = !mk || opt.getAttribute('data-mk') === mk;
                opt.hidden = !cocok;
                opt.disabled = !cocok;
            });
            var terpilih = kelasSelect.querySelector('option[value="' + current + '"]');
            if (terpilih && (terpilih.hidden || terpilih.disabled)) {
                kelasSelect.value = '';
            }
        }

        function buka() { modal.classList.add('open'); }
        function tutup() { modal.classList.remove('open'); }

        document.querySelectorAll('[data-open-mahasiswa]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                form.reset();
                form.setAttribute('action', storeUrl);
                if (methodInput) methodInput.value = 'POST';
                if (title) title.textContent = 'Tambah Mahasiswa';
                filterKelas();
                buka();
            });
        });

        document.querySelectorAll('.btn-edit-mahasiswa').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                form.reset();
                if (title) title.textContent = 'Ubah Data Mahasiswa';
                if (methodInput) methodInput.value = 'PUT';
                form.setAttribute('action', updateUrlTemplate.replace('__ID__', btn.getAttribute('data-id')));
                nimInput.value = btn.getAttribute('data-nim') || '';
                namaInput.value = btn.getAttribute('data-nama') || '';
                if (prodiInput) prodiInput.value = btn.getAttribute('data-prodi') || '';
                var mkId = btn.getAttribute('data-mk') || '';
                if (mkSelect) mkSelect.value = mkId;
                filterKelas();
                if (kelasSelect) kelasSelect.value = btn.getAttribute('data-kelas') || '';
                buka();
            });
        });

        modal.querySelectorAll('[data-close-modal]').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                tutup();
            });
        });

        modal.addEventListener('click', function (e) {
            if (e.target === modal) tutup();
        });

        if (mkSelect) mkSelect.addEventListener('change', filterKelas);

        filterKelas();
    }

    /* ------------------------------------------------------------------ *
     * Rekap: submit otomatis saat filter berubah
     * ------------------------------------------------------------------ */
    function initRekap() {
        var form = document.getElementById('form-filter-rekap');
        if (!form) return;
        form.querySelectorAll('select').forEach(function (sel) {
            sel.addEventListener('change', function () {
                form.submit();
            });
        });
    }

    /* ------------------------------------------------------------------ *
     * Konfirmasi hapus
     * ------------------------------------------------------------------ */
    function initConfirm() {
        document.querySelectorAll('[data-confirm]').forEach(function (el) {
            el.addEventListener('submit', function (e) {
                if (!window.confirm(el.getAttribute('data-confirm'))) {
                    e.preventDefault();
                }
            });
        });
    }

    function initFlashAutoHide() {
        document.querySelectorAll('.alert[data-auto-hide]').forEach(function (el) {
            setTimeout(function () {
                el.style.transition = 'opacity .4s ease';
                el.style.opacity = '0';
                setTimeout(function () { el.remove(); }, 400);
            }, 6000);
        });
    }

    function formatAngka(n) {
        return (Math.round(n * 100) / 100)
            .toString()
            .replace('.', ',');
    }
})();
