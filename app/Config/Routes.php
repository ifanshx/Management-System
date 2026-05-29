<?php

use CodeIgniter\Router\RouteCollection;

/**
 * ====================================================================
 * NORIC ENTERPRISE RESOURCE PLANNING (ERP) ROUTING SYSTEM
 * ====================================================================
 * @var RouteCollection $routes
 */

// ====================================================================
// 1. RUTE PUBLIK (LANDING PAGE & AUTENTIKASI)
// ====================================================================
$routes->get('/', 'LandingController::index');
$routes->get('/login', 'AuthController::index');
$routes->post('/login/process', 'AuthController::process');
$routes->get('/logout', 'AuthController::logout');

// ====================================================================
// 2. RUTE GLOBAL (AKSES BERSAMA: HARUS LOGIN)
// Kita buang filter adminAuth/karyawanAuth yang kaku, ganti dengan filter standar (auth)
// Pastikan Anda punya filter 'auth' yang mengecek apakah user sudah login.
// Jika belum ada filter 'auth', hapus ['filter' => 'auth'] di bawah ini.
// ====================================================================
$routes->group('', static function ($routes) {
    
    // --- PORTAL PRIBADI & PENGATURAN AKUN ---
    $routes->get('/profile', 'Profile::index');
    $routes->post('/profile/update_password', 'Profile::update_password');
    $routes->get('/portal', 'Portal::index');
    $routes->get('/portal/absen/(:segment)', 'Portal::record_attendance/$1');
    $routes->get('/portal/slip_gaji', 'Portal::slip_gaji');
    $routes->get('/portal/print_my_slip/(:num)', 'Portal::print_my_slip/$1');
    
    $routes->get('/leave', 'Leave::index');
    $routes->post('/leave/store', 'Leave::store');

// --- EXECUTIVE DASHBOARD & IDENTITAS ---
    $routes->get('/dashboard', 'Dashboard::index');
    $routes->get('/setting/company', 'Setting::company');
    $routes->post('/setting/update_company', 'Setting::update_company');

    // --- PENGATURAN SHIFT KERJA ---
    $routes->get('/setting/workshift_index', 'Setting::workshift_index'); // Menampilkan Form + Tabel
    $routes->post('/setting/workshift_store', 'Setting::workshift_store'); // Menangani Simpan Baru & Update
    $routes->get('/setting/workshift_delete/(:num)', 'Setting::workshift_delete/$1'); // Menangani Hapus Data
    
    // --- RUTE PENGATURAN API FINGERSPOT ---
    $routes->get('/device/settings', 'Device::settings');
    $routes->post('/device/update_settings', 'Device::update_settings');
    
    // Manajemen Katalog Landing Page
    $routes->post('/setting/store_catalog', 'Setting::store_catalog');
    $routes->post('/setting/update_catalog/(:num)', 'Setting::update_catalog/$1');
    $routes->get('/setting/delete_catalog/(:num)', 'Setting::delete_catalog/$1');

    // --- MODUL SDM & KARYAWAN (HRIS) ---
    $routes->get('/employee', 'Employee::index');
    $routes->get('/employee/create', 'Employee::create');
    $routes->post('/employee/store', 'Employee::store');
    $routes->get('/employee/edit/(:num)', 'Employee::edit/$1');
    $routes->post('/employee/update/(:num)', 'Employee::update/$1');
    $routes->post('/employee/delete/(:num)', 'Employee::delete/$1');
    $routes->post('/employee/deactivate/(:num)', 'Employee::deactivate/$1');
    
    // Integrasi Karyawan ke Mesin IoT
    $routes->get('/employee/push_to_machine/(:num)', 'Employee::push_to_machine/$1');
    $routes->get('/employee/sync_biometric/(:segment)', 'Employee::sync_biometric/$1');
    $routes->get('/employee/pull_from_machine', 'Employee::pull_from_machine');

    // --- KONTROL HARDWARE (IOT DEVICE MAINTENANCE) ---
    $routes->get('/device', 'Device::index');
    $routes->get('/device/sync_time', 'Device::sync_time');
    $routes->get('/device/restart', 'Device::restart');
    $routes->get('/device/audit_pins', 'Device::audit_pins');

    // --- MODUL ABSENSI & CUTI ---
    $routes->group('attendance', function($routes) {
        $routes->get('/', 'Attendance::index');
        $routes->get('syncData', 'Attendance::syncData');
        $routes->get('syncMachineTime', 'Attendance::syncMachineTime');
        $routes->get('manual', 'Attendance::manual');
        $routes->post('store_manual', 'Attendance::store_manual');
        $routes->get('delete/(:num)', 'Attendance::delete/$1');
        $routes->get('get_existing_log', 'Attendance::get_existing_log');
        $routes->get('toggle_meal/(:num)', 'Attendance::toggle_meal/$1');
        $routes->post('toggle_overtime_meal', 'Attendance::toggle_overtime_meal');
        $routes->post('store_quick_kasbon', 'Attendance::store_quick_kasbon');
    });
    
    $routes->get('/leave/approval', 'Leave::approval');
    $routes->get('/leave/process_action/(:num)/(:any)', 'Leave::process_action/$1/$2');
    
    // --- MODUL CASH ADVANCE ---
    $routes->get('/cash_advance', 'CashAdvance::index');
    $routes->post('/cash_advance/store', 'CashAdvance::store');
    $routes->get('/cash_advance/delete/(:num)', 'CashAdvance::delete/$1');

    // --- MODUL PENGGAJIAN (PAYROLL ENGINE) ---
    $routes->get('/payroll', 'Payroll::index');
    $routes->post('/payroll/generate', 'Payroll::generate');
    $routes->get('/payroll/detail/(:num)', 'Payroll::detail/$1');
    $routes->get('/payroll/print_slip/(:num)', 'Payroll::print_slip/$1');
    $routes->get('/payroll/delete/(:num)', 'Payroll::delete/$1');
    $routes->post('/payroll/push_to_finance', 'Payroll::push_to_finance');

    // --- ACCOUNTING ---
    $routes->get('/accounting', 'Accounting::index');
    $routes->get('/accounting/journal', 'Accounting::journal');
    $routes->post('/accounting/store_journal', 'Accounting::store_journal');
    $routes->get('/accounting/void_journal/(:num)', 'Accounting::void_journal/$1');
    $routes->get('/accounting/coa', 'Accounting::coa');
    $routes->post('/accounting/store_account', 'Accounting::store_account');
    $routes->get('/accounting/ledger', 'Accounting::ledger');
    $routes->get('/accounting/print-report', 'Accounting::print_report');
    
    // --- FINANCE ---
    $routes->get('/finance/cash_index', 'Finance::cash_index');
    $routes->post('/finance/cash-store', 'Finance::cash_store');
    $routes->get('/finance/cash-delete/(:num)', 'Finance::cash_delete/$1');
    
    // Pastikan kode ini ada di dalam file app/Config/Routes.php
$routes->group('companydebt', function ($routes) {
    $routes->get('/', 'CompanyDebt::index');
    $routes->post('store', 'CompanyDebt::store');
    $routes->post('pay/(:num)', 'CompanyDebt::pay/$1');
    
    // 2 Baris di bawah ini WAJIB ADA untuk Edit dan Hapus
    $routes->post('update/(:num)', 'CompanyDebt::update/$1');
    $routes->get('delete/(:num)', 'CompanyDebt::delete/$1'); 
});
    
    // --- MODUL PENDANAAN & INVESTOR ---
    $routes->get('/investor', 'Investor::index');
    $routes->post('/investor/store_investor', 'Investor::store_investor');
    $routes->post('/investor/store_transaction', 'Investor::store_transaction');
    $routes->get('/investor/void_transaction/(:num)', 'Investor::void_transaction/$1');
    
    // Tambahkan 2 baris wajib ini agar tombol Hapus dan Power berfungsi:
    $routes->get('/investor/delete_investor/(:num)', 'Investor::delete_investor/$1');
    $routes->get('/investor/toggle_status/(:num)', 'Investor::toggle_status/$1');

    // --- MODUL PENJUALAN KASIR (OFFLINE POS) ---
    $routes->get('/sales/offline', 'OfflineSales::index');
    $routes->post('/sales/process_offline', 'OfflineSales::process_offline'); 
    $routes->get('/sales/offline_history', 'OfflineSales::history');
    $routes->get('/sales/get_detail/(:any)', 'OfflineSales::get_detail/$1');

// ====================================================================
    // ROUTES: B2B WHOLESALE (GROSIR & PIUTANG)
    // ====================================================================
    $routes->group('wholesale', function($routes) {
        $routes->get('/', 'Wholesale::index');
        $routes->post('store_so', 'Wholesale::store_so');
        $routes->get('surat_jalan/(:segment)', 'Wholesale::surat_jalan/$1');
        $routes->post('pay_installment/(:segment)', 'Wholesale::pay_installment/$1');
        $routes->post('process_shipment/(:segment)', 'Wholesale::process_shipment/$1');
        $routes->post('return_so/(:segment)', 'Wholesale::return_so/$1');
        $routes->get('delete_so/(:segment)', 'Wholesale::delete_so/$1'); 
        $routes->post('store_customer', 'Wholesale::store_customer');
        $routes->get('get_customer/(:segment)', 'Wholesale::get_customer/$1'); 
        $routes->post('update_customer/(:segment)', 'Wholesale::update_customer/$1'); 
        $routes->get('delete_customer/(:segment)', 'Wholesale::delete_customer/$1'); 
        $routes->get('export_excel/(:segment)', 'Wholesale::export_excel/$1'); 


        // --- RUTE KHUSUS FITUR EDIT & TAMBAH ITEM SO ---
        $routes->get('get_so/(:segment)', 'Wholesale::get_so/$1');
        $routes->post('add_item_to_so', 'Wholesale::add_item_to_so');
        $routes->get('delete_item_from_so/(:segment)', 'Wholesale::delete_item_from_so/$1');
        $routes->post('update_item_qty/(:num)', 'Wholesale::update_item_qty/$1');

        // --- RUTE PENGIRIMAN GABUNGAN ---
        $routes->get('get_pending_by_customer', 'Wholesale::get_pending_by_customer');
        $routes->get('get_pending_by_customer/(:segment)', 'Wholesale::get_pending_by_customer/$1');
        $routes->post('process_shipment_gabungan', 'Wholesale::process_shipment_gabungan');
        $routes->get('print_sj_gabungan/(:any)', 'Wholesale::print_sj_gabungan/$1');
    });
    
    // --- ROUTING MODUL PRODUKSI ---
    $routes->get('production', 'Production::index');
    $routes->get('production/bom_builder', 'Production::bom_builder');
    $routes->post('production/store_bom', 'Production::store_bom');
    
    // Fitur Konfirmasi Setoran (Baru)
    $routes->get('production/confirm_logs', 'Production::confirm_logs');
    $routes->get('production/approve_log/(:num)', 'Production::approve_log/$1');
    $routes->get('production/reject_log/(:num)', 'Production::reject_log/$1');
    $routes->get('production/delete_log/(:num)', 'Production::delete_log/$1');

    
    // Rute Baru untuk SPK Reguler
    $routes->post('production/create_spk', 'Production::create_spk');
    $routes->get('production/get_spk/(:num)', 'Production::get_spk/$1');
    $routes->post('production/update_spk', 'Production::update_spk');
    $routes->get('production/delete_spk/(:num)', 'Production::delete_spk/$1');
    
    $routes->get('production/duplicate_bom      /(:num)', 'Production::duplicate_bom/$1');
    $routes->post('production/mass_copy_operations', 'Production::mass_copy_operations');
    $routes->post('production/mass_copy_bom', 'Production::mass_copy_bom');


    
    $routes->post('production/add_production_log', 'Production::add_production_log');
    $routes->get('production/print_spk/(:num)', 'Production::print_spk/$1');
    $routes->post('production/check_last_wage', 'Production::check_last_wage');
    
    $routes->get('production/mass_update_materials', 'Production::mass_update_materials');
    $routes->get('production/mass_update_routing', 'Production::mass_update_routing');
    
    
    
    $routes->get('production/print_rekap_produksi/(:num)', 'Production::print_rekap_produksi/$1');
    $routes->get('production/print_form_setoran', 'Production::print_form_setoran');
    
    $routes->get('production/get_operations/(:num)', 'Production::get_operations/$1');

    $routes->get('production/get_bom/(:num)', 'Production::get_bom/$1');
    $routes->post('production/update_bom/(:num)', 'Production::update_bom/$1');
    $routes->get('production/delete_bom/(:num)', 'Production::delete_bom/$1');
    
    $routes->get('production/sync_old_po', 'Production::sync_old_po');
    $routes->get('production/print_spk_batch/(:num)', 'Production::print_spk_batch/$1');
    
    $routes->get('production/print_bom/(:segment)', 'Production::print_bom/$1');
    $routes->get('production/print_bom_batch', 'Production::print_bom_batch');
    
    $routes->get('production/print_blank_bom', 'Production::print_blank_bom');
    $routes->get('production/print_blank_bom_batch', 'Production::print_blank_bom_batch');
    
    // --- FITUR BARU: Revisi Upah & Mass Inject Harga Tukang ---
    $routes->post('production/update_log_wage', 'Production::update_log_wage');
    $routes->post('production/mass_update_custom_wage', 'Production::mass_update_custom_wage');
    // Fitur Konfirmasi Setoran (Baru)
    $routes->get('production/confirm_logs', 'Production::confirm_logs');
    $routes->get('production/approve_log/(:num)', 'Production::approve_log/$1'); // <--- INI UNTUK TERIMA (ACC)
    $routes->get('production/reject_log/(:num)', 'Production::reject_log/$1');   // <--- INI UNTUK TOLAK (REJECT)

    // --- FITUR BARU: Terbitkan & Cetak SPK Massal (Batch) ---
    $routes->post('production/batch_create_spk', 'Production::batch_create_spk');
    $routes->get('production/print_spk_selected_batch', 'Production::print_spk_selected_batch');
    $routes->post('production/print_spk_selected', 'Production::print_spk_selected');
    $routes->get('production/print_spk_selected_batch', 'Production::print_spk_selected_batch');

    // --- MODUL PEMBELIAN BARANG (PROCUREMENT) ---
    $routes->get('/procurement', 'Procurement::index');
    $routes->get('/procurement/detail/(:num)', 'Procurement::detail/$1'); 
    $routes->post('/procurement/store_supplier', 'Procurement::store_supplier'); 
    $routes->get('/procurement/create_po', 'Procurement::create_po');
    $routes->post('/procurement/store_po', 'Procurement::store_po');
    
    // Rute untuk Penerimaan dan Void Barang
    $routes->post('/procurement/receive_goods/(:num)', 'Procurement::receive_goods/$1');
    $routes->get('/procurement/void_po/(:num)', 'Procurement::void_po/$1'); 
    $routes->get('/procurement/get_po_items/(:num)', 'Procurement::get_po_items/$1'); 
    
    $routes->get('/procurement/delete_supplier/(:num)', 'Procurement::delete_supplier/$1');
    $routes->get('/procurement/delete_po/(:num)', 'Procurement::delete_po/$1');
    $routes->get('/procurement/get_supplier/(:num)', 'Procurement::get_supplier/$1');
    $routes->post('/procurement/update_supplier/(:num)', 'Procurement::update_supplier/$1');
    $routes->post('/procurement/pay_po/(:num)', 'Procurement::pay_po/$1');

    // --- MANAJEMEN ASET PABRIK ---
    $routes->group('asset', function($routes) {
        $routes->get('/', 'Asset::index');
        $routes->post('store', 'Asset::store');
        $routes->post('update_status/(:num)', 'Asset::update_status/$1');
        $routes->post('delete/(:num)', 'Asset::delete/$1'); 
    });

    // --- MANAJEMEN GUDANG (INVENTORY & WAREHOUSE) ---
    $routes->get('/warehouse/local-inventory', 'LocalWarehouse::index');
    $routes->post('/warehouse/store_fg', 'LocalWarehouse::store_fg');
    $routes->post('/warehouse/store_rm', 'LocalWarehouse::store_rm');
    
    $routes->get('/warehouse/get_fg/(:num)', 'LocalWarehouse::get_fg/$1');
    $routes->post('/warehouse/update_fg/(:num)', 'LocalWarehouse::update_fg/$1');
    $routes->get('/warehouse/get_rm/(:num)', 'LocalWarehouse::get_rm/$1');
    $routes->post('/warehouse/update_rm/(:num)', 'LocalWarehouse::update_rm/$1');

    $routes->get('/warehouse/delete_fg/(:num)', 'LocalWarehouse::delete_fg/$1');
    $routes->get('/warehouse/delete_rm/(:num)', 'LocalWarehouse::delete_rm/$1');
    $routes->post('/warehouse/store_adjustment', 'LocalWarehouse::store_adjustment');
    
    // Pemenuhan Pesanan Shopee
    $routes->get('/warehouse/orders', 'Warehouse::orders');
    $routes->get('/warehouse/ship_shopee_order/(:segment)', 'Warehouse::ship_shopee_order/$1');
    $routes->get('/warehouse/print_shopee_awb/(:segment)', 'Warehouse::print_shopee_awb/$1');
    $routes->get('/warehouse/mass-fulfillment', 'Warehouse::mass_fulfillment');
    $routes->post('/warehouse/process_mass_action', 'Warehouse::process_mass_action');
    
    // Resolusi Pembatalan
    $routes->get('/warehouse/cancellation-hub', 'Warehouse::cancellation_hub');
    $routes->post('/warehouse/process_cancellation', 'Warehouse::process_cancellation');

    // ====================================================================
    // INTEGRASI OMNICHANNEL (SHOPEE OPEN API)
    // ====================================================================
    $routes->get('/omni-dashboard', 'OmniDashboard::index');
    $routes->get('/shopee', 'Shopee::index');
    $routes->get('/shopee/callback', 'Shopee::callback');
    
    // Sinkronisasi Data Shopee
    $routes->get('/shopee/sync_orders/(:segment)', 'Shopee::sync_orders/$1');
    $routes->get('/shopee/sync_products/(:segment)', 'Shopee::sync_products/$1');
    $routes->get('/shopee/sync_finance/(:segment)', 'Shopee::sync_finance/$1');
    $routes->get('/shopee/sync_returns/(:segment)', 'Shopee::sync_returns/$1');
    
    // Operasional Shopee
    $routes->get('/shopee/finances/(:segment)', 'Shopee::finances/$1');
    $routes->get('/shopee/returns/(:segment)', 'Shopee::returns/$1');
    $routes->get('/shopee/confirm_return/(:segment)/(:segment)', 'ShopeeReturn::confirm/$1/$2');
    $routes->post('/shopee/dispute_return/(:segment)', 'ShopeeReturn::dispute/$1');
    
    // Customer Service & Review
    $routes->get('/customerservice/inbox/(:segment)', 'CustomerService::inbox/$1');
    $routes->post('/customerservice/reply_chat', 'CustomerService::reply_chat');
    $routes->get('/shopee/reviews/(:segment)', 'ShopeeReview::index/$1');
    $routes->post('/shopee/reply_review/(:segment)', 'ShopeeReview::reply/$1');

    // Katalog & Update Massal
    $routes->get('/shopee/products/(:segment)', 'Shopee::products/$1'); 
    $routes->get('/shopee/mass_price/(:segment)', 'ShopeeMassUpdate::price/$1');
    $routes->post('/shopee/update_price_action/(:segment)', 'ShopeeMassUpdate::update_price_action/$1');
    $routes->get('/shopee/variation/(:segment)/(:num)', 'ShopeeVariation::build/$1/$2');
    $routes->post('/shopee/save_variation/(:segment)/(:num)', 'ShopeeVariation::save/$1/$2');

    // Marketing & Promosi Shopee
    $routes->get('/marketing/shopee_discount/(:segment)', 'Marketing::shopee_discount/$1');
    $routes->post('/marketing/create_discount/(:segment)', 'Marketing::create_discount/$1');
    
    $routes->get('/shopee/boost/(:segment)', 'ShopeeBoost::index/$1');
    $routes->post('/shopee/boost_action/(:segment)', 'ShopeeBoost::push_boost/$1');
    
    $routes->get('/shopee/voucher/(:segment)', 'ShopeeVoucher::index/$1');
    $routes->post('/shopee/create_voucher/(:segment)', 'ShopeeVoucher::create_voucher/$1');
    $routes->get('/shopee/end_voucher/(:segment)/(:num)', 'ShopeeVoucher::end_voucher/$1/$2');
    
    $routes->get('/shopee/bundle/(:segment)', 'ShopeeBundle::index/$1');
    $routes->post('/shopee/create_bundle/(:segment)', 'ShopeeBundle::create_bundle/$1');
    
    $routes->get('/shopee/addon/(:segment)', 'ShopeeAddon::index/$1');
    $routes->post('/shopee/create_addon/(:segment)', 'ShopeeAddon::create_addon/$1');

    // Route Pelindung
    $routes->get('/shopee/products', function() { return redirect()->to('/shopee')->with('error', 'Pilih toko Shopee terlebih dahulu.'); });
    $routes->get('/marketing/shopee_discount', function() { return redirect()->to('/shopee')->with('error', 'Pilih toko Shopee terlebih dahulu.'); });
    $routes->get('/shopee/boost', function() { return redirect()->to('/shopee')->with('error', 'Pilih toko Shopee terlebih dahulu.'); });
    $routes->get('/shopee/voucher', function() { return redirect()->to('/shopee')->with('error', 'Pilih toko Shopee terlebih dahulu.'); });
});

// ====================================================================
// 5. API WEBHOOKS (LISTENER SISTEM EKSTERNAL)
// ====================================================================
$routes->post('/api/webhook/fingerspot', 'Api\Webhook::fingerspot');
$routes->get('/api/webhook/fingerspot', 'Api\Webhook::ping');
$routes->post('/api/webhook/shopee', 'Api\ShopeeWebhook::receive');