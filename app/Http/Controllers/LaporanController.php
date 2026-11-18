<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Kunjungan;
use App\Models\Denda;
use App\Models\Buku;
use App\Models\EksemplarBuku;
use App\Models\Kategori;
use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'peminjaman');
        $tglMulai = $request->get('tgl_mulai', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $tglSelesai = $request->get('tgl_selesai', Carbon::today()->format('Y-m-d'));

        $kategoriList = Kategori::orderBy('nama_kategori', 'asc')->get();
        $pengaturan = Pengaturan::getPengaturan();

        // 1. Data Peminjaman
        $peminjamanQuery = Peminjaman::with(['anggota', 'eksemplar.buku.kategori', 'user', 'denda'])
            ->whereDate('tgl_pinjam', '>=', $tglMulai)
            ->whereDate('tgl_pinjam', '<=', $tglSelesai);

        if ($request->filled('status_pinjam')) {
            $peminjamanQuery->where('status', $request->status_pinjam);
        }
        $dataPeminjaman = $peminjamanQuery->latest('tgl_pinjam')->get();

        // 2. Data Pengembalian
        $pengembalianQuery = Peminjaman::with(['anggota', 'eksemplar.buku', 'denda'])
            ->where('status', 'Selesai')
            ->whereNotNull('tgl_kembali')
            ->whereDate('tgl_kembali', '>=', $tglMulai)
            ->whereDate('tgl_kembali', '<=', $tglSelesai);

        if ($request->filled('kondisi_kembali')) {
            $pengembalianQuery->where('kondisi_kembali', $request->kondisi_kembali);
        }
        $dataPengembalian = $pengembalianQuery->latest('tgl_kembali')->get();

        // 3. Data Denda
        $dendaQuery = Denda::with(['peminjaman.anggota', 'peminjaman.eksemplar.buku'])
            ->whereDate('created_at', '>=', $tglMulai)
            ->whereDate('created_at', '<=', $tglSelesai);

        if ($request->filled('status_bayar')) {
            $dendaQuery->where('status_bayar', $request->status_bayar);
        }
        $dataDenda = $dendaQuery->latest('id')->get();

        // 4. Data Kunjungan
        $kunjunganQuery = Kunjungan::with(['anggota', 'detailBuku.eksemplar.buku'])
            ->whereDate('tanggal', '>=', $tglMulai)
            ->whereDate('tanggal', '<=', $tglSelesai);

        if ($request->filled('status_kunjungan')) {
            $kunjunganQuery->where('status', $request->status_kunjungan);
        }
        $dataKunjungan = $kunjunganQuery->latest('tanggal')->get();

        // 5. Data Stok & Eksemplar Buku
        $eksemplarQuery = EksemplarBuku::with(['buku.kategori', 'laci.lemari']);
        if ($request->filled('id_kategori')) {
            $eksemplarQuery->whereHas('buku', function($q) use ($request) {
                $q->where('id_kategori', $request->id_kategori);
            });
        }
        if ($request->filled('status_eksemplar')) {
            $eksemplarQuery->where('status', $request->status_eksemplar);
        }
        $dataEksemplar = $eksemplarQuery->orderBy('kode_eksemplar', 'asc')->get();

        return view('laporan.index', compact(
            'tab',
            'tglMulai',
            'tglSelesai',
            'kategoriList',
            'pengaturan',
            'dataPeminjaman',
            'dataPengembalian',
            'dataDenda',
            'dataKunjungan',
            'dataEksemplar'
        ));
    }

    public function cetak(Request $request)
    {
        return view('laporan.cetak', $this->buildReportPayload($request));
    }

    public function exportExcel(Request $request)
    {
        $payload = $this->buildReportPayload($request);
        $jenis = $payload['jenis'];
        $title = $payload['title'];
        $tglMulai = $payload['tglMulai'];
        $tglSelesai = $payload['tglSelesai'];
        $pengaturan = $payload['pengaturan'];
        $data = $payload['data'];

        [$headers, $rows, $currencyColumns, $totalRow] = $this->formatExcelTable($jenis, $data);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr(ucfirst($jenis), 0, 31));

        $colCount = count($headers);
        $lastCol = Coordinate::stringFromColumnIndex($colCount);

        // Header Perpustakaan & Judul Laporan
        $sheet->setCellValue('A1', $pengaturan->nama_perpustakaan ?? 'Perpustakaan');
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $alamatParts = array_filter([
            $pengaturan->alamat_perpustakaan ?? null,
            !empty($pengaturan->no_telp_perpustakaan) ? 'Telp: ' . $pengaturan->no_telp_perpustakaan : null,
        ]);
        $sheet->setCellValue('A2', implode(' • ', $alamatParts));
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->getStyle('A2')->getFont()->setSize(10);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A3', strtoupper($title));
        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $periodeText = $jenis === 'buku'
            ? 'Dicetak pada: ' . Carbon::now()->format('d/m/Y H:i')
            : 'Periode: ' . Carbon::parse($tglMulai)->format('d/m/Y') . ' s/d ' . Carbon::parse($tglSelesai)->format('d/m/Y');
        $sheet->setCellValue('A4', $periodeText);
        $sheet->mergeCells("A4:{$lastCol}4");
        $sheet->getStyle('A4')->getFont()->setSize(10)->setItalic(true);
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Table Header (Row 6)
        $headerRow = 6;
        foreach ($headers as $colIdx => $headerText) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValue("{$colLetter}{$headerRow}", $headerText);
        }

        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E9ECEF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Data Rows
        $currentRow = $headerRow + 1;
        if (empty($rows)) {
            $sheet->setCellValue("A{$currentRow}", 'Tidak ada data pada periode/filter ini.');
            $sheet->mergeCells("A{$currentRow}:{$lastCol}{$currentRow}");
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $lastDataRow = $currentRow;
        } else {
            foreach ($rows as $rowValues) {
                foreach ($rowValues as $colIdx => $value) {
                    $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
                    $sheet->setCellValue("{$colLetter}{$currentRow}", $value);
                }
                $currentRow++;
            }

            if ($totalRow !== null) {
                foreach ($totalRow as $colIdx => $value) {
                    $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
                    $sheet->setCellValue("{$colLetter}{$currentRow}", $value);
                }
                $sheet->getStyle("A{$currentRow}:{$lastCol}{$currentRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8F9FA'],
                    ],
                ]);
                $lastDataRow = $currentRow;
            } else {
                $lastDataRow = $currentRow - 1;
            }
        }

        // Format currency columns & borders
        foreach ($currencyColumns as $colIndexOneBased) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndexOneBased);
            $sheet->getStyle("{$colLetter}" . ($headerRow + 1) . ":{$colLetter}{$lastDataRow}")
                ->getNumberFormat()
                ->setFormatCode('#,##0');
        }

        $sheet->getStyle("A{$headerRow}:{$lastCol}{$lastDataRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '333333'],
                ],
            ],
        ]);

        for ($i = 1; $i <= $colCount; $i++) {
            $colLetter = Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $filename = $jenis === 'buku'
            ? "laporan-stok-buku-" . Carbon::now()->format('Y-m-d') . ".xlsx"
            : "laporan-{$jenis}-{$tglMulai}-sd-{$tglSelesai}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Build shared report query payload for print and Excel export.
     */
    private function buildReportPayload(Request $request): array
    {
        $jenis = $request->get('jenis', 'peminjaman');
        if (!in_array($jenis, ['peminjaman', 'pengembalian', 'denda', 'kunjungan', 'buku'], true)) {
            $jenis = 'peminjaman';
        }

        $tglMulai = $request->get('tgl_mulai', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $tglSelesai = $request->get('tgl_selesai', Carbon::today()->format('Y-m-d'));
        $pengaturan = Pengaturan::getPengaturan();

        $data = collect();
        $title = '';

        if ($jenis === 'peminjaman') {
            $title = 'Laporan Transaksi Peminjaman Buku';
            $q = Peminjaman::with(['anggota', 'eksemplar.buku.kategori', 'user', 'denda'])
                ->whereDate('tgl_pinjam', '>=', $tglMulai)
                ->whereDate('tgl_pinjam', '<=', $tglSelesai);
            if ($request->filled('status')) {
                $q->where('status', $request->status);
            }
            $data = $q->latest('tgl_pinjam')->get();
        } elseif ($jenis === 'pengembalian') {
            $title = 'Laporan Transaksi Pengembalian Buku';
            $q = Peminjaman::with(['anggota', 'eksemplar.buku', 'denda'])
                ->where('status', 'Selesai')
                ->whereNotNull('tgl_kembali')
                ->whereDate('tgl_kembali', '>=', $tglMulai)
                ->whereDate('tgl_kembali', '<=', $tglSelesai);
            if ($request->filled('kondisi')) {
                $q->where('kondisi_kembali', $request->kondisi);
            }
            $data = $q->latest('tgl_kembali')->get();
        } elseif ($jenis === 'denda') {
            $title = 'Laporan Denda & Keterlambatan';
            $q = Denda::with(['peminjaman.anggota', 'peminjaman.eksemplar.buku'])
                ->whereDate('created_at', '>=', $tglMulai)
                ->whereDate('created_at', '<=', $tglSelesai);
            if ($request->filled('status_bayar')) {
                $q->where('status_bayar', $request->status_bayar);
            }
            $data = $q->latest('id')->get();
        } elseif ($jenis === 'kunjungan') {
            $title = 'Laporan Kunjungan Anggota Perpustakaan';
            $q = Kunjungan::with(['anggota', 'detailBuku.eksemplar.buku'])
                ->whereDate('tanggal', '>=', $tglMulai)
                ->whereDate('tanggal', '<=', $tglSelesai);
            if ($request->filled('status')) {
                $q->where('status', $request->status);
            }
            $data = $q->latest('tanggal')->get();
        } elseif ($jenis === 'buku') {
            $title = 'Laporan Inventaris Eksemplar & Stok Buku';
            $q = EksemplarBuku::with(['buku.kategori', 'laci.lemari']);
            if ($request->filled('id_kategori')) {
                $q->whereHas('buku', function ($qb) use ($request) {
                    $qb->where('id_kategori', $request->id_kategori);
                });
            }
            if ($request->filled('status')) {
                $q->where('status', $request->status);
            }
            $data = $q->orderBy('kode_eksemplar', 'asc')->get();
        }

        return compact('jenis', 'title', 'tglMulai', 'tglSelesai', 'pengaturan', 'data');
    }

    /**
     * Map report data into Excel headers, rows, currency column indices (1-based), and optional total row.
     */
    private function formatExcelTable(string $jenis, $data): array
    {
        $rows = [];
        $currencyColumns = [];
        $totalRow = null;

        if ($jenis === 'peminjaman') {
            $headers = ['No', 'No. Anggota', 'Nama Peminjam', 'Kode Eksemplar', 'Judul Buku', 'Tgl Pinjam', 'Jatuh Tempo', 'Tgl Kembali', 'Status'];
            foreach ($data as $index => $item) {
                $rows[] = [
                    $index + 1,
                    $item->anggota->no_anggota ?? '-',
                    $item->anggota->nama ?? '-',
                    $item->eksemplar->kode_eksemplar ?? ($item->buku->kode_induk_buku ?? '-'),
                    $item->buku->judul ?? '-',
                    $item->tgl_pinjam ? $item->tgl_pinjam->format('d/m/Y') : '-',
                    $item->tgl_jatuh_tempo ? $item->tgl_jatuh_tempo->format('d/m/Y') : '-',
                    $item->tgl_kembali ? $item->tgl_kembali->format('d/m/Y') : '-',
                    $item->status,
                ];
            }
        } elseif ($jenis === 'pengembalian') {
            $headers = ['No', 'Nama Peminjam', 'Kode Eksemplar', 'Judul Buku', 'Tgl Pinjam', 'Tgl Kembali', 'Kondisi Buku', 'Denda (Rp)'];
            $currencyColumns = [8];
            foreach ($data as $index => $item) {
                $rows[] = [
                    $index + 1,
                    $item->anggota->nama ?? '-',
                    $item->eksemplar->kode_eksemplar ?? ($item->buku->kode_induk_buku ?? '-'),
                    $item->buku->judul ?? '-',
                    $item->tgl_pinjam ? $item->tgl_pinjam->format('d/m/Y') : '-',
                    $item->tgl_kembali ? $item->tgl_kembali->format('d/m/Y') : '-',
                    ucfirst((string) $item->kondisi_kembali),
                    (int) ($item->denda->nominal ?? 0),
                ];
            }
        } elseif ($jenis === 'denda') {
            $headers = ['No', 'Peminjam', 'Buku', 'Tgl Tercatat', 'Nominal Denda (Rp)', 'Status Pembayaran', 'Tgl Lunas'];
            $currencyColumns = [5];
            $totalNominal = 0;
            foreach ($data as $index => $item) {
                $nominal = (int) $item->nominal;
                $totalNominal += $nominal;
                $rows[] = [
                    $index + 1,
                    $item->peminjaman->anggota->nama ?? '-',
                    $item->peminjaman->buku->judul ?? '-',
                    $item->created_at ? $item->created_at->format('d/m/Y') : '-',
                    $nominal,
                    $item->status_bayar,
                    $item->tgl_bayar ? $item->tgl_bayar->format('d/m/Y') : '-',
                ];
            }
            if (!empty($rows)) {
                $totalRow = ['', '', '', 'TOTAL', $totalNominal, '', ''];
            }
        } elseif ($jenis === 'kunjungan') {
            $headers = ['No', 'No. Anggota', 'Nama Anggota', 'Tanggal', 'Waktu Masuk', 'Waktu Keluar', 'Jml Buku Dibaca', 'Status'];
            foreach ($data as $index => $item) {
                $rows[] = [
                    $index + 1,
                    $item->anggota->no_anggota ?? '-',
                    $item->anggota->nama ?? '-',
                    $item->tanggal ? $item->tanggal->format('d/m/Y') : '-',
                    $item->waktu_masuk,
                    $item->waktu_keluar ?: '-',
                    $item->detailBuku->count(),
                    $item->status,
                ];
            }
        } else {
            $headers = ['No', 'Kode Eksemplar', 'Judul Buku', 'Pengarang', 'Kategori', 'Lokasi Rak/Laci', 'Kondisi', 'Status'];
            foreach ($data as $index => $item) {
                $lokasi = $item->laci && $item->laci->lemari
                    ? ($item->laci->lemari->kode_lemari . ' - ' . $item->laci->kode_laci)
                    : '-';
                $rows[] = [
                    $index + 1,
                    $item->kode_eksemplar,
                    $item->buku->judul ?? '-',
                    $item->buku->pengarang ?? '-',
                    $item->buku->kategori->nama_kategori ?? 'Umum',
                    $lokasi,
                    ucfirst((string) $item->kondisi),
                    ucfirst((string) $item->status),
                ];
            }
        }

        return [$headers, $rows, $currencyColumns, $totalRow];
    }
}
