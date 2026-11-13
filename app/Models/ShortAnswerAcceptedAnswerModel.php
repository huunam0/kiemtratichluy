<?php

namespace App\Models;

use CodeIgniter\Model;

class ShortAnswerAcceptedAnswerModel extends Model
{
    protected $table            = 'short_answer_accepted_answers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'question_id', 'answer_text', 'case_sensitive', 'space_sensitive', 'sort_order',
    ];

    protected $useTimestamps = false;

    public function getAnswersByQuestion(int $questionId): array
    {
        return $this->where('question_id', $questionId)
                    ->orderBy('sort_order', 'ASC')
                    ->orderBy('id', 'ASC')
                    ->findAll();
    }

    public function deleteByQuestion(int $questionId): void
    {
        $this->where('question_id', $questionId)->delete();
    }
}
