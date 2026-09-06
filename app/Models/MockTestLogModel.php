<?php

namespace App\Models;

use CodeIgniter\Model;

class MockTestLogModel extends Model
{
    protected $table            = 'mock_test_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'student_id', 'test_id', 'num_questions', 'score_correct', 
        'total_questions', 'completed_at'
    ];
}
