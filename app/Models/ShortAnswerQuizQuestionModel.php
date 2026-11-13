<?php

namespace App\Models;

use CodeIgniter\Model;

class ShortAnswerQuizQuestionModel extends Model
{
    protected $table            = 'short_answer_quiz_questions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'quiz_id', 'question_id', 'points', 'sort_order',
    ];

    protected $useTimestamps = false;

    /**
     * Lấy danh sách câu hỏi trong bài kiểm tra kèm nội dung câu hỏi.
     */
    public function getQuestionsWithContent(int $quizId): array
    {
        return $this->select('short_answer_quiz_questions.*, short_answer_questions.title, short_answer_questions.question_text, short_answer_questions.equivalent_id,
                             topics.name as topic_name, subjects.name as subject_name')
                    ->join('short_answer_questions', 'short_answer_questions.id = short_answer_quiz_questions.question_id')
                    ->join('topics', 'topics.id = short_answer_questions.topic_id', 'left')
                    ->join('subjects', 'subjects.id = topics.subject_id', 'left')
                    ->where('short_answer_quiz_questions.quiz_id', $quizId)
                    ->orderBy('short_answer_quiz_questions.sort_order', 'ASC')
                    ->orderBy('short_answer_quiz_questions.id', 'ASC')
                    ->findAll();
    }

    public function getMaxPoints(int $quizId): float
    {
        $result = $this->selectSum('points')->where('quiz_id', $quizId)->first();
        return (float)($result['points'] ?? 0);
    }

    public function deleteByQuiz(int $quizId): void
    {
        $this->where('quiz_id', $quizId)->delete();
    }
}
