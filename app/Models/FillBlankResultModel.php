<?php

namespace App\Models;

use CodeIgniter\Model;

class FillBlankResultModel extends Model
{
    protected $table            = 'fill_blank_results';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'quiz_id', 'session_id', 'student_id', 'is_mock',
        'score_correct', 'total_blanks', 'score_base10',
        'student_answers', 'completed_at'
    ];

    public function getResultsByStudent(int $studentId)
    {
        return $this->select('fill_blank_results.*, fill_blank_quizzes.title as quiz_title')
                    ->join('fill_blank_quizzes', 'fill_blank_quizzes.id = fill_blank_results.quiz_id')
                    ->where('fill_blank_results.student_id', $studentId)
                    ->orderBy('fill_blank_results.id', 'DESC')
                    ->findAll();
    }
}
