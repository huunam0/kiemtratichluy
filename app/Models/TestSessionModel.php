<?php

namespace App\Models;

use CodeIgniter\Model;

class TestSessionModel extends Model
{
    protected $table            = 'test_sessions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'test_id', 'teacher_id', 'session_code', 'session_title', 
        'num_questions', 'time_per_question_sec', 'status', 
        'started_at', 'ended_at'
    ];

    // Dates
    protected $useTimestamps = false; // We set created_at via DEFAULT CURRENT_TIMESTAMP

    public function getActiveSessionsForStudent(int $classId)
    {
        return $this->select('test_sessions.*, tests.title as test_title, classes.name as class_name, users.full_name as teacher_name')
                    ->join('tests', 'tests.id = test_sessions.test_id')
                    ->join('classes', 'classes.id = tests.class_id')
                    ->join('users', 'users.id = test_sessions.teacher_id', 'left')
                    ->where('tests.class_id', $classId)
                    ->whereIn('test_sessions.status', ['waiting', 'in_progress'])
                    ->orderBy('test_sessions.id', 'DESC')
                    ->findAll();
    }
}
