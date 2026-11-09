<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Hasil Belajar — {{ data_get($snapshot, 'student.name') }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 14mm 15mm 15mm 15mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.35;
            color: #1e293b;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .header-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }

        .kop-logo {
            width: 70px;
            text-align: left;
        }

        .kop-text {
            text-align: center;
        }

        .kop-title-kemag {
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.5px;
            color: #064e3b;
            text-transform: uppercase;
        }

        .kop-school-name {
            font-size: 14px;
            font-weight: bold;
            color: #064e3b;
            margin-top: 2px;
            margin-bottom: 2px;
            letter-spacing: 0.5px;
        }

        .kop-meta {
            font-size: 8px;
            color: #334155;
            margin-bottom: 2px;
        }

        .kop-divider {
            border-top: 2px solid #064e3b;
            border-bottom: 1px solid #064e3b;
            height: 2px;
            margin-bottom: 12px;
        }

        .title-box {
            text-align: center;
            margin-bottom: 12px;
        }

        .report-title {
            font-size: 12px;
            font-weight: bold;
            color: #064e3b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .identity-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 9px;
        }

        .identity-table td {
            vertical-align: top;
            padding: 2px 4px;
            border: none;
        }

        .section-title {
            font-size: 10.5px;
            font-weight: bold;
            color: #064e3b;
            margin-top: 10px;
            margin-bottom: 5px;
            padding-bottom: 2px;
            border-bottom: 1px solid #cbd5e1;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9px;
        }

        .data-table th, .data-table td {
            border: 1px solid #94a3b8;
            padding: 4px 6px;
            vertical-align: top;
        }

        .data-table th {
            background-color: #ecfdf5;
            color: #064e3b;
            font-weight: bold;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .summary-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            margin-bottom: 8px;
            border-radius: 4px;
            font-size: 9px;
        }

        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 9px;
            page-break-inside: avoid;
        }

        .signatures-table td {
            border: none;
            padding: 4px;
            vertical-align: top;
            text-align: center;
        }

        .avoid-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>

    <!-- KOP MADRASAH -->
    <table class="header-table">
        <tr>
            @if(data_get($snapshot, 'school.logo'))
                <td class="kop-logo">
                    <img src="data:image/png;base64,{{ data_get($snapshot, 'school.logo') }}" style="max-height: 55px; max-width: 65px;" alt="Logo">
                </td>
            @endif
            <td class="kop-text">
                <div class="kop-title-kemag">Kementerian Agama Republik Indonesia</div>
                <div class="kop-school-name">{{ data_get($snapshot, 'school.name', 'MADRASAH IBTIDAIYAH') }}</div>
                <div class="kop-meta">
                    @if(data_get($snapshot, 'school.nsm')) NSM: {{ data_get($snapshot, 'school.nsm') }} &nbsp;|&nbsp; @endif
                    @if(data_get($snapshot, 'school.npsn')) NPSN: {{ data_get($snapshot, 'school.npsn') }} @endif
                </div>
                <div class="kop-meta">
                    {{ data_get($snapshot, 'school.address', '-') }}
                    @if(data_get($snapshot, 'school.village')), Kel. {{ data_get($snapshot, 'school.village') }}@endif
                    @if(data_get($snapshot, 'school.district')), Kec. {{ data_get($snapshot, 'school.district') }}@endif
                    @if(data_get($snapshot, 'school.regency')), {{ data_get($snapshot, 'school.regency') }}@endif
                    @if(data_get($snapshot, 'school.province')), {{ data_get($snapshot, 'school.province') }}@endif
                </div>
            </td>
        </tr>
    </table>
    <div class="kop-divider"></div>

    <!-- JUDUL RAPOR -->
    <div class="title-box">
        <div class="report-title">Laporan Hasil Belajar Peserta Didik</div>
        <div style="font-size: 9px; color: #475569; margin-top: 2px;">
            Semester {{ data_get($snapshot, 'enrollment.semester', '-') }} Tahun Ajaran {{ data_get($snapshot, 'enrollment.academic_year', '-') }}
        </div>
    </div>

    <!-- IDENTITAS SISWA & KELAS -->
    <table class="identity-table">
        <tr>
            <td style="width: 18%; font-weight: bold;">Nama Peserta Didik</td>
            <td style="width: 2%;">:</td>
            <td style="width: 38%;">{{ data_get($snapshot, 'student.name', '-') }}</td>
            <td style="width: 16%; font-weight: bold;">Kelas / Jenjang</td>
            <td style="width: 2%;">:</td>
            <td style="width: 24%;">{{ data_get($snapshot, 'enrollment.class_name', '-') }} / Kelas {{ data_get($snapshot, 'enrollment.grade_level', '-') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">NIS / NISN</td>
            <td>:</td>
            <td>{{ data_get($snapshot, 'student.nis', '-') }} / {{ data_get($snapshot, 'student.nisn', '-') }}</td>
            <td style="font-weight: bold;">Fase</td>
            <td>:</td>
            <td>
                @php
                    $grade = (int) data_get($snapshot, 'enrollment.grade_level', 1);
                    $fase = $grade <= 2 ? 'Fase A' : ($grade <= 4 ? 'Fase B' : 'Fase C');
                @endphp
                {{ $fase }}
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Tempat, Tanggal Lahir</td>
            <td>:</td>
            <td>
                {{ data_get($snapshot, 'student.birth_place', '-') }},
                @if(data_get($snapshot, 'student.birth_date'))
                    {{ \Carbon\Carbon::parse(data_get($snapshot, 'student.birth_date'))->isoFormat('D MMMM Y') }}
                @else
                    -
                @endif
            </td>
            <td style="font-weight: bold;">Semester</td>
            <td>:</td>
            <td>{{ data_get($snapshot, 'enrollment.semester', '-') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Jenis Kelamin</td>
            <td>:</td>
            <td>{{ data_get($snapshot, 'student.gender', '-') }}</td>
            <td style="font-weight: bold;">Tahun Ajaran</td>
            <td>:</td>
            <td>{{ data_get($snapshot, 'enrollment.academic_year', '-') }}</td>
        </tr>
    </table>

    <!-- A. PENILAIAN AKADEMIK -->
    <div class="section-title">A. Nilai Hasil Belajar (Akademik)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 32%;">Mata Pelajaran</th>
                <th style="width: 12%;">Nilai Akhir</th>
                <th style="width: 51%;">Capaian Kompetensi / Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse(data_get($snapshot, 'academics', []) as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $item['subject'] ?? '—' }}</td>
                    <td class="text-center font-bold">
                        {{ isset($item['final_score']) && $item['final_score'] !== null ? $item['final_score'] : '—' }}
                    </td>
                    <td>{{ $item['description'] ?: 'Telah menyelesaikan target pembelajaran dengan capaian yang baik.' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center" style="color: #64748b; font-style: italic;">
                        Belum ada data mata pelajaran dan penilaian akademik.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- B. PENILAIAN SIKAP & PERILAKU -->
    <div class="avoid-break">
        <div class="section-title">B. Penilaian Sikap dan Perilaku</div>
        @if(data_get($snapshot, 'attitude_summary'))
            <div class="summary-box">
                <span class="font-bold" style="color: #064e3b;">Catatan Umum Perkembangan Sikap:</span><br>
                {{ data_get($snapshot, 'attitude_summary') }}
            </div>
        @endif

        @if(!empty(data_get($snapshot, 'attitudes', [])))
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 28%;">Aspek Karakter / Sikap</th>
                        <th style="width: 15%;">Predikat</th>
                        <th style="width: 52%;">Catatan Perkembangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(data_get($snapshot, 'attitudes', []) as $index => $att)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="font-bold">{{ $att['aspect'] ?? '—' }}</td>
                            <td class="text-center font-bold">{{ $att['rating'] ?? '—' }}</td>
                            <td>{{ $att['notes'] ?? 'Menunjukkan perkembangan karakter yang baik dan konsisten.' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- C. KEHADIRAN -->
    <div class="avoid-break">
        <div class="section-title">C. Rekapitulasi Kehadiran</div>
        <table class="data-table" style="width: 65%;">
            <thead>
                <tr>
                    <th style="width: 10%;">No</th>
                    <th style="width: 60%;">Keterangan Ketidakhadiran</th>
                    <th style="width: 30%;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">1</td>
                    <td>Sakit (S)</td>
                    <td class="text-center font-bold">{{ data_get($snapshot, 'attendance.sakit', 0) }} hari</td>
                </tr>
                <tr>
                    <td class="text-center">2</td>
                    <td>Izin (I)</td>
                    <td class="text-center font-bold">{{ data_get($snapshot, 'attendance.izin', 0) }} hari</td>
                </tr>
                <tr>
                    <td class="text-center">3</td>
                    <td>Tanpa Keterangan (A)</td>
                    <td class="text-center font-bold">{{ data_get($snapshot, 'attendance.alpa', 0) }} hari</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- D. KEGIATAN EKSTRAKURIKULER -->
    <div class="avoid-break">
        <div class="section-title">D. Kegiatan Ekstrakurikuler</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 30%;">Kegiatan Ekstrakurikuler</th>
                    <th style="width: 18%;">Predikat</th>
                    <th style="width: 47%;">Keterangan / Capaian</th>
                </tr>
            </thead>
            <tbody>
                @forelse(data_get($snapshot, 'extracurriculars', []) as $index => $extra)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="font-bold">{{ $extra['name'] ?? '—' }}</td>
                        <td class="text-center font-bold">{{ $extra['predicate'] ?? '—' }}</td>
                        <td>{{ $extra['description'] ?? 'Aktif mengikuti seluruh rangkaian kegiatan ekstrakurikuler.' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center" style="color: #64748b; font-style: italic;">
                            Tidak ada kegiatan ekstrakurikuler yang diikuti pada semester ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- E. PRESTASI SISWA -->
    <div class="avoid-break">
        <div class="section-title">E. Prestasi Siswa</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 35%;">Nama Kegiatan / Prestasi</th>
                    <th style="width: 18%;">Tingkat</th>
                    <th style="width: 18%;">Hasil / Juara</th>
                    <th style="width: 24%;">Penyelenggara</th>
                </tr>
            </thead>
            <tbody>
                @forelse(data_get($snapshot, 'achievements', []) as $index => $ach)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="font-bold">{{ $ach['achievement_name'] ?? '—' }}</td>
                        <td class="text-center">{{ $ach['level'] ?? '—' }}</td>
                        <td class="text-center font-bold">{{ $ach['rank'] ?? '—' }}</td>
                        <td>{{ $ach['organizer'] ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center" style="color: #64748b; font-style: italic;">
                            Tidak ada catatan prestasi pada semester ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- F. CATATAN WALI KELAS -->
    <div class="avoid-break">
        <div class="section-title">F. Catatan Perkembangan dan Rekomendasi Wali Kelas</div>
        <table class="data-table">
            <tbody>
                <tr>
                    <td style="width: 28%; font-weight: bold; background-color: #f8fafc;">Perkembangan Akademik</td>
                    <td style="width: 72%;">{{ data_get($snapshot, 'homeroom_note.academic_progress', 'Perkembangan akademik berlangsung dengan baik sesuai tahapan belajar.') }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; background-color: #f8fafc;">Sikap & Perilaku</td>
                    <td>{{ data_get($snapshot, 'homeroom_note.attitude_behavior', 'Menunjukkan akhlakul karimah dan sikap saling menghormati di lingkungan madrasah.') }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; background-color: #f8fafc;">Kedisiplinan & Ketertiban</td>
                    <td>{{ data_get($snapshot, 'homeroom_note.discipline', 'Mematuhi tata tertib dan memelihara kebiasaan belajar secara disiplin.') }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; background-color: #f8fafc;">Potensi & Bakat Siswa</td>
                    <td>{{ data_get($snapshot, 'homeroom_note.student_potential', 'Memiliki potensi positif yang dapat terus dibina dan dikembangkan.') }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; background-color: #f8fafc;">Hal yang Perlu Ditingkatkan</td>
                    <td>{{ data_get($snapshot, 'homeroom_note.areas_for_improvement', 'Tingkatkan ketekunan dalam latihan mandiri dan eksplorasi materi.') }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; background-color: #f8fafc;">Saran untuk Orang Tua/Wali</td>
                    <td>{{ data_get($snapshot, 'homeroom_note.parent_advice', 'Mohon senantiasa mendampingi dan memberikan motivasi belajar ananda di rumah.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- G. PENGESAHAN / TANDA TANGAN -->
    <div class="avoid-break" style="margin-top: 25px;">
        <table class="signatures-table">
            <tr>
                <td style="width: 33%;">
                    Mengetahui,<br>
                    Orang Tua / Wali Siswa
                    <br><br><br><br><br>
                    .........................................................
                </td>
                <td style="width: 34%;">
                    {{ data_get($snapshot, 'school.regency', 'Jakarta') }}, {{ now()->isoFormat('D MMMM Y') }}<br>
                    Wali Kelas
                    <br><br><br><br><br>
                    <span class="font-bold" style="text-decoration: underline;">
                        {{ data_get($snapshot, 'signatories.homeroom_teacher.teacher_name', '.........................................................') }}
                    </span>
                    @if(data_get($snapshot, 'signatories.homeroom_teacher.teacher_nip'))
                        <br>NIP. {{ data_get($snapshot, 'signatories.homeroom_teacher.teacher_nip') }}
                    @endif
                </td>
                <td style="width: 33%;">
                    Mengetahui,<br>
                    Kepala Madrasah
                    <br><br><br><br><br>
                    <span class="font-bold" style="text-decoration: underline;">
                        {{ data_get($snapshot, 'signatories.principal.principal_name', '.........................................................') }}
                    </span>
                    @if(data_get($snapshot, 'signatories.principal.principal_nip'))
                        <br>NIP. {{ data_get($snapshot, 'signatories.principal.principal_nip') }}
                    @endif
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
