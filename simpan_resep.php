<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/BpjsClient.php';

$bpjs = new BpjsClient();
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $endpoint = 'sjpresep/v3/insert';
    
    // Siapkan data dari form dengan trim untuk mencegah spasi tersembunyi
    $requestData = [
        "TGLSJP" => trim($_POST['TGLSJP'] ?? ''),
        "REFASALSJP" => trim($_POST['REFASALSJP'] ?? ''),
        "POLIRSP" => trim($_POST['POLIRSP'] ?? ''),
        "KDJNSOBAT" => trim($_POST['KDJNSOBAT'] ?? ''),
        "NORESEP" => trim($_POST['NORESEP'] ?? ''),
        "IDUSERSJP" => trim($_POST['IDUSERSJP'] ?? ''),
        "TGLRSP" => trim($_POST['TGLRSP'] ?? ''),
        "TGLPELRSP" => trim($_POST['TGLPELRSP'] ?? ''),
        "KdDokter" => trim($_POST['KdDokter'] ?? '0'),
        "iterasi" => trim($_POST['iterasi'] ?? '0')
    ];

    // Karena BPJS V3 WAF/Gateway seringkali block request selain urlencoded, kita kembalikan
    // ke format x-www-form-urlencoded. Data array otomatis di-encode ke JSON oleh BpjsClient.
    $result = $bpjs->request($endpoint, 'POST', $requestData, 'application/x-www-form-urlencoded');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simpan Resep - Bridging BPJS Apotek Online</title>
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
            <h1>Simpan Resep BPJS</h1>
            <p>Bridging SIMRS Khanza - Apotek Online</p>
        </div>

        <div class="card">
            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="TGLSJP">TGLSJP</label>
                        <input type="text" id="TGLSJP" name="TGLSJP" value="<?php echo isset($_POST['TGLSJP']) ? htmlspecialchars($_POST['TGLSJP']) : date('Y-m-d H:i:s'); ?>" placeholder="YYYY-MM-DD HH:MM:SS" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="REFASALSJP">REFASALSJP</label>
                        <input type="text" id="REFASALSJP" name="REFASALSJP" value="<?php echo isset($_POST['REFASALSJP']) ? htmlspecialchars($_POST['REFASALSJP']) : ''; ?>" placeholder="Contoh: 1202R0010318V000092" required>
                    </div>

                    <div class="form-group">
                        <label for="POLIRSP">POLIRSP</label>
                        <input type="text" id="POLIRSP" name="POLIRSP" value="<?php echo isset($_POST['POLIRSP']) ? htmlspecialchars($_POST['POLIRSP']) : 'IPD'; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="KDJNSOBAT">KDJNSOBAT</label>
                        <?php $kdjnsobat = $_POST['KDJNSOBAT'] ?? '1'; ?>
                        <select id="KDJNSOBAT" name="KDJNSOBAT">
                            <option value="1" <?php echo $kdjnsobat === '1' ? 'selected' : ''; ?>>1 - Obat PRB</option>
                            <option value="2" <?php echo $kdjnsobat === '2' ? 'selected' : ''; ?>>2 - Obat Kronis Blm Stabil</option>
                            <option value="3" <?php echo $kdjnsobat === '3' ? 'selected' : ''; ?>>3 - Obat Kemoterapi</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="NORESEP">NORESEP</label>
                        <input type="text" id="NORESEP" name="NORESEP" value="<?php echo isset($_POST['NORESEP']) ? htmlspecialchars($_POST['NORESEP']) : ''; ?>" placeholder="Contoh: 12346" required>
                    </div>

                    <div class="form-group">
                        <label for="IDUSERSJP">IDUSERSJP</label>
                        <input type="text" id="IDUSERSJP" name="IDUSERSJP" value="<?php echo isset($_POST['IDUSERSJP']) ? htmlspecialchars($_POST['IDUSERSJP']) : 'USR-01'; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="TGLRSP">TGLRSP</label>
                        <input type="text" id="TGLRSP" name="TGLRSP" value="<?php echo isset($_POST['TGLRSP']) ? htmlspecialchars($_POST['TGLRSP']) : date('Y-m-d 00:00:00'); ?>" placeholder="YYYY-MM-DD HH:MM:SS" required>
                    </div>

                    <div class="form-group">
                        <label for="TGLPELRSP">TGLPELRSP</label>
                        <input type="text" id="TGLPELRSP" name="TGLPELRSP" value="<?php echo isset($_POST['TGLPELRSP']) ? htmlspecialchars($_POST['TGLPELRSP']) : date('Y-m-d 00:00:00'); ?>" placeholder="YYYY-MM-DD HH:MM:SS" required>
                    </div>

                    <div class="form-group">
                        <label for="KdDokter">KdDokter</label>
                        <input type="text" id="KdDokter" name="KdDokter" value="<?php echo isset($_POST['KdDokter']) ? htmlspecialchars($_POST['KdDokter']) : '0'; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="iterasi">iterasi</label>
                        <?php $iterasi = $_POST['iterasi'] ?? '0'; ?>
                        <select id="iterasi" name="iterasi">
                            <option value="0" <?php echo $iterasi === '0' ? 'selected' : ''; ?>>0 - Non Iterasi</option>
                            <option value="1" <?php echo $iterasi === '1' ? 'selected' : ''; ?>>1 - Iterasi</option>
                        </select>
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="submit">Simpan Resep</button>
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

    <script>
        // Otomatis menyamakan TGLPELRSP dengan TGLRSP saat TGLRSP diubah,
        // sesuai aturan BPJS: Untuk Iterasi KRONIS, TGLPELRSP harus sama dengan TGLRSP.
        document.getElementById('TGLRSP').addEventListener('input', function() {
            document.getElementById('TGLPELRSP').value = this.value;
        });
    </script>
</body>
</html>
