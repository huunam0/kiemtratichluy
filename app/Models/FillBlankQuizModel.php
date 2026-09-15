<?php

namespace App\Models;

use CodeIgniter\Model;

class FillBlankQuizModel extends Model
{
    protected $table            = 'fill_blank_quizzes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'teacher_id', 'school_id', 'class_id', 'title', 
        'subject', 'time_limit', 'selected_question_ids', 'status'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getQuizzesByTeacher(int $teacherId)
    {
        return $this->select('fill_blank_quizzes.*, classes.name as class_name')
                    ->join('classes', 'classes.id = fill_blank_quizzes.class_id', 'left')
                    ->where('fill_blank_quizzes.teacher_id', $teacherId)
                    ->orderBy('fill_blank_quizzes.id', 'DESC')
                    ->findAll();
    }

    public function getQuizzesByClass(int $classId)
    {
        return $this->select('fill_blank_quizzes.*, users.full_name as teacher_name, classes.name as class_name')
                    ->join('users', 'users.id = fill_blank_quizzes.teacher_id', 'left')
                    ->join('classes', 'classes.id = fill_blank_quizzes.class_id', 'left')
                    ->where('fill_blank_quizzes.class_id', $classId)
                    ->where('fill_blank_quizzes.status', 'active')
                    ->orderBy('fill_blank_quizzes.id', 'DESC')
                    ->findAll();
    }

    public function getQuizzesBySchool(int $schoolId)
    {
        return $this->select('fill_blank_quizzes.*, users.full_name as teacher_name, classes.name as class_name')
                    ->join('users', 'users.id = fill_blank_quizzes.teacher_id', 'left')
                    ->join('classes', 'classes.id = fill_blank_quizzes.class_id', 'left')
                    ->where('fill_blank_quizzes.school_id', $schoolId)
                    ->where('fill_blank_quizzes.status', 'active')
                    ->orderBy('fill_blank_quizzes.id', 'DESC')
                    ->findAll();
    }
}
