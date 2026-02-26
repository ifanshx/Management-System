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
    $routes->get('/employee/trigger_register_online/(:segment)', 'Employee::trigger_register_online/$1');
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
    
    $routes->get('/leave/approval', 'Leave::approval');
    $routes->get('/leave/process_action/(:num)/(:any)', 'Leave::process_action/$1/$2');

    // Modul Penggajian (Payroll)
    $routes->get('/payroll', 'Payroll::index');
    $routes->post('/payroll/generate', 'Payroll::generate');
    $routes->get('/payroll/detail/(:num)', 'Payroll::detail/$1');
    $routes->get('/payroll/print_slip/(:num)', 'Payroll::print_slip/$1');

    // Modul Keuangan & Kas
    $routes->get('/finance/cash_index', 'Finance::cash_index');
    $routes->post('/finance/cash_store', 'Finance::cash_store');
    $routes->get('/finance/cash_delete/(:num)', 'Finance::cash_delete/$1'); // TAMBAHKAN INI
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