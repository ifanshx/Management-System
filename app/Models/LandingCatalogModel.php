<?php

namespace App\Models;

use CodeIgniter\Model;

class LandingCatalogModel extends Model
{
    protected $table            = 'landing_catalogs';
    protected $primaryKey       = 'id';
    
    // Tipe data return array sangat disarankan agar view mudah memprosesnya
    protected $returnType       = 'array'; 

   protected $allowedFields = [
        'product_name', 'category', 'product_image', 
        'price', 'discount_price', 'specs', 
        'badge_text', 'icon_class', 'shopee_link', 'wa_link'
    ];
}