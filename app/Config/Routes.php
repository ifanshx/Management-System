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
// Halaman depan / Company Profile
$routes->get('/', 'LandingController::index');

// Autentikasi Sistem
$routes->get('/login', 'AuthController::index');
$routes->post('/login/process', 'AuthController::process');
$routes->get('/logout', 'AuthController::logout');

// ====================================================================
// 2. RUTE GLOBAL (AKSES BERSAMA: ADMIN & KARYAWAN)
// ====================================================================
$routes->get('/profile', 'Profile::index');
$routes->post('/profile/update_password', 'Profile::update_password');


// ====================================================================
// 3. RUTE MANAJEMEN PUSAT (SUPER ADMIN & HRD)
// Dilindungi oleh Filter: adminAuth
// ====================================================================
$routes->group('', ['filter' => 'adminAuth'], static function ($routes) {
    
    // --- EXECUTIVE DASHBOARD & IDENTITAS ---
    $routes->get('/dashboard', 'Dashboard::index');
    $routes->get('/setting/company', 'Setting::company');
    $routes->post('/setting/update_company', 'Setting::update_company');
    $routes->get('/setting/workshift_create', 'Setting::workshift_create');
    $routes->post('/setting/workshift_store', 'Setting::workshift_store');
    // Manajemen Katalog Landing Page
    $routes->post('/setting/store_catalog', 'Setting::store_catalog');
    $routes->post('/setting/update_catalog/(:num)', 'Setting::update_catalog/$1'); // <-- TAMBAHKAN INI
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

    // --- KONTROL HARDWARE (IOT DEVICE MAINTENANCE) ---
    $routes->get('/device', 'Device::index');
    $routes->get('/device/sync_time', 'Device::sync_time');
    $routes->get('/device/restart', 'Device::restart');
    $routes->get('/device/audit_pins', 'Device::audit_pins');

    // --- MODUL ABSENSI & CUTI ---
    $routes->get('/attendance', 'Attendance::index');
    $routes->get('/attendance/sync', 'Attendance::syncData');
    $routes->get('/attendance/manual', 'Attendance::manual');
    $routes->post('/attendance/store_manual', 'Attendance::store_manual');
    $routes->get('/attendance/delete/(:num)', 'Attendance::delete/$1');
    
    $routes->get('/leave/approval', 'Leave::approval');
    $routes->get('/leave/process_action/(:num)/(:any)', 'Leave::process_action/$1/$2');

    // --- MODUL PENGGAJIAN (PAYROLL ENGINE) ---
    $routes->get('/payroll', 'Payroll::index');
    $routes->post('/payroll/generate', 'Payroll::generate');
    $routes->get('/payroll/detail/(:num)', 'Payroll::detail/$1');
    $routes->get('/payroll/print_slip/(:num)', 'Payroll::print_slip/$1');
    $routes->get('/payroll/delete/(:num)', 'Payroll::delete/$1');
    $routes->post('/payroll/push_to_finance', 'Payroll::push_to_finance');

    // --- MODUL KEUANGAN & AKUNTANSI ---
    $routes->get('/finance/cash_index', 'Finance::cash_index');
    $routes->post('/finance/cash_store', 'Finance::cash_store');
    $routes->get('/finance/cash_delete/(:num)', 'Finance::cash_delete/$1');

    $routes->get('/accounting', 'Accounting::index');
    $routes->get('/accounting/journal', 'Accounting::journal');
    $routes->post('/accounting/store_journal', 'Accounting::store_journal');

    // --- MODUL PENJUALAN KASIR (OFFLINE POS) ---
    $routes->get('/sales/offline', 'OfflineSales::index');
    $routes->post('/sales/process_offline', 'OfflineSales::process_offline'); 
    $routes->get('/sales/offline_history', 'OfflineSales::history');
    $routes->get('/sales/get_detail/(:any)', 'OfflineSales::get_detail/$1');

    // --- MODUL B2B (WHOLESALE & PIUTANG) ---
    $routes->get('/wholesale', 'Wholesale::index');
    $routes->get('/wholesale/surat_jalan/(:num)', 'Wholesale::surat_jalan/$1');
    $routes->post('/wholesale/store_customer', 'Wholesale::store_customer');
    $routes->get('/wholesale/delete_customer/(:num)', 'Wholesale::delete_customer/$1');
    $routes->post('/wholesale/store_so', 'Wholesale::store_so');
    $routes->post('/wholesale/pay_installment/(:num)', 'Wholesale::pay_installment/$1');

    // --- MODUL PRODUKSI & MANUFAKTUR (MES) ---
    $routes->get('/production', 'Production::index');
    $routes->get('/production/bom_builder', 'Production::bom_builder');
    $routes->post('/production/store_bom', 'Production::store_bom');
    $routes->post('/production/create_spk', 'Production::create_spk');
    $routes->get('/production/complete_spk/(:num)', 'Production::complete_spk/$1');

    // --- MODUL PEMBELIAN BARANG (PROCUREMENT) ---
    $routes->get('/procurement', 'Procurement::index');
    $routes->get('/procurement/detail/(:num)', 'Procurement::detail/$1'); 
    $routes->post('/procurement/store_supplier', 'Procurement::store_supplier'); 
    $routes->get('/procurement/create_po', 'Procurement::create_po');
    $routes->post('/procurement/store_po', 'Procurement::store_po');
    $routes->get('/procurement/receive_goods/(:num)', 'Procurement::receive_goods/$1');
    $routes->get('/procurement/delete_supplier/(:num)', 'Procurement::delete_supplier/$1');

    // --- MANAJEMEN ASET PABRIK ---
    $routes->get('/asset', 'Asset::index');
    $routes->post('/asset/store', 'Asset::store');
    $routes->post('/asset/update_status/(:num)', 'Asset::update_status/$1');
    $routes->get('/asset/delete/(:num)', 'Asset::delete/$1');

    // --- MANAJEMEN GUDANG (INVENTORY & WAREHOUSE) ---
    $routes->get('/warehouse/local-inventory', 'LocalWarehouse::index');
    $routes->post('/warehouse/store_fg', 'LocalWarehouse::store_fg');
    $routes->post('/warehouse/store_rm', 'LocalWarehouse::store_rm');
    $routes->get('/warehouse/delete_fg/(:num)', 'LocalWarehouse::delete_fg/$1');
    $routes->get('/warehouse/delete_rm/(:num)', 'LocalWarehouse::delete_rm/$1');
    
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

    // Route Pelindung (Mencegah Akses Tanpa ID Toko)
    $routes->get('/shopee/products', function() { return redirect()->to('/shopee')->with('error', 'Pilih toko Shopee terlebih dahulu.'); });
    $routes->get('/marketing/shopee_discount', function() { return redirect()->to('/shopee')->with('error', 'Pilih toko Shopee terlebih dahulu.'); });
    $routes->get('/shopee/boost', function() { return redirect()->to('/shopee')->with('error', 'Pilih toko Shopee terlebih dahulu.'); });
    $routes->get('/shopee/voucher', function() { return redirect()->to('/shopee')->with('error', 'Pilih toko Shopee terlebih dahulu.'); });
});

// ====================================================================
// 4. RUTE PORTAL KARYAWAN (EMPLOYEE SELF-SERVICE / ESS)
// Dilindungi oleh Filter: karyawanAuth
// ====================================================================
$routes->group('', ['filter' => 'karyawanAuth'], static function ($routes) {
    $routes->get('/portal', 'Portal::index');
    $routes->get('/portal/absen/(:segment)', 'Portal::record_attendance/$1');
    $routes->get('/portal/slip_gaji', 'Portal::slip_gaji');
    $routes->get('/portal/print_slip/(:num)', 'Portal::print_my_slip/$1');
    $routes->get('/leave', 'Leave::index');
    $routes->post('/leave/store', 'Leave::store');
}); 

// ====================================================================
// 5. API WEBHOOKS (LISTENER SISTEM EKSTERNAL)
// ====================================================================
// IoT Hardware Integration (Fingerspot / Mesin Absen)
$routes->post('/api/webhook/fingerspot', 'Api\Webhook::fingerspot');
$routes->get('/api/webhook/fingerspot', 'Api\Webhook::ping');

// Endpoint Khusus Server Shopee (Real-Time Notification Push)
$routes->post('/api/webhook/shopee', 'Api\ShopeeWebhook::receive');