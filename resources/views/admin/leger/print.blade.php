<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Leger Nilai Kelas {{ data_get($ledgerData, 'schoolClass.name') }} — {{ data_get($ledgerData, 'semester.name') }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #111;
            margin: 0;
            padding: 10px;
        }

        .header-kop {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }

        .header-kop h1 {
            font-size: 14px;
            margin: 0;
            text-transform: uppercase;
        }

        .header-kop h2 {
            font-size: 12px;
            margin: 2px 0;
        }

        .header-kop p {
            font-size: 9px;
            margin: 0;
            color: #444;
        }

        .meta-info {
            width: 100%;
            margin-bottom: 10px;
            font-size: 10px;
        }

        .meta-info td {
            padding: 2px 4px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        table.data-table th, table.data-table td {
            border: 1px solid #333;
            padding: 3px 4px;
        }

        table.data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .signatures {
            margin-top: 25px;
            width: 100%;
            page-break-inside: avoid;
        }

        .signatures td {
            text-align: center;
            vertical-align: top;
            width: 50%;
            font-size: 10px;
        }

        .print-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #064e3b;
            color: #fff;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.15);
            border: none;
        }

        @media print {
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="print-btn">🖨️ Cetak / Print Dokumen</button>

    <div class="header-kop">
        <h1>Kementerian Agama Republik Indonesia</h1>
        <h2>{{ data_get($ledgerData, 'school.name', 'MADRASAH IBTIDAIYAH') }}</h2>
        <p>
            Alamat: {{ data_get($ledgerData, 'school.address', '-') }}
            @if(data_get($ledgerData, 'school.regency')), {{ data_get($ledgerData, 'school.regency') }}@endif
            | NSM: {{ data_get($ledgerData, 'school.nsm', '-') }} | NPSN: {{ data_get($ledgerData, 'school.npsn', '-') }}
        </p>
    </div>

    <table class="meta-info">
        <tr>
            <td style="width: 12%; font-weight: bold;">Buku Leger Nilai</td>
            <td style="width: 2%;">:</td>
            <td style="width: 36%;">Kelas {{ data_get($ledgerData, 'schoolClass.name') }} (Tingkat {{ data_get($ledgerData, 'schoolClass.grade_level') }})</td>
            <td style="width: 12%; font-weight: bold;">Tahun Ajaran</td>
            <td style="width: 2%;">:</td>
            <td style="width: 36%;">{{ data_get($ledgerData, 'semester.academicYear.name') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Semester</td>
            <td>:</td>
            <td>Semester {{ data_get($ledgerData, 'semester.name') }}</td>
            <td style="font-weight: bold;">Wali Kelas</td>
            <td>:</td>
            <td>{{ data_get($ledgerData, 'teacher.teacher_name', '-') }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" rowspan="2">No</th>
                <th style="width: 50px;" rowspan="2">NIS</th>
                <th style="width: 150px;" rowspan="2">Nama Peserta Didik</th>
                <th colspan="{{ count($ledgerData['subjects']) }}">Mata Pelajaran</th>
                <th style="width: 45px;" rowspan="2">Jumlah</th>
                <th style="width: 45px;" rowspan="2">Rata2</th>
                @if($showRanking)
                    <th style="width: 40px;" rowspan="2">Rangking</th>
                @endif
                <th colspan="3">Absensi</th>
            </tr>
            <tr>
                @foreach($ledgerData['subjects'] as $subj)
                    <th style="font-size: 8px; width: 40px;" title="{{ $subj->name }}">{{ $subj->code }}</th>
                @endforeach
                <th style="width: 20px;">S</th>
                <th style="width: 20px;">I</th>
                <th style="width: 20px;">A</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ledgerData['rows'] as $idx => $row)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center font-bold">{{ $row['student']->nis }}</td>
                    <td>{{ $row['student']->name }}</td>

                    @foreach($ledgerData['subjects'] as $subj)
                        <td class="text-center">{{ $row['scores'][$subj->id] ?? '-' }}</td>
                    @endforeach

                    <td class="text-center font-bold">{{ $row['total_score'] }}</td>
                    <td class="text-center font-bold">{{ number_format($row['average_score'], 1) }}</td>

                    @if($showRanking)
                        <td class="text-center font-bold">{{ $row['rank'] }}</td>
                    @endif

                    <td class="text-center">{{ $row['attendance']['sakit'] ?: '-' }}</td>
                    <td class="text-center">{{ $row['attendance']['izin'] ?: '-' }}</td>
                    <td class="text-center">{{ $row['attendance']['alpa'] ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f7f7f7; font-weight: bold;">
                <td colspan="3" class="text-right">Rata-Rata Kelas:</td>
                @foreach($ledgerData['subjects'] as $subj)
                    <td class="text-center">{{ $ledgerData['statistics']['subject_averages'][$subj->id] ?? '-' }}</td>
                @endforeach
                <td></td>
                <td class="text-center">{{ number_format($ledgerData['statistics']['overall_class_average'], 1) }}</td>
                @if($showRanking)
                    <td></td>
                @endif
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

    <table class="signatures">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala Madrasah<br><br><br><br>
                <strong>{{ data_get($ledgerData, 'school.principal_name', '................................................') }}</strong><br>
                NIP. {{ data_get($ledgerData, 'school.principal_nip', '—') }}
            </td>
            <td>
                {{ data_get($ledgerData, 'school.regency', 'Jakarta') }}, {{ now()->isoFormat('D MMMM Y') }}<br>
                Wali Kelas<br><br><br><br>
                <strong>{{ data_get($ledgerData, 'teacher.teacher_name', '................................................') }}</strong><br>
                NIP. {{ data_get($ledgerData, 'teacher.teacher_nip', '—') }}
            </td>
        </tr>
    </table>

</body>
</html>
