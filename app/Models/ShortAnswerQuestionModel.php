<?php

namespace App\Models;

use CodeIgniter\Model;

class ShortAnswerQuestionModel extends Model
{
    protected $table            = 'short_answer_questions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'teacher_id', 'school_id', 'topic_id', 'title', 'question_text', 'prefilled_text', 'equivalent_id',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getQuestionsByTeacher(int $teacherId): array
    {
        return $this->select('short_answer_questions.*, topics.name as topic_name, subjects.name as subject_name, topics.grade_level,
                             (SELECT COUNT(*) FROM short_answer_accepted_answers WHERE short_answer_accepted_answers.question_id = short_answer_questions.id) as answer_count')
                    ->join('topics', 'topics.id = short_answer_questions.topic_id', 'left')
                    ->join('subjects', 'subjects.id = topics.subject_id', 'left')
                    ->where('short_answer_questions.teacher_id', $teacherId)
                    ->orderBy('short_answer_questions.id', 'DESC')
                    ->findAll();
    }

    public function getQuestionsBySchool(int $schoolId): array
    {
        return $this->select('short_answer_questions.*, topics.name as topic_name, subjects.name as subject_name, topics.grade_level,
                             (SELECT COUNT(*) FROM short_answer_accepted_answers WHERE short_answer_accepted_answers.question_id = short_answer_questions.id) as answer_count')
                    ->join('topics', 'topics.id = short_answer_questions.topic_id', 'left')
                    ->join('subjects', 'subjects.id = topics.subject_id', 'left')
                    ->where('short_answer_questions.school_id', $schoolId)
                    ->orderBy('short_answer_questions.id', 'DESC')
                    ->findAll();
    }
}
