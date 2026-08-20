<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/BpjsClient.php';

$bpjs = new BpjsClient();
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $endpoint = 'daftarresep';
    
    // Siapkan data dari form
    $requestData = [
        "kdppk" => trim($_POST['kdppk'] ?? (defined('KODE_PPK') ? KODE_PPK : '')),
        "KdJnsObat" => trim($_POST['KdJnsObat'] ?? '0'),
        "JnsTgl" => trim($_POST['JnsTgl'] ?? 'TGLPELSJP'),
        "TglMulai" => trim($_POST['TglMulai'] ?? ''),
        "TglAkhir" => trim($_POST['TglAkhir'] ?? '')
    ];

    // Request ke BPJS dengan method POST
    // Untuk daftarresep, ternyata BPJS meminta format JSON yang flat (tanpa wrapper "request")
    $result = $bpjs->request($endpoint, 'POST', $requestData, 'application/x-www-form-urlencoded');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Resep - Bridging BPJS Apotek Online</title>
    <style>
        :root {
            --bg-color: #0f172a;
            --surface-color: #1e293b;
            --border-color: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --success: #10b981;
            --danger: #ef4444;
        }
        * { box-sizing: border-box; font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-main); margin: 0; padding: 2rem; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 2rem; }
        .header h1 { margin: 0 0 0.5rem 0; font-weight: 600; background: linear-gradient(to right, #38bdf8, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .header p { color: var(--text-muted); margin: 0; }
        .card { background-color: var(--surface-color); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group.full { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-muted); font-size: 0.875rem; }
        input[type="text"], input[type="datetime-local"], select { width: 100%; padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid var(--border-color); background-color: #0f172a; color: var(--text-main); font-size: 0.875rem; outline: none; transition: border-color 0.2s; }
        input[type="text"]:focus, input[type="datetime-local"]:focus, select:focus { border-color: var(--primary); }
        
        .button-group { display: flex; gap: 0.5rem; margin-top: 1.5rem; flex-wrap: wrap; }
        button { background-color: var(--primary); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-size: 1rem; font-weight: 500; cursor: pointer; transition: background-color 0.2s; }
        button:hover { background-color: var(--primary-hover); }
        button.btn-outline { background-color: transparent; border: 1px solid var(--primary); color: var(--primary); }
        button.btn-outline:hover { background-color: rgba(59, 130, 246, 0.1); }
        
        pre { background-color: #0f172a; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-color); overflow-x: auto; color: #a5b4fc; font-size: 0.875rem; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Cek Daftar Resep BPJS</h1>
            <p>Melihat ketersediaan resep pada sistem BPJS Apotek Online</p>
        </div>

        <div class="card">
            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="kdppk">Kode PPK</label>
                        <input type="text" id="kdppk" name="kdppk" value="<?php echo isset($_POST['kdppk']) ? htmlspecialchars($_POST['kdppk']) : (defined('KODE_PPK') ? KODE_PPK : ''); ?>" placeholder="Contoh: 0112A017" required>
                        <small style="color: var(--text-muted); font-size: 0.75rem;">(Otomatis dari config.php, ubah jika PPK Apotek berbeda)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="KdJnsObat">Jenis Obat (KdJnsObat)</label>
                        <?php $kdjnsobat = $_POST['KdJnsObat'] ?? '0'; ?>
                        <select id="KdJnsObat" name="KdJnsObat">
                            <option value="0" <?php echo $kdjnsobat === '0' ? 'selected' : ''; ?>>0 - Semua/Default</option>
                            <option value="1" <?php echo $kdjnsobat === '1' ? 'selected' : ''; ?>>1 - Obat PRB</option>
                            <option value="2" <?php echo $kdjnsobat === '2' ? 'selected' : ''; ?>>2 - Obat Kronis Blm Stabil</option>
                            <option value="3" <?php echo $kdjnsobat === '3' ? 'selected' : ''; ?>>3 - Obat Kemoterapi</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="JnsTgl">Jenis Tanggal (JnsTgl)</label>
                        <?php $jnstgl = $_POST['JnsTgl'] ?? 'TGLPELSJP'; ?>
                        <select id="JnsTgl" name="JnsTgl">
                            <option value="TGLPELSJP" <?php echo $jnstgl === 'TGLPELSJP' ? 'selected' : ''; ?>>TGLPELSJP (Tgl Pelayanan)</option>
                            <option value="TGLRSP" <?php echo $jnstgl === 'TGLRSP' ? 'selected' : ''; ?>>TGLRSP (Tgl Resep)</option>
                        </select>
                    </div>

                    <div class="form-group"></div> <!-- Spacer -->

                    <div class="form-group">
                        <label for="TglMulai">Tanggal Mulai (TglMulai)</label>
                        <input type="text" id="TglMulai" name="TglMulai" value="<?php echo isset($_POST['TglMulai']) ? htmlspecialchars($_POST['TglMulai']) : date('Y-m-d 00:00:00'); ?>" placeholder="YYYY-MM-DD HH:MM:SS" required>
                    </div>

                    <div class="form-group">
                        <label for="TglAkhir">Tanggal Akhir (TglAkhir)</label>
                        <input type="text" id="TglAkhir" name="TglAkhir" value="<?php echo isset($_POST['TglAkhir']) ? htmlspecialchars($_POST['TglAkhir']) : date('Y-m-d 23:59:59'); ?>" placeholder="YYYY-MM-DD HH:MM:SS" required>
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="submit">Cari Daftar Resep</button>
                    <button type="button" class="btn-outline" onclick="window.location.href='index.php'">Kembali</button>
                </div>
            </form>
        </div>

        <?php if ($result !== null || $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div class="card">
            <h3>Response BPJS</h3>
            <?php if ($result === null): ?>
                <div style="color: var(--danger); font-weight: bold;">Error: Tidak ada response JSON yang valid dari server BPJS. Kemungkinan koneksi ditolak atau format tidak sesuai.</div>
            <?php else: ?>
                <pre><?php echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
            <?php endif; ?>
            
            <h4 style="margin-top: 1rem; color: var(--text-muted);">Payload Terkirim:</h4>
            <pre style="font-size: 0.75rem; color: #64748b;"><?php echo htmlspecialchars(json_encode($requestData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
