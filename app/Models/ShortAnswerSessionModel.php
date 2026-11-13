<?php

namespace App\Models;

use CodeIgniter\Model;

class ShortAnswerSessionModel extends Model
{
    protected $table            = 'short_answer_sessions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'quiz_id', 'teacher_id', 'class_id', 'status', 'opened_at', 'closed_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = false;

    public function getLatestOpenSession(int $quizId): ?array
    {
        return $this->where('quiz_id', $quizId)
                    ->where('status', 'open')
                    ->orderBy('id', 'DESC')
                    ->first();
    }

    public function getSessionsByQuiz(int $quizId): array
    {
        return $this->where('quiz_id', $quizId)
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }
}
