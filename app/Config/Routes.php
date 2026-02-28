<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ====================================================================
// 1. RUTE PUBLIK (Tanpa Login / Guest)
// ====================================================================
$routes->get('/', 'AuthController::index');
$routes->get('/login', 'AuthController::index');
$routes->post('/login/process', 'AuthController::process');
$routes->get('/logout', 'AuthController::logout');

// ====================================================================
// 2. RUTE GLOBAL (Akses Bersama: Admin & Karyawan)
// ====================================================================
$routes->get('/profile', 'Profile::index');
$routes->post('/profile/update_password', 'Profile::update_password');

// ====================================================================
// 3. RUTE MANAJEMEN PUSAT (Super Admin & HRD)
// ====================================================================
$routes->group('', ['filter' => 'adminAuth'], static function ($routes) {
    // Pengaturan Identitas Perusahaan (White-Label)
    $routes->get('/setting/company', 'Setting::company');
    $routes->post('/setting/update_company', 'Setting::update_company');

    // Dashboard & Pengaturan
    $routes->get('/dashboard', 'Dashboard::index');
    $routes->get('/setting/workshift_create', 'Setting::workshift_create');
    $routes->post('/setting/workshift_store', 'Setting::workshift_store');

    // Modul Karyawan (CRUD)
    $routes->get('/employee', 'Employee::index');
    $routes->get('/employee/create', 'Employee::create');
    $routes->post('/employee/store', 'Employee::store');
    $routes->get('/employee/edit/(:num)', 'Employee::edit/$1');
    $routes->post('/employee/update/(:num)', 'Employee::update/$1');
    $routes->post('/employee/delete/(:num)', 'Employee::delete/$1');
    $routes->post('/employee/deactivate/(:num)', 'Employee::deactivate/$1');
    
    // Employee IoT Commands (Aksi per Individu)
    $routes->get('/employee/push_to_machine/(:num)', 'Employee::push_to_machine/$1');
    $routes->get('/employee/sync_biometric/(:segment)', 'Employee::sync_biometric/$1');

    // ====================================================================
    // PANEL KONTROL MESIN IOT (Hardware Maintenance)
    // ====================================================================
    $routes->get('/device', 'Device::index');
    $routes->get('/device/sync_time', 'Device::sync_time');
    $routes->get('/device/restart', 'Device::restart');
    $routes->get('/device/audit_pins', 'Device::audit_pins');

    // Modul Kehadiran & Cuti
    $routes->get('/attendance', 'Attendance::index');
    $routes->get('/attendance/sync', 'Attendance::syncData');
    $routes->get('/attendance/manual', 'Attendance::manual');
    $routes->post('/attendance/store_manual', 'Attendance::store_manual');
    $routes->get('/attendance/delete/(:num)', 'Attendance::delete/$1');
    
    $routes->get('/leave/approval', 'Leave::approval');
    $routes->get('/leave/process_action/(:num)/(:any)', 'Leave::process_action/$1/$2');

    // Modul Penggajian (Payroll)
    $routes->get('/payroll', 'Payroll::index');
    $routes->post('/payroll/generate', 'Payroll::generate');
    $routes->get('/payroll/detail/(:num)', 'Payroll::detail/$1');
    $routes->get('/payroll/print_slip/(:num)', 'Payroll::print_slip/$1');
    $routes->get('/payroll/delete/(:num)', 'Payroll::delete/$1');
    $routes->post('/payroll/push_to_finance', 'Payroll::push_to_finance');

    // Modul Keuangan & Kas
    $routes->get('/finance/cash_index', 'Finance::cash_index');
    $routes->post('/finance/cash_store', 'Finance::cash_store');
    $routes->get('/finance/cash_delete/(:num)', 'Finance::cash_delete/$1');

    // ====================================================================
    // Modul Penjualan Offline (POS Kasir) - BARU DITAMBAHKAN
    // ====================================================================
    $routes->get('/sales/offline', 'OfflineSales::index');
    $routes->post('/sales/process_offline', 'OfflineSales::process_checkout');

    // Modul Pabrik & Produksi
    $routes->get('/production', 'Production::index');
    $routes->post('/production/store_production', 'Production::store_production');

    // ====================================================================
    // INTEGRASI OMNICHANNEL (Shopee Open API)
    // ====================================================================
    $routes->get('/omni-dashboard', 'OmniDashboard::index');
    $routes->get('/shopee', 'Shopee::index');
    $routes->get('/shopee/callback', 'Shopee::callback');
    $routes->get('/shopee/sync_orders/(:segment)', 'Shopee::sync_orders/$1');
    $routes->get('/shopee/sync_products/(:segment)', 'Shopee::sync_products/$1');
    $routes->get('/shopee/sync_finance/(:segment)', 'Shopee::sync_finance/$1');
    $routes->get('/shopee/finances/(:segment)', 'Shopee::finances/$1');
    $routes->get('/shopee/sync_returns/(:segment)', 'Shopee::sync_returns/$1');
    // Modul Customer Service
    $routes->get('/customerservice/inbox/(:segment)', 'CustomerService::inbox/$1');
    $routes->post('/customerservice/reply_chat', 'CustomerService::reply_chat');

    // Rute Asli (Dengan ID Toko)
    $routes->get('/shopee/products/(:segment)', 'Shopee::products/$1'); 
    
    // Rute Pelindung (Jika user mengetik manual tanpa ID Toko)
    $routes->get('/shopee/products', function() {
        return redirect()->to('/shopee')->with('error', 'Silakan pilih toko Shopee terlebih dahulu untuk melihat katalog.');
    });

    // ====================================================================
    // Modul Marketing & Promo
    // ====================================================================
    // Rute Asli (Membutuhkan ID Toko)
    $routes->get('/marketing/shopee_discount/(:segment)', 'Marketing::shopee_discount/$1');
    $routes->post('/marketing/create_discount/(:segment)', 'Marketing::create_discount/$1');

    // Rute Pelindung (Jika admin mengetik URL manual tanpa ID Toko)
    $routes->get('/marketing/shopee_discount', function() {
        return redirect()->to('/shopee')->with('error', 'Silakan pilih toko Shopee terlebih dahulu dari Dasbor untuk mengatur Promosi.');
    });

    // ====================================================================
    // Modul Auto Boost Shopee
    // ====================================================================
    $routes->get('/shopee/boost/(:segment)', 'ShopeeBoost::index/$1');
    $routes->post('/shopee/boost_action/(:segment)', 'ShopeeBoost::push_boost/$1');
    
    // Rute Pelindung
    $routes->get('/shopee/boost', function() {
        return redirect()->to('/shopee')->with('error', 'Silakan pilih toko Shopee terlebih dahulu untuk menaikkan produk.');
    });

    // ====================================================================
    // Modul Marketing & Voucher Toko
    // ====================================================================
    $routes->get('/shopee/voucher/(:segment)', 'ShopeeVoucher::index/$1');
    $routes->post('/shopee/create_voucher/(:segment)', 'ShopeeVoucher::create_voucher/$1');
    $routes->get('/shopee/end_voucher/(:segment)/(:num)', 'ShopeeVoucher::end_voucher/$1/$2');
    
    $routes->get('/shopee/voucher', function() {
        return redirect()->to('/shopee')->with('error', 'Silakan pilih toko Shopee terlebih dahulu untuk mengatur Voucher.');
    });

    // ====================================================================
    // MODUL GUDANG & PACKING (WAREHOUSE)
    // ====================================================================
    $routes->get('/warehouse/orders', 'Warehouse::orders');
    $routes->get('/warehouse/ship_shopee_order/(:segment)', 'Warehouse::ship_shopee_order/$1');
    $routes->get('/warehouse/print_shopee_awb/(:segment)', 'Warehouse::print_shopee_awb/$1');
    // Modul Mass Fulfillment Gudang
    $routes->get('/warehouse/mass-fulfillment', 'Warehouse::mass_fulfillment');
    $routes->post('/warehouse/process_mass_action', 'Warehouse::process_mass_action');
    // Modul Master Gudang Lokal & Bahan Baku
    $routes->get('/warehouse/local-inventory', 'LocalWarehouse::index');
    $routes->post('/warehouse/store_fg', 'LocalWarehouse::store_fg');
    $routes->post('/warehouse/store_rm', 'LocalWarehouse::store_rm');
    $routes->get('/warehouse/delete_fg/(:num)', 'LocalWarehouse::delete_fg/$1');
    $routes->get('/warehouse/delete_rm/(:num)', 'LocalWarehouse::delete_rm/$1');
});

// ====================================================================
// 4. RUTE PORTAL KARYAWAN (Employee Self-Service / ESS)
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
// 5. API WEBHOOK (IoT Hardware Integration)
// ====================================================================
$routes->post('/api/webhook/fingerspot', 'Api\Webhook::fingerspot');
$routes->get('/api/webhook/fingerspot', 'Api\Webhook::ping');

// Endpoint Khusus Server Shopee (Real-Time Notification)
$routes->post('/api/webhook/shopee', 'Api\ShopeeWebhook::receive');