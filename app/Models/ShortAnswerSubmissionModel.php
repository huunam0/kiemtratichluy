<?php

namespace App\Models;

use CodeIgniter\Model;

class ShortAnswerSubmissionModel extends Model
{
    protected $table            = 'short_answer_submissions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'session_id', 'quiz_id', 'student_id', 'is_mock',
        'student_answers', 'raw_score', 'final_score', 'penalty_deducted', 'submitted_at',
    ];

    protected $useTimestamps = false;

    /**
     * Kiểm tra học sinh đã nộp bài trong session này chưa.
     */
    public function hasSubmitted(int $sessionId, int $studentId): bool
    {
        return $this->where('session_id', $sessionId)
                    ->where('student_id', $studentId)
                    ->where('is_mock', 0)
                    ->countAllResults() > 0;
    }

    /**
     * Lấy bài nộp của học sinh trong session.
     */
    public function getBySessionAndStudent(int $sessionId, int $studentId): ?array
    {
        return $this->where('session_id', $sessionId)
                    ->where('student_id', $studentId)
                    ->where('is_mock', 0)
                    ->first();
    }

    /**
     * Lấy tất cả bài nộp thật trong session (để tính điểm phạt).
     */
    public function getRealSubmissionsBySession(int $sessionId): array
    {
        return $this->select('short_answer_submissions.*, users.full_name as student_name, users.username')
                    ->join('users', 'users.id = short_answer_submissions.student_id')
                    ->where('short_answer_submissions.session_id', $sessionId)
                    ->where('short_answer_submissions.is_mock', 0)
                    ->orderBy('short_answer_submissions.raw_score', 'DESC')
                    ->orderBy('short_answer_submissions.submitted_at', 'ASC')
                    ->findAll();
    }

    /**
     * Lấy lịch sử bài làm thật của học sinh theo quiz.
     */
    public function getStudentHistory(int $quizId, int $studentId): array
    {
        return $this->where('quiz_id', $quizId)
                    ->where('student_id', $studentId)
                    ->where('is_mock', 0)
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }
}
