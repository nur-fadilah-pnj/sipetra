<?php
$title = "Dashboard";
require_once __DIR__ . '/includes/functions.php';
cek_login();

// Statistik
$total    = $pdo->query("SELECT COUNT(*) FROM pengaduan")->fetchColumn();
$diterima = $pdo->query("SELECT COUNT(*) FROM pengaduan WHERE status='Diterima'")->fetchColumn();
$proses   = $pdo->query("SELECT COUNT(*) FROM pengaduan WHERE status IN ('Menunggu Persetujuan Camat','Disetujui','Ditindaklanjuti')")->fetchColumn();
$selesai  = $pdo->query("SELECT COUNT(*) FROM pengaduan WHERE status='Selesai'")->fetchColumn();
$ditolak  = $pdo->query("SELECT COUNT(*) FROM pengaduan WHERE status='Ditolak'")->fetchColumn();

// Data untuk grafik: pengaduan per bulan (6 bulan terakhir)
$chart_bulan_labels = [];
$chart_bulan_data   = [];
for ($i = 5; $i >= 0; $i--) {
    $ts = strtotime("-$i months");
    $chart_bulan_labels[] = date('M Y', $ts);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pengaduan WHERE YEAR(tgl_diterima)=? AND MONTH(tgl_diterima)=?");
    $stmt->execute([date('Y', $ts), date('n', $ts)]);
    $chart_bulan_data[] = (int)$stmt->fetchColumn();
}

// Data untuk grafik: per kategori
$stmt = $pdo->query("
    SELECT k.nama_kategori, COUNT(p.id_pengaduan) AS jml
    FROM kategori k
    LEFT JOIN pengaduan p ON k.id_kategori = p.id_kategori
    GROUP BY k.id_kategori, k.nama_kategori
    HAVING jml > 0
    ORDER BY jml DESC
");
$chart_kat = $stmt->fetchAll();

// Pengaduan terbaru
$terbaru = $pdo->query("
    SELECT p.*, k.nama_kategori
    FROM pengaduan p
    LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
    ORDER BY p.created_at DESC LIMIT 5
")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">📊</div>
        <div class="num"><?= $total ?></div>
        <div class="lbl">Total Pengaduan</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon">📥</div>
        <div class="num"><?= $diterima ?></div>
        <div class="lbl">Diterima</div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-icon">⏳</div>
        <div class="num"><?= $proses ?></div>
        <div class="lbl">Dalam Proses</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon">✅</div>
        <div class="num"><?= $selesai ?></div>
        <div class="lbl">Selesai</div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon">❌</div>
        <div class="num"><?= $ditolak ?></div>
        <div class="lbl">Ditolak</div>
    </div>
</div>

<!-- Grafik -->
<div class="chart-grid">
    <div class="chart-card">
        <h3>📈 Tren Pengaduan 6 Bulan Terakhir</h3>
        <div class="chart-wrapper">
            <canvas id="chartBulan"></canvas>
        </div>
    </div>
    <div class="chart-card">
        <h3>🥧 Distribusi per Kategori</h3>
        <div class="chart-wrapper">
            <canvas id="chartKategori"></canvas>
        </div>
    </div>
</div>

<!-- Tabel Terbaru -->
<div class="card">
    <div class="card-header">
        <h3>🕒 Pengaduan Terbaru</h3>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>No Surat</th><th>Tanggal</th><th>Kategori</th><th>Lokasi</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php foreach ($terbaru as $r): ?>
                <tr>
                    <td><b><?= e($r['no_surat']) ?></b></td>
                    <td><?= e(date('d/m/Y', strtotime($r['tgl_diterima']))) ?></td>
                    <td><?= e($r['nama_kategori'] ?? '-') ?></td>
                    <td><?= e($r['lokasi_kejadian']) ?></td>
                    <td><?= badge_status($r['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($terbaru)): ?>
                <tr><td colspan="5" style="text-align:center;color:var(--text-muted)">Belum ada pengaduan.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const PRIMARY = '#0f4c81';
const ACCENT  = '#d4a017';

// Grafik tren bulanan (line)
new Chart(document.getElementById('chartBulan'), {
    type: 'line',
    data: {
        labels: <?= json_encode($chart_bulan_labels) ?>,
        datasets: [{
            label: 'Jumlah Pengaduan',
            data: <?= json_encode($chart_bulan_data) ?>,
            borderColor: PRIMARY,
            backgroundColor: 'rgba(15, 76, 129, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: ACCENT,
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1a2233',
                padding: 12,
                cornerRadius: 8,
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1, color: '#6b7a90' },
                grid: { color: '#eef2f7' }
            },
            x: {
                ticks: { color: '#6b7a90' },
                grid: { display: false }
            }
        }
    }
});

// Grafik kategori (doughnut)
const katData = <?= json_encode($chart_kat) ?>;
new Chart(document.getElementById('chartKategori'), {
    type: 'doughnut',
    data: {
        labels: katData.map(k => k.nama_kategori),
        datasets: [{
            data: katData.map(k => k.jml),
            backgroundColor: [
                '#0f4c81','#1565a8','#2563eb','#3b82f6','#60a5fa',
                '#d4a017','#f59e0b','#fbbf24','#fcd34d','#fde68a'
            ],
            borderWidth: 3,
            borderColor: '#fff',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '60%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: { padding: 14, font: { size: 11 }, color: '#1a2233' }
            }
        }
    }
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>