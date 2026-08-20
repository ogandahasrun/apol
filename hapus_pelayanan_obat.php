<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/BpjsClient.php';

$bpjs = new BpjsClient();
$result = null;

$nosepapotek = '';
$noresep = '';
$kodeobat = '';
$tipeobat = 'N';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nosepapotek = $_POST['nosepapotek'] ?? '';
    $noresep = $_POST['noresep'] ?? '';
    $kodeobat = $_POST['kodeobat'] ?? '';
    $tipeobat = $_POST['tipeobat'] ?? 'N';
    
    // Endpoint Hapus Pelayanan Obat
    $endpoint = "pelayanan/obat/hapus/";
    
    // Format JSON sesuai request
    $payload = [
        "request" => [
            "t_obat" => [
                "nosepapotek" => $nosepapotek,
                "noresep" => $noresep,
                "kodeobat" => $kodeobat,
                "tipeobat" => $tipeobat
            ]
        ]
    ];
    
    // Melakukan request DELETE dengan content-type Application/x-www-form-urlencoded
    // Karena payload dari API adalah JSON tetapi content-type disyaratkan urlencoded
    $result = $bpjs->request($endpoint, 'DELETE', $payload, 'application/x-www-form-urlencoded');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Pelayanan Obat - BPJS Apotek Online</title>
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
        
        .nav-links { margin-bottom: 2rem; text-align: center; }
        .nav-links a { color: var(--primary); text-decoration: none; font-weight: 500; }
        .nav-links a:hover { text-decoration: underline; }

        .card { background-color: var(--surface-color); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        
        .form-row { display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; }
        .form-group { flex: 1; min-width: 200px; margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-muted); font-size: 0.9rem; }
        input[type="text"] { 
            width: 100%; 
            padding: 0.75rem 1rem; 
            border-radius: 8px; 
            border: 1px solid var(--border-color); 
            background-color: #0f172a; 
            color: var(--text-main); 
            font-size: 1rem; 
            outline: none; 
            transition: border-color 0.2s; 
        }
        input[type="text"]:focus { border-color: var(--primary); }
        
        button { background-color: var(--danger); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-size: 1rem; font-weight: 500; cursor: pointer; transition: background-color 0.2s; width: 100%; }
        button:hover { background-color: #dc2626; }
        
        pre { background-color: #0f172a; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-color); overflow-x: auto; color: #a5b4fc; font-size: 0.875rem; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-links">
            &larr; <a href="index.php">Kembali ke Dashboard Utama</a>
        </div>

        <div class="header">
            <h1>Hapus Pelayanan Obat</h1>
            <p>Hapus data resep obat pada sistem BPJS Apotek Online</p>
        </div>

        <div class="card">
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nosepapotek">No SEP Apotek</label>
                        <input type="text" id="nosepapotek" name="nosepapotek" value="<?php echo htmlspecialchars($nosepapotek); ?>" placeholder="Contoh: 1801A00104190000001" required>
                    </div>
                    <div class="form-group">
                        <label for="noresep">No Resep</label>
                        <input type="text" id="noresep" name="noresep" value="<?php echo htmlspecialchars($noresep); ?>" placeholder="Contoh: 12345" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="kodeobat">Kode Obat</label>
                        <input type="text" id="kodeobat" name="kodeobat" value="<?php echo htmlspecialchars($kodeobat); ?>" placeholder="Contoh: 25180404057" required>
                    </div>
                    <div class="form-group">
                        <label for="tipeobat">Tipe Obat</label>
                        <input type="text" id="tipeobat" name="tipeobat" value="<?php echo htmlspecialchars($tipeobat); ?>" placeholder="N / PRB / K" required>
                    </div>
                </div>
                
                <button type="submit">Hapus Pelayanan Obat</button>
            </form>
        </div>

        <?php if ($result !== null): ?>
        <div class="card">
            <h3>Hasil Response BPJS</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: -10px;">Endpoint: <code><?php echo htmlspecialchars($endpoint); ?></code></p>
            <pre><?php echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
            
            <h4 style="margin-top: 1rem; color: var(--text-muted);">Payload Terkirim:</h4>
            <pre style="font-size: 0.75rem; color: #64748b;"><?php echo htmlspecialchars(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
