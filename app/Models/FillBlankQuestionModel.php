<?php

namespace App\Models;

use CodeIgniter\Model;

class FillBlankQuestionModel extends Model
{
    protected $table            = 'fill_blank_questions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'teacher_id', 'school_id', 'title', 'topic_id'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getQuestionsByTeacher(int $teacherId)
    {
        return $this->select('fill_blank_questions.*, topics.name as topic_name, subjects.name as subject_name, topics.grade_level,
                            (SELECT COUNT(*) FROM fill_blank_question_variants WHERE fill_blank_question_variants.question_id = fill_blank_questions.id) as variant_count')
                    ->join('topics', 'topics.id = fill_blank_questions.topic_id', 'left')
                    ->join('subjects', 'subjects.id = topics.subject_id', 'left')
                    ->where('teacher_id', $teacherId)
                    ->orderBy('fill_blank_questions.id', 'DESC')
                    ->findAll();
    }
}
