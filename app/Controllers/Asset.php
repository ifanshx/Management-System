<?php

namespace App\Controllers;

class Asset extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/portal');
        }

        $assets = $this->db->table('factory_assets')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        // =========================================================
        // AUTO GENERATE PREVIEW KODE ASET
        // =========================================================
        $datePrefix = date('Ym');
        $lastAsset = $this->db->table('factory_assets')
            ->like('asset_code', "AST-$datePrefix-", 'after')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        $seq = $lastAsset ? (int) substr($lastAsset['asset_code'], -3) + 1 : 1;
        $autoAssetCode = "AST-" . $datePrefix . "-" . str_pad($seq, 3, '0', STR_PAD_LEFT);

        // =========================================================
        // SUMMARY KPI
        // =========================================================
        $totalAssets = count($assets);
        $totalValue = 0;
        $totalDep = 0;
        $activeCount = 0;
        $maintenanceCount = 0;
        $brokenCount = 0;

        foreach ($assets as $a) {
            $totalValue += (float) ($a['purchase_price'] ?? 0);
            $totalDep += (float) ($a['monthly_depreciation'] ?? 0);

            if (($a['status'] ?? '') === 'ACTIVE') $activeCount++;
            if (($a['status'] ?? '') === 'MAINTENANCE') $maintenanceCount++;
            if (($a['status'] ?? '') === 'BROKEN') $brokenCount++;
        }

        return view('asset/index', [
            'title'             => 'Manajemen Aset Tetap & Depresiasi',
            'assets'            => $assets,
            'autoAssetCode'     => $autoAssetCode,
            'totalAssets'       => $totalAssets,
            'totalValue'        => $totalValue,
            'totalDep'          => $totalDep,
            'activeCount'       => $activeCount,
            'maintenanceCount'  => $maintenanceCount,
            'brokenCount'       => $brokenCount,
        ]);
    }

    public function store()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/portal');
        }

        try {
            // =========================================================
            // VALIDASI INPUT
            // =========================================================
            $rules = [
                'asset_name'         => 'required|min_length[3]|max_length[150]',
                'asset_category'     => 'required|max_length[100]',
                'purchase_date'      => 'required|valid_date',
                'purchase_price'     => 'required',
                'useful_life_months' => 'permit_empty|integer',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
            }

            $this->db->transBegin();

            // =========================================================
            // GENERATE KODE ASET
            // =========================================================
            $datePrefix = date('Ym');
            $lastAsset = $this->db->table('factory_assets')
                ->like('asset_code', "AST-$datePrefix-", 'after')
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();

            $seq = $lastAsset ? (int) substr($lastAsset['asset_code'], -3) + 1 : 1;
            $assetCode = "AST-" . $datePrefix . "-" . str_pad($seq, 3, '0', STR_PAD_LEFT);

            // =========================================================
            // AMBIL INPUT
            // =========================================================
            $assetName     = trim($this->request->getPost('asset_name'));
            $assetCategory = trim($this->request->getPost('asset_category'));
            $purchaseDate  = $this->request->getPost('purchase_date');

            $purchasePriceRaw = $this->request->getPost('purchase_price') ?? '0';
            $purchasePrice = (float) preg_replace('/[^0-9]/', '', $purchasePriceRaw);

            $usefulLife = (int) ($this->request->getPost('useful_life_months') ?? 0);

            if ($purchasePrice <= 0) {
                throw new \Exception("Nilai valuasi / harga beli harus lebih besar dari nol.");
            }

            // =========================================================
            // LOGIKA PENYUSUTAN
            // =========================================================
            $monthlyDepreciation = 0;

            if ($assetCategory === 'Tanah / Lahan') {
                $usefulLife = 0;
                $monthlyDepreciation = 0;
            } else {
                if ($usefulLife <= 0) {
                    throw new \Exception("Umur ekonomis harus diisi minimal 1 bulan untuk aset yang dapat disusutkan.");
                }

                $monthlyDepreciation = $purchasePrice / $usefulLife;
            }

            // =========================================================
            // SIMPAN ASET FISIK
            // =========================================================
            $this->db->table('factory_assets')->insert([
                'asset_code'           => $assetCode,
                'asset_name'           => $assetName,
                'asset_category'       => $assetCategory,
                'purchase_date'        => $purchaseDate,
                'purchase_price'       => $purchasePrice,
                'useful_life_months'   => $usefulLife,
                'monthly_depreciation' => $monthlyDepreciation,
                'status'               => 'ACTIVE',
            ]);

            // =========================================================
            // AUTO JURNAL AKUNTANSI PEMBELIAN ASET
            // =========================================================
            $asetAcc = $this->db->table('chart_of_accounts')->where('account_code', '1-5000')->get()->getRowArray();
            if (!$asetAcc) {
                $this->db->table('chart_of_accounts')->insert([
                    'account_code' => '1-5000',
                    'account_name' => 'Aset Tetap (Mesin, Gedung, Tanah)',
                    'account_type' => 'ASET',
                    'balance'      => 0
                ]);
                $asetAcc = $this->db->table('chart_of_accounts')->where('account_code', '1-5000')->get()->getRowArray();
            }

            $bankAcc = $this->db->table('chart_of_accounts')->where('account_code', '1-2000')->get()->getRowArray();
            if (!$bankAcc) {
                throw new \Exception("Akun Bank (1-2000) tidak ditemukan di Chart of Accounts.");
            }

            if ($asetAcc && $bankAcc) {
                $journalNumber = 'JRN-AST-' . date('YmdHis');

                $this->db->table('journals')->insert([
                    'journal_number'   => $journalNumber,
                    'transaction_date' => $purchaseDate,
                    'description'      => "Akuisisi aset tetap: {$assetName} ({$assetCode})",
                    'total_amount'     => $purchasePrice,
                    'created_by'       => session()->get('name') ?? 'System',
                ]);

                $journalId = $this->db->insertID();

                $this->db->table('journal_items')->insertBatch([
                    [
                        'journal_id' => $journalId,
                        'account_id' => $asetAcc['id'],
                        'debit'      => $purchasePrice,
                        'credit'     => 0
                    ],
                    [
                        'journal_id' => $journalId,
                        'account_id' => $bankAcc['id'],
                        'debit'      => 0,
                        'credit'     => $purchasePrice
                    ]
                ]);

                $this->db->query("UPDATE chart_of_accounts SET balance = balance + ? WHERE id = ?", [$purchasePrice, $asetAcc['id']]);
                $this->db->query("UPDATE chart_of_accounts SET balance = balance - ? WHERE id = ?", [$purchasePrice, $bankAcc['id']]);
            }

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                throw new \Exception("Terjadi kegagalan saat menyimpan data aset dan jurnal akuntansi.");
            }

            $this->db->transCommit();

            $msg = "Aset <b>{$assetName}</b> berhasil diregistrasi. Nilai perolehan <b>Rp " . number_format($purchasePrice, 0, ',', '.') . "</b> telah dicatat ke sistem akuntansi.";

            if ($assetCategory !== 'Tanah / Lahan') {
                $msg .= "<br>Penyusutan bulanan: <b>Rp " . number_format($monthlyDepreciation, 0, ',', '.') . "</b> / bulan.";
            }

            return redirect()->back()->with('success', $msg);

        } catch (\Throwable $e) {
            if ($this->db->transStatus() !== false) {
                $this->db->transRollback();
            }

            return redirect()->back()->withInput()->with('error', 'Gagal mendaftarkan aset: ' . $e->getMessage());
        }
    }

    public function update_status($id)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/portal');
        }

        try {
            $asset = $this->db->table('factory_assets')->where('id', $id)->get()->getRowArray();
            if (!$asset) {
                throw new \Exception("Data aset tidak ditemukan.");
            }

            $status = $this->request->getPost('status');
            $allowedStatus = ['ACTIVE', 'MAINTENANCE', 'BROKEN'];

            if (!in_array($status, $allowedStatus)) {
                throw new \Exception("Status aset tidak valid.");
            }

            $this->db->table('factory_assets')->where('id', $id)->update([
                'status' => $status
            ]);

            $msg = match($status) {
                'MAINTENANCE' => "Aset <b>{$asset['asset_name']}</b> berhasil ditandai dalam status <b>Perawatan</b>.",
                'ACTIVE'      => "Aset <b>{$asset['asset_name']}</b> berhasil dikembalikan ke status <b>Aktif</b>.",
                'BROKEN'      => "Aset <b>{$asset['asset_name']}</b> berhasil ditandai sebagai <b>Tidak Layak Pakai</b>.",
                default       => "Status aset berhasil diperbarui."
            };

            return redirect()->back()->with('success', $msg);

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui status aset: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/portal');
        }

        try {
            $asset = $this->db->table('factory_assets')->where('id', $id)->get()->getRowArray();

            if (!$asset) {
                throw new \Exception("Data aset tidak ditemukan.");
            }

            $this->db->table('factory_assets')->where('id', $id)->delete();

            return redirect()->back()->with(
                'success',
                "Aset <b>{$asset['asset_name']}</b> berhasil dihapus dari inventaris sistem."
            );

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal menghapus aset: ' . $e->getMessage());
        }
    }
}