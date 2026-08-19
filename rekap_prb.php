<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/BpjsClient.php';

$bpjs = new BpjsClient();
$result = null;
$tahun = date('Y');
$bulan = date('m');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tahun = $_POST['tahun'] ?? date('Y');
    $bulan = $_POST['bulan'] ?? date('m');
    
    // Pastikan format bulan 2 digit (contoh: 01, 02)
    $bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);
    
    // Endpoint: Prb/rekappeserta/tahun/{parameter 1}/bulan/{parameter 2}
    $endpoint = "Prb/rekappeserta/tahun/" . urlencode($tahun) . "/bulan/" . urlencode($bulan);
    
    $result = $bpjs->request($endpoint, 'GET');
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Peserta PRB - BPJS Apotek Online</title>
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
        
        .form-row { display: flex; gap: 1rem; margin-bottom: 1rem; }
        .form-group { flex: 1; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-muted); font-size: 0.9rem; }
        input[type="number"], select { 
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
        input[type="number"]:focus, select:focus { border-color: var(--primary); }
        
        button { background-color: var(--primary); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-size: 1rem; font-weight: 500; cursor: pointer; transition: background-color 0.2s; width: 100%; }
        button:hover { background-color: var(--primary-hover); }
        
        pre { background-color: #0f172a; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-color); overflow-x: auto; color: #a5b4fc; font-size: 0.875rem; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-links">
            &larr; <a href="index.php">Kembali ke Dashboard Utama</a>
        </div>

        <div class="header">
            <h1>Rekap Peserta PRB</h1>
            <p>Data rekap peserta Program Rujuk Balik (PRB) yang baru diresepkan</p>
        </div>

        <div class="card">
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="tahun">Tahun</label>
                        <input type="number" id="tahun" name="tahun" value="<?php echo htmlspecialchars($tahun); ?>" min="2000" max="2100" required>
                    </div>
                    <div class="form-group">
                        <label for="bulan">Bulan</label>
                        <select id="bulan" name="bulan" required>
                            <?php for ($i = 1; $i <= 12; $i++): 
                                $b = str_pad($i, 2, '0', STR_PAD_LEFT);
                                $selected = ($bulan == $b) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $b; ?>" <?php echo $selected; ?>><?php echo $b; ?> - <?php echo date('F', mktime(0, 0, 0, $i, 10)); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <button type="submit">Tarik Data Rekap PRB</button>
            </form>
        </div>

        <?php if ($result !== null): ?>
        <div class="card">
            <h3>Hasil Response BPJS</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: -10px;">Endpoint: <code><?php echo htmlspecialchars($endpoint); ?></code></p>
            <pre><?php echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
