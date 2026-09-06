<?php

namespace App\Models;

use CodeIgniter\Model;

class SchoolModel extends Model
{
    protected $table            = 'schools';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'code', 'address', 'status'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'name' => 'required|min_length[3]|max_length[255]',
        'code' => 'required|min_length[2]|max_length[50]|is_unique[schools.code,id,{id}]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
}
