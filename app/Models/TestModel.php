<?php

namespace App\Models;

use CodeIgniter\Model;

class TestModel extends Model
{
    protected $table            = 'tests';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'class_id', 'teacher_id', 'title', 'description', 'status', 'allow_mock'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getTestsByClass(int $classId)
    {
        return $this->select('tests.*, classes.name as class_name, users.full_name as teacher_name, 
                            (SELECT COUNT(*) FROM test_questions_pool WHERE test_questions_pool.test_id = tests.id) as total_questions')
                    ->join('classes', 'classes.id = tests.class_id')
                    ->join('users', 'users.id = tests.teacher_id', 'left')
                    ->where('tests.class_id', $classId)
                    ->orderBy('tests.id', 'DESC')
                    ->findAll();
    }
}
