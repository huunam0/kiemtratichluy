<?php

namespace App\Models;

use CodeIgniter\Model;

class ShortAnswerQuizModel extends Model
{
    protected $table            = 'short_answer_quizzes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'teacher_id', 'school_id', 'class_id', 'title', 'time_limit',
        'shuffle_questions', 'penalty_coefficient', 'is_open', 'allow_mock',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getQuizzesByTeacher(int $teacherId): array
    {
        return $this->select('short_answer_quizzes.*, classes.name as class_name')
                    ->join('classes', 'classes.id = short_answer_quizzes.class_id', 'left')
                    ->where('short_answer_quizzes.teacher_id', $teacherId)
                    ->orderBy('short_answer_quizzes.id', 'DESC')
                    ->findAll();
    }

    public function getOpenQuizzesByClass(int $classId): array
    {
        return $this->select('short_answer_quizzes.*, users.full_name as teacher_name')
                    ->join('users', 'users.id = short_answer_quizzes.teacher_id', 'left')
                    ->where('short_answer_quizzes.class_id', $classId)
                    ->where('short_answer_quizzes.is_open', 1)
                    ->orderBy('short_answer_quizzes.id', 'DESC')
                    ->findAll();
    }

    public function getOpenQuizzesBySchool(int $schoolId): array
    {
        return $this->select('short_answer_quizzes.*, users.full_name as teacher_name, classes.name as class_name')
                    ->join('users', 'users.id = short_answer_quizzes.teacher_id', 'left')
                    ->join('classes', 'classes.id = short_answer_quizzes.class_id', 'left')
                    ->where('short_answer_quizzes.school_id', $schoolId)
                    ->where('short_answer_quizzes.is_open', 1)
                    ->orderBy('short_answer_quizzes.id', 'DESC')
                    ->findAll();
    }
}
