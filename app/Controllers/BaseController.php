<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->session = \Config\Services::session();

        // -------------------------------------------------------------------
        // ENGINE WHITE-LABEL: Ambil identitas perusahaan dari Database
        // -------------------------------------------------------------------
        $db = \Config\Database::connect();
        // Asumsi Anda sudah membuat tabel 'company_settings'
        $company = $db->table('company_settings')->get()->getRowArray();

        // Jika tabel masih kosong (baru diinstal di klien baru), gunakan default
        if (!$company) {
            $company = [
                'app_name'     => 'ERP System',
                'company_name' => 'Nama Perusahaan Anda',
                'address'      => 'Alamat Perusahaan',
                'phone'        => '080000000',
                'logo_path'    => 'default-logo.png' // Pastikan ada gambar ini di public/assets/img/
            ];
        }

        // Sebarkan variabel $company ke SELURUH file View (.php)
        \Config\Services::renderer()->setVar('company', $company);
    }
}
