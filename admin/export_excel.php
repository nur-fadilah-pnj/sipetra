<?php
require_once __DIR__ . '/../includes/functions.php';
cek_role(['admin','camat','kasi']);

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

$dari   = $_GET['dari']   ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT p.*, k.nama_kategori
    FROM pengaduan p
    LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
    WHERE p.tgl_diterima BETWEEN ? AND ?
    ORDER BY p.tgl_diterima ASC
");
$stmt->execute([$dari, $sampai]);
$data = $stmt->fetchAll();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Laporan Pengaduan');

// ===== JUDUL =====
$sheet->mergeCells('A1:G1');
$sheet->setCellValue('A1', 'PEMERINTAH KOTA MEDAN');
$sheet->mergeCells('A2:G2');
$sheet->setCellValue('A2', 'KECAMATAN MEDAN PETISAH');
$sheet->mergeCells('A3:G3');
$sheet->setCellValue('A3', 'LAPORAN REKAPITULASI PENGADUAN TRANTIBUM');
$sheet->mergeCells('A4:G4');
$sheet->setCellValue('A4', 'Periode: ' . date('d/m/Y', strtotime($dari)) . ' s/d ' . date('d/m/Y', strtotime($sampai)));

$sheet->getStyle('A1:G4')->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
    ->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
$sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12);
$sheet->getStyle('A3')->getFont()->setUnderline(Font::UNDERLINE_SINGLE);

// ===== HEADER TABEL =====
$headerRow = 6;
$headers = ['No', 'No Surat', 'Tanggal', 'Kategori', 'Lokasi Kejadian', 'Status', 'Pelapor'];
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col . $headerRow, $h);
    $col++;
}

$sheet->getStyle("A{$headerRow}:G{$headerRow}")->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F4C81']],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER,
    ],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
]);

// ===== DATA =====
$row = $headerRow + 1;
$no = 1;
foreach ($data as $r) {
    $sheet->setCellValue('A' . $row, $no++);
    $sheet->setCellValue('B' . $row, $r['no_surat']);
    $sheet->setCellValue('C' . $row, date('d/m/Y', strtotime($r['tgl_diterima'])));
    $sheet->setCellValue('D' . $row, $r['nama_kategori'] ?? '-');
    $sheet->setCellValue('E' . $row, $r['lokasi_kejadian']);
    $sheet->setCellValue('F' . $row, $r['status']);
    $sheet->setCellValue('G' . $row, $r['nama_pelapor'] ?: '-');
    $row++;
}

// Border & alignment untuk data
$lastRow = $row - 1;
if ($lastRow >= $headerRow + 1) {
    $sheet->getStyle("A" . ($headerRow + 1) . ":G{$lastRow}")->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
    ]);
    // Zebra striping
    for ($i = $headerRow + 1; $i <= $lastRow; $i++) {
        if ($i % 2 === 0) {
            $sheet->getStyle("A{$i}:G{$i}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('F5F7FB');
        }
    }
}

// ===== RINGKASAN STATUS =====
$row += 2;
$sheet->mergeCells("A{$row}:G{$row}");
$sheet->setCellValue("A{$row}", 'RINGKASAN STATUS');
$sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
$sheet->getStyle("A{$row}")->getFill()
    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D4A017');
$sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$rekap = [];
foreach ($data as $r) {
    $rekap[$r['status']] = ($rekap[$r['status']] ?? 0) + 1;
}

$row++;
$sheet->setCellValue("A{$row}", 'Status');
$sheet->setCellValue("B{$row}", 'Jumlah');
$sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
$sheet->getStyle("A{$row}:B{$row}")->getFill()
    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EEF2F7');

foreach ($rekap as $s => $j) {
    $row++;
    $sheet->setCellValue("A{$row}", $s);
    $sheet->setCellValue("B{$row}", $j);
}
$row++;
$sheet->setCellValue("A{$row}", 'TOTAL');
$sheet->setCellValue("B{$row}", count($data));
$sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
$sheet->getStyle("A{$row}:B{$row}")->getFill()
    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D1FAE5');

// ===== LEBAR KOLOM =====
$sheet->getColumnDimension('A')->setWidth(6);
$sheet->getColumnDimension('B')->setWidth(18);
$sheet->getColumnDimension('C')->setWidth(14);
$sheet->getColumnDimension('D')->setWidth(24);
$sheet->getColumnDimension('E')->setWidth(32);
$sheet->getColumnDimension('F')->setWidth(24);
$sheet->getColumnDimension('G')->setWidth(20);

// ===== DOWNLOAD =====
$filename = 'Laporan_Pengaduan_Trantibum_' . $dari . '_sd_' . $sampai . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;