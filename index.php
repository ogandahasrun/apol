<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/BpjsClient.php';

$bpjs = new BpjsClient();
$result = null;
$endpoint = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $endpoint = $_POST['endpoint'] ?? '';
    if (!empty($endpoint)) {
        $result = $bpjs->request($endpoint, 'GET');
    }
}

// Cek koneksi db
$db_status = "Gagal";
$db_class = "error";
if (isset($koneksi) && !$koneksi->connect_errno) {
    $db_status = "Terhubung (MySQLi)";
    $db_class = "success";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bridging BPJS Apotek Online - SIMRS Khanza</title>
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
        .status-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 500; }
        .status-badge.success { background-color: rgba(16, 185, 129, 0.1); color: var(--success); border: 1px solid var(--success); }
        .status-badge.error { background-color: rgba(239, 68, 68, 0.1); color: var(--danger); border: 1px solid var(--danger); }
        
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-muted); }
        input[type="text"] { width: 100%; padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid var(--border-color); background-color: #0f172a; color: var(--text-main); font-size: 1rem; outline: none; transition: border-color 0.2s; }
        input[type="text"]:focus { border-color: var(--primary); }
        
        .button-group { display: flex; gap: 0.5rem; margin-bottom: 1rem; flex-wrap: wrap; }
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
            <h1>Bridging BPJS Apotek Online</h1>
            <p>Dashboard Pengujian API SIMRS Khanza</p>
        </div>

        <div class="card">
            <h3>Status Sistem</h3>
            <p>Koneksi Database SIMRS: <span class="status-badge <?php echo $db_class; ?>"><?php echo $db_status; ?></span></p>
            <p>Konfigurasi BPJS: <span class="status-badge success">Termuat (<?php echo BPJS_CONS_ID; ?>)</span></p>
        </div>

        <div class="card">
            <h3>Test Request (GET)</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="endpoint">Endpoint API BPJS (tanpa base url)</label>
                    <input type="text" id="endpoint" name="endpoint" value="<?php echo htmlspecialchars($endpoint); ?>" placeholder="Contoh: referensi/dpho" required>
                </div>
                
                <div class="button-group">
                    <button type="submit">Kirim Request</button>
                    <button type="button" class="btn-outline" onclick="setEndpoint('referensi/dpho')">Test Referensi Obat (DPHO)</button>
                    <button type="button" class="btn-outline" onclick="setEndpoint('referensi/poli')">Test Referensi Poli</button>
                    <button type="button" class="btn-outline" style="border-color: var(--success); color: var(--success);" onclick="window.location.href='rekap_prb.php'">Ke Menu Rekap PRB &rarr;</button>
                </div>
            </form>
        </div>

        <?php if ($result !== null): ?>
        <div class="card">
            <h3>Response Hasil Dekripsi</h3>
            <pre><?php echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function setEndpoint(val) {
            document.getElementById('endpoint').value = val;
            document.forms[0].submit();
        }
    </script>
</body>
</html>
