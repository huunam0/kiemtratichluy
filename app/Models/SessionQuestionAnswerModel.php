<?php

namespace App\Models;

use CodeIgniter\Model;

class SessionQuestionAnswerModel extends Model
{
    protected $table            = 'session_question_answers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'participant_id', 'question_id', 'question_order', 
        'option_mapping', 'selected_option', 'original_selected_option', 
        'is_correct', 'answered_at'
    ];

    public function getParticipantQuestions(int $participantId)
    {
        return $this->select('session_question_answers.*, questions.content, questions.option_a, questions.option_b, questions.option_c, questions.option_d, questions.correct_option, questions.explanation')
                    ->join('questions', 'questions.id = session_question_answers.question_id')
                    ->where('session_question_answers.participant_id', $participantId)
                    ->orderBy('session_question_answers.question_order', 'ASC')
                    ->findAll();
    }
}
