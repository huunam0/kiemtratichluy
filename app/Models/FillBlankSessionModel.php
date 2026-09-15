<?php

namespace App\Models;

use CodeIgniter\Model;

class FillBlankSessionModel extends Model
{
    protected $table            = 'fill_blank_sessions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'quiz_id', 'teacher_id', 'class_id', 'session_code', 'status'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    public function getActiveSessionsForStudent(int $classId)
    {
        return $this->select('fill_blank_sessions.*, fill_blank_quizzes.title as quiz_title, fill_blank_quizzes.time_limit, classes.name as class_name, users.full_name as teacher_name')
                    ->join('fill_blank_quizzes', 'fill_blank_quizzes.id = fill_blank_sessions.quiz_id')
                    ->join('classes', 'classes.id = fill_blank_sessions.class_id')
                    ->join('users', 'users.id = fill_blank_sessions.teacher_id', 'left')
                    ->where('fill_blank_sessions.class_id', $classId)
                    ->whereIn('fill_blank_sessions.status', ['waiting', 'in_progress'])
                    ->orderBy('fill_blank_sessions.id', 'DESC')
                    ->findAll();
    }
}
