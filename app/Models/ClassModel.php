<?php

namespace App\Models;

use CodeIgniter\Model;

class ClassModel extends Model
{
    protected $table            = 'classes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'school_id', 'creator_teacher_id', 'name', 
        'grade_level', 'academic_year', 'status'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getClassesBySchool(int $schoolId)
    {
        return $this->select('classes.*, users.full_name as creator_name')
                    ->join('users', 'users.id = classes.creator_teacher_id', 'left')
                    ->where('classes.school_id', $schoolId)
                    ->where('classes.status', 'active')
                    ->orderBy('classes.name', 'ASC')
                    ->findAll();
    }
}
