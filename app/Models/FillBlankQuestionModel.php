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
        'teacher_id', 'school_id', 'title', 'subject', 'grade_level'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getQuestionsByTeacher(int $teacherId)
    {
        return $this->select('fill_blank_questions.*, 
                            (SELECT COUNT(*) FROM fill_blank_question_variants WHERE fill_blank_question_variants.question_id = fill_blank_questions.id) as variant_count')
                    ->where('teacher_id', $teacherId)
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }
}
