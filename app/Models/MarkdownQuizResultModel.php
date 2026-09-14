<?php

namespace App\Models;

use CodeIgniter\Model;

class MarkdownQuizResultModel extends Model
{
    protected $table            = 'markdown_quiz_results';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'quiz_id', 'user_id', 'student_name',
        'score_correct', 'total_questions', 'wrong_questions_json', 'completed_at'
    ];

    protected $useTimestamps = false;

    public function getResultsByQuiz(int $quizId)
    {
        return $this->where('quiz_id', $quizId)
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }
}
