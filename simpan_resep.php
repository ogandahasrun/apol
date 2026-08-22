<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/BpjsClient.php';

$bpjs = new BpjsClient();
$resultResep = null;
$resultsObatNonRacikan = [];
$resultsObatRacikan = [];
$nosjp = '';
$resepSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Simpan Resep (sjpresep/v3/insert)
    $endpointResep = 'sjpresep/v3/insert';
    $requestResep = [
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

    $resultResep = $bpjs->request($endpointResep, 'POST', $requestResep, 'application/x-www-form-urlencoded');

    // Ekstrak NOSJP dari response BPJS
    if (is_array($resultResep) && isset($resultResep['response'])) {
        if (is_array($resultResep['response'])) {
            $nosjp = $resultResep['response']['NOSJP'] 
                  ?? $resultResep['response']['nosjp'] 
                  ?? $resultResep['response']['nosjpApotek'] 
                  ?? $resultResep['response']['noSjp'] 
                  ?? '';
        } elseif (is_string($resultResep['response']) && !empty($resultResep['response'])) {
            $nosjp = $resultResep['response'];
        }
    }

    // Jika response metaData code = 200 atau NOSJP didapat
    $metaCode = $resultResep['metaData']['code'] ?? null;
    if ($metaCode == 200 || $metaCode === '200' || !empty($nosjp)) {
        $resepSuccess = true;
    }

    // Fallback jika pengguna mengisi NOSJP secara manual untuk pengujian
    if (empty($nosjp) && !empty($_POST['MANUAL_NOSJP'])) {
        $nosjp = trim($_POST['MANUAL_NOSJP']);
    }

    $noresep = trim($_POST['NORESEP'] ?? '');

    // 2. Simpan Obat Non Racikan (obatnonracikan/v3/insert)
    if (!empty($nosjp) && isset($_POST['obat_nonracikan']) && is_array($_POST['obat_nonracikan'])) {
        $endpointNonRacikan = 'obatnonracikan/v3/insert';
        foreach ($_POST['obat_nonracikan'] as $item) {
            $kdobt = trim($item['KDOBT'] ?? '');
            if (empty($kdobt)) continue; // Abaikan baris kosong

            $payloadNonRacikan = [
                "NOSJP" => $nosjp,
                "NORESEP" => $noresep,
                "KDOBT" => $kdobt,
                "NMOBAT" => trim($item['NMOBAT'] ?? ''),
                "SIGNA1OBT" => (int)($item['SIGNA1OBT'] ?? 1),
                "SIGNA2OBT" => (int)($item['SIGNA2OBT'] ?? 1),
                "JMLOBT" => (int)($item['JMLOBT'] ?? 1),
                "JHO" => (int)($item['JHO'] ?? 1),
                "CatKhsObt" => trim($item['CatKhsObt'] ?? '')
            ];

            $res = $bpjs->request($endpointNonRacikan, 'POST', $payloadNonRacikan, 'application/x-www-form-urlencoded');
            $resultsObatNonRacikan[] = [
                'payload' => $payloadNonRacikan,
                'result' => $res
            ];
        }
    }

    // 3. Simpan Obat Racikan (obatracikan/v3/insert)
    if (!empty($nosjp) && isset($_POST['obat_racikan']) && is_array($_POST['obat_racikan'])) {
        $endpointRacikan = 'obatracikan/v3/insert';
        foreach ($_POST['obat_racikan'] as $item) {
            $kdobt = trim($item['KDOBT'] ?? '');
            if (empty($kdobt)) continue; // Abaikan baris kosong

            $payloadRacikan = [
                "NOSJP" => $nosjp,
                "NORESEP" => $noresep,
                "JNSROBT" => trim($item['JNSROBT'] ?? 'R.01'),
                "KDOBT" => $kdobt,
                "NMOBAT" => trim($item['NMOBAT'] ?? ''),
                "SIGNA1OBT" => (int)($item['SIGNA1OBT'] ?? 1),
                "SIGNA2OBT" => (int)($item['SIGNA2OBT'] ?? 1),
                "PERMINTAAN" => (int)($item['PERMINTAAN'] ?? 1),
                "JMLOBT" => (int)($item['JMLOBT'] ?? 1),
                "JHO" => (int)($item['JHO'] ?? 1),
                "CatKhsObt" => trim($item['CatKhsObt'] ?? '')
            ];

            $res = $bpjs->request($endpointRacikan, 'POST', $payloadRacikan, 'application/x-www-form-urlencoded');
            $resultsObatRacikan[] = [
                'payload' => $payloadRacikan,
                'result' => $res
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simpan Resep & Obat - Bridging BPJS Apotek Online</title>
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
            --warning: #f59e0b;
        }
        * { box-sizing: border-box; font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-main); margin: 0; padding: 2rem; }
        .container { max-width: 1100px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 2rem; }
        .header h1 { margin: 0 0 0.5rem 0; font-weight: 600; background: linear-gradient(to right, #38bdf8, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .header p { color: var(--text-muted); margin: 0; }
        .card { background-color: var(--surface-color); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .section-title { font-size: 1.15rem; font-weight: 600; color: #38bdf8; margin-top: 0; margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center; }
        
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group.full { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 0.35rem; font-weight: 500; color: var(--text-muted); font-size: 0.85rem; }
        input[type="text"], input[type="number"], select { width: 100%; padding: 0.6rem 0.8rem; border-radius: 8px; border: 1px solid var(--border-color); background-color: #0f172a; color: var(--text-main); font-size: 0.875rem; outline: none; transition: border-color 0.2s; }
        input[type="text"]:focus, input[type="number"]:focus, select:focus { border-color: var(--primary); }
        
        .table-responsive { overflow-x: auto; margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; font-size: 0.85rem; text-align: left; }
        th { background-color: #0f172a; color: var(--text-muted); padding: 0.6rem 0.75rem; border: 1px solid var(--border-color); font-weight: 600; }
        td { padding: 0.5rem 0.75rem; border: 1px solid var(--border-color); vertical-align: middle; }
        
        .button-group { display: flex; gap: 0.5rem; margin-top: 1.5rem; flex-wrap: wrap; }
        button { background-color: var(--primary); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-size: 1rem; font-weight: 500; cursor: pointer; transition: background-color 0.2s; }
        button:hover { background-color: var(--primary-hover); }
        button.btn-outline { background-color: transparent; border: 1px solid var(--primary); color: var(--primary); }
        button.btn-outline:hover { background-color: rgba(59, 130, 246, 0.1); }
        button.btn-sm { padding: 0.35rem 0.75rem; font-size: 0.8rem; border-radius: 6px; }
        button.btn-danger { background-color: var(--danger); color: white; }
        button.btn-danger:hover { background-color: #dc2626; }
        button.btn-success { background-color: var(--success); color: white; }
        button.btn-success:hover { background-color: #059669; }
        
        .badge { display: inline-block; padding: 0.25rem 0.6rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
        .badge-success { background-color: rgba(16, 185, 129, 0.2); color: var(--success); border: 1px solid var(--success); }
        .badge-warning { background-color: rgba(245, 158, 11, 0.2); color: var(--warning); border: 1px solid var(--warning); }
        .badge-danger { background-color: rgba(239, 68, 68, 0.2); color: var(--danger); border: 1px solid var(--danger); }
        
        pre { background-color: #0f172a; padding: 1.25rem; border-radius: 8px; border: 1px solid var(--border-color); overflow-x: auto; color: #a5b4fc; font-size: 0.85rem; line-height: 1.4; margin-top: 0.5rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Simpan Resep & Obat BPJS</h1>
            <p>Bridging SIMRS Khanza - Apotek Online (v3)</p>
        </div>

        <form method="POST" action="">
            <!-- SECTION 1: DATA RESEP -->
            <div class="card">
                <div class="section-title">
                    <span>1. Data Resep Utama (sjpresep/v3/insert)</span>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="TGLSJP">TGLSJP</label>
                        <input type="text" id="TGLSJP" name="TGLSJP" value="<?php echo isset($_POST['TGLSJP']) ? htmlspecialchars($_POST['TGLSJP']) : date('Y-m-d H:i:s'); ?>" placeholder="YYYY-MM-DD HH:MM:SS" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="REFASALSJP">REFASALSJP (No. SEP / Rujukan)</label>
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
                        <input type="text" id="NORESEP" name="NORESEP" value="<?php echo isset($_POST['NORESEP']) ? htmlspecialchars($_POST['NORESEP']) : '01236'; ?>" placeholder="Contoh: 01236" required>
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

                    <div class="form-group">
                        <label for="MANUAL_NOSJP">Fallback Manual NOSJP (Opsional)</label>
                        <input type="text" id="MANUAL_NOSJP" name="MANUAL_NOSJP" value="<?php echo isset($_POST['MANUAL_NOSJP']) ? htmlspecialchars($_POST['MANUAL_NOSJP']) : ''; ?>" placeholder="Gunakan jika ingin uji coba input obat tanpa resep baru">
                    </div>
                </div>
            </div>

            <!-- SECTION 2: OBAT NON-RACIKAN -->
            <div class="card">
                <div class="section-title">
                    <span>2. Daftar Obat Non-Racikan (obatnonracikan/v3/insert)</span>
                    <button type="button" class="btn-sm btn-success" onclick="addNonRacikanRow()">+ Tambah Baris Obat</button>
                </div>
                <div class="table-responsive">
                    <table id="table-nonracikan">
                        <thead>
                            <tr>
                                <th style="width: 15%;">KDOBT</th>
                                <th style="width: 25%;">NMOBAT</th>
                                <th style="width: 10%;">SIGNA 1</th>
                                <th style="width: 10%;">SIGNA 2</th>
                                <th style="width: 10%;">JML OBAT</th>
                                <th style="width: 10%;">JHO</th>
                                <th style="width: 15%;">CatKhsObt</th>
                                <th style="width: 5%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $nonracikanData = $_POST['obat_nonracikan'] ?? [
                                [
                                    "KDOBT" => "123456",
                                    "NMOBAT" => "IVAN",
                                    "SIGNA1OBT" => 1,
                                    "SIGNA2OBT" => 1,
                                    "JMLOBT" => 1,
                                    "JHO" => 1,
                                    "CatKhsObt" => "TES"
                                ]
                            ];
                            foreach ($nonracikanData as $index => $row):
                            ?>
                            <tr>
                                <td><input type="text" name="obat_nonracikan[<?php echo $index; ?>][KDOBT]" value="<?php echo htmlspecialchars($row['KDOBT'] ?? ''); ?>" placeholder="Kode Obat"></td>
                                <td><input type="text" name="obat_nonracikan[<?php echo $index; ?>][NMOBAT]" value="<?php echo htmlspecialchars($row['NMOBAT'] ?? ''); ?>" placeholder="Nama Obat"></td>
                                <td><input type="number" name="obat_nonracikan[<?php echo $index; ?>][SIGNA1OBT]" value="<?php echo htmlspecialchars($row['SIGNA1OBT'] ?? 1); ?>"></td>
                                <td><input type="number" name="obat_nonracikan[<?php echo $index; ?>][SIGNA2OBT]" value="<?php echo htmlspecialchars($row['SIGNA2OBT'] ?? 1); ?>"></td>
                                <td><input type="number" name="obat_nonracikan[<?php echo $index; ?>][JMLOBT]" value="<?php echo htmlspecialchars($row['JMLOBT'] ?? 1); ?>"></td>
                                <td><input type="number" name="obat_nonracikan[<?php echo $index; ?>][JHO]" value="<?php echo htmlspecialchars($row['JHO'] ?? 1); ?>"></td>
                                <td><input type="text" name="obat_nonracikan[<?php echo $index; ?>][CatKhsObt]" value="<?php echo htmlspecialchars($row['CatKhsObt'] ?? ''); ?>" placeholder="Catatan"></td>
                                <td><button type="button" class="btn-sm btn-danger" onclick="removeRow(this)">Hapus</button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SECTION 3: OBAT RACIKAN -->
            <div class="card">
                <div class="section-title">
                    <span>3. Daftar Obat Racikan (obatracikan/v3/insert)</span>
                    <button type="button" class="btn-sm btn-success" onclick="addRacikanRow()">+ Tambah Baris Racikan</button>
                </div>
                <div class="table-responsive">
                    <table id="table-racikan">
                        <thead>
                            <tr>
                                <th style="width: 10%;">JNSROBT</th>
                                <th style="width: 12%;">KDOBT</th>
                                <th style="width: 20%;">NMOBAT</th>
                                <th style="width: 8%;">SIGNA1</th>
                                <th style="width: 8%;">SIGNA2</th>
                                <th style="width: 10%;">PERMINTAAN</th>
                                <th style="width: 8%;">JMLOBT</th>
                                <th style="width: 8%;">JHO</th>
                                <th style="width: 11%;">CatKhsObt</th>
                                <th style="width: 5%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $racikanData = $_POST['obat_racikan'] ?? [
                                [
                                    "JNSROBT" => "R.01",
                                    "KDOBT" => "012131",
                                    "NMOBAT" => "OBAT SOA 1",
                                    "SIGNA1OBT" => 1,
                                    "SIGNA2OBT" => 1,
                                    "PERMINTAAN" => 1,
                                    "JMLOBT" => 1,
                                    "JHO" => 1,
                                    "CatKhsObt" => "RACIKAN 1"
                                ]
                            ];
                            foreach ($racikanData as $index => $row):
                            ?>
                            <tr>
                                <td><input type="text" name="obat_racikan[<?php echo $index; ?>][JNSROBT]" value="<?php echo htmlspecialchars($row['JNSROBT'] ?? 'R.01'); ?>" placeholder="R.01"></td>
                                <td><input type="text" name="obat_racikan[<?php echo $index; ?>][KDOBT]" value="<?php echo htmlspecialchars($row['KDOBT'] ?? ''); ?>" placeholder="Kode Obat"></td>
                                <td><input type="text" name="obat_racikan[<?php echo $index; ?>][NMOBAT]" value="<?php echo htmlspecialchars($row['NMOBAT'] ?? ''); ?>" placeholder="Nama Obat"></td>
                                <td><input type="number" name="obat_racikan[<?php echo $index; ?>][SIGNA1OBT]" value="<?php echo htmlspecialchars($row['SIGNA1OBT'] ?? 1); ?>"></td>
                                <td><input type="number" name="obat_racikan[<?php echo $index; ?>][SIGNA2OBT]" value="<?php echo htmlspecialchars($row['SIGNA2OBT'] ?? 1); ?>"></td>
                                <td><input type="number" name="obat_racikan[<?php echo $index; ?>][PERMINTAAN]" value="<?php echo htmlspecialchars($row['PERMINTAAN'] ?? 1); ?>"></td>
                                <td><input type="number" name="obat_racikan[<?php echo $index; ?>][JMLOBT]" value="<?php echo htmlspecialchars($row['JMLOBT'] ?? 1); ?>"></td>
                                <td><input type="number" name="obat_racikan[<?php echo $index; ?>][JHO]" value="<?php echo htmlspecialchars($row['JHO'] ?? 1); ?>"></td>
                                <td><input type="text" name="obat_racikan[<?php echo $index; ?>][CatKhsObt]" value="<?php echo htmlspecialchars($row['CatKhsObt'] ?? ''); ?>" placeholder="Catatan"></td>
                                <td><button type="button" class="btn-sm btn-danger" onclick="removeRow(this)">Hapus</button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- BUTTON GROUP -->
            <div class="card button-group">
                <button type="submit">Simpan Resep & Seluruh Obat</button>
                <button type="button" class="btn-outline" onclick="window.location.href='index.php'">Kembali ke Dashboard</button>
            </div>
        </form>

        <!-- SECTION RESPONSE -->
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div class="card">
            <h2>Hasil Response Processing BPJS</h2>
            
            <!-- RESULT RESEP -->
            <div style="margin-bottom: 1.5rem;">
                <h3>
                    1. Response Simpan Resep (sjpresep/v3/insert)
                    <?php if (!empty($nosjp)): ?>
                        <span class="badge badge-success">NOSJP: <?php echo htmlspecialchars($nosjp); ?></span>
                    <?php else: ?>
                        <span class="badge badge-danger">NOSJP Tidak Ditemukan</span>
                    <?php endif; ?>
                </h3>
                <?php if ($resultResep === null): ?>
                    <div style="color: var(--danger); font-weight: bold;">Error: Tidak ada response dari server BPJS.</div>
                <?php else: ?>
                    <pre><?php echo htmlspecialchars(json_encode($resultResep, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
                <?php endif; ?>
                <h4 style="margin-top: 0.5rem; color: var(--text-muted);">Payload Resep Terkirim:</h4>
                <pre style="font-size: 0.75rem; color: #64748b;"><?php echo htmlspecialchars(json_encode($requestResep, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
            </div>

            <!-- RESULT OBAT NON-RACIKAN -->
            <div style="margin-bottom: 1.5rem;">
                <h3>2. Response Simpan Obat Non-Racikan (<?php echo count($resultsObatNonRacikan); ?> item terkirim)</h3>
                <?php if (empty($nosjp)): ?>
                    <div style="color: var(--warning); font-weight: 500;">
                        ⚠️ Pengiriman Obat Non-Racikan dilewati karena <code>NOSJP</code> tidak didapatkan dari response simpan resep.
                    </div>
                <?php elseif (empty($resultsObatNonRacikan)): ?>
                    <div style="color: var(--text-muted);">Tidak ada data obat non-racikan yang diinput atau kode obat kosong.</div>
                <?php else: ?>
                    <?php foreach ($resultsObatNonRacikan as $i => $itemRes): ?>
                        <div style="margin-top: 1rem; border-left: 3px solid var(--primary); padding-left: 1rem;">
                            <strong>Item Non-Racikan #<?php echo ($i + 1); ?> - Obat: <?php echo htmlspecialchars($itemRes['payload']['NMOBAT']); ?> (<?php echo htmlspecialchars($itemRes['payload']['KDOBT']); ?>)</strong>
                            <pre><?php echo htmlspecialchars(json_encode($itemRes['result'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
                            <span style="font-size: 0.75rem; color: #64748b;">Payload Terkirim:</span>
                            <pre style="font-size: 0.75rem; color: #64748b;"><?php echo htmlspecialchars(json_encode($itemRes['payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- RESULT OBAT RACIKAN -->
            <div>
                <h3>3. Response Simpan Obat Racikan (<?php echo count($resultsObatRacikan); ?> item terkirim)</h3>
                <?php if (empty($nosjp)): ?>
                    <div style="color: var(--warning); font-weight: 500;">
                        ⚠️ Pengiriman Obat Racikan dilewati karena <code>NOSJP</code> tidak didapatkan dari response simpan resep.
                    </div>
                <?php elseif (empty($resultsObatRacikan)): ?>
                    <div style="color: var(--text-muted);">Tidak ada data obat racikan yang diinput atau kode obat kosong.</div>
                <?php else: ?>
                    <?php foreach ($resultsObatRacikan as $i => $itemRes): ?>
                        <div style="margin-top: 1rem; border-left: 3px solid var(--warning); padding-left: 1rem;">
                            <strong>Item Racikan #<?php echo ($i + 1); ?> [<?php echo htmlspecialchars($itemRes['payload']['JNSROBT']); ?>] - Obat: <?php echo htmlspecialchars($itemRes['payload']['NMOBAT']); ?> (<?php echo htmlspecialchars($itemRes['payload']['KDOBT']); ?>)</strong>
                            <pre><?php echo htmlspecialchars(json_encode($itemRes['result'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
                            <span style="font-size: 0.75rem; color: #64748b;">Payload Terkirim:</span>
                            <pre style="font-size: 0.75rem; color: #64748b;"><?php echo htmlspecialchars(json_encode($itemRes['payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Sync TGLPELRSP dengan TGLRSP
        document.getElementById('TGLRSP').addEventListener('input', function() {
            document.getElementById('TGLPELRSP').value = this.value;
        });

        // Tambah Baris Obat Non Racikan
        function addNonRacikanRow() {
            const tbody = document.querySelectorAll('#table-nonracikan tbody')[0];
            const index = tbody.children.length;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="text" name="obat_nonracikan[${index}][KDOBT]" placeholder="Kode Obat"></td>
                <td><input type="text" name="obat_nonracikan[${index}][NMOBAT]" placeholder="Nama Obat"></td>
                <td><input type="number" name="obat_nonracikan[${index}][SIGNA1OBT]" value="1"></td>
                <td><input type="number" name="obat_nonracikan[${index}][SIGNA2OBT]" value="1"></td>
                <td><input type="number" name="obat_nonracikan[${index}][JMLOBT]" value="1"></td>
                <td><input type="number" name="obat_nonracikan[${index}][JHO]" value="1"></td>
                <td><input type="text" name="obat_nonracikan[${index}][CatKhsObt]" placeholder="Catatan"></td>
                <td><button type="button" class="btn-sm btn-danger" onclick="removeRow(this)">Hapus</button></td>
            `;
            tbody.appendChild(tr);
        }

        // Tambah Baris Obat Racikan
        function addRacikanRow() {
            const tbody = document.querySelectorAll('#table-racikan tbody')[0];
            const index = tbody.children.length;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="text" name="obat_racikan[${index}][JNSROBT]" value="R.01" placeholder="R.01"></td>
                <td><input type="text" name="obat_racikan[${index}][KDOBT]" placeholder="Kode Obat"></td>
                <td><input type="text" name="obat_racikan[${index}][NMOBAT]" placeholder="Nama Obat"></td>
                <td><input type="number" name="obat_racikan[${index}][SIGNA1OBT]" value="1"></td>
                <td><input type="number" name="obat_racikan[${index}][SIGNA2OBT]" value="1"></td>
                <td><input type="number" name="obat_racikan[${index}][PERMINTAAN]" value="1"></td>
                <td><input type="number" name="obat_racikan[${index}][JMLOBT]" value="1"></td>
                <td><input type="number" name="obat_racikan[${index}][JHO]" value="1"></td>
                <td><input type="text" name="obat_racikan[${index}][CatKhsObt]" placeholder="Catatan"></td>
                <td><button type="button" class="btn-sm btn-danger" onclick="removeRow(this)">Hapus</button></td>
            `;
            tbody.appendChild(tr);
        }

        // Hapus Baris
        function removeRow(btn) {
            const tr = btn.closest('tr');
            if (tr) {
                tr.remove();
            }
        }
    </script>
</body>
</html>
