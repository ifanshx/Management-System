<?php
namespace App\Controllers;
use App\Models\LandingCatalogModel;

class LandingController extends BaseController
{
    public function index()
    {
        $catalogModel = new LandingCatalogModel();
        $data['catalogs'] = $catalogModel->findAll();
        
        return view('landing', $data);
    }
}