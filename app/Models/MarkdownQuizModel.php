<?php

namespace App\Models;

use CodeIgniter\Model;

class MarkdownQuizModel extends Model
{
    protected $table            = 'markdown_quizzes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'teacher_id', 'school_id', 'class_id', 'title', 'slug',
        'subject', 'grade_level', 'content_markdown', 'status', 'allow_mock'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getQuizzesByTeacher(int $teacherId)
    {
        return $this->select('markdown_quizzes.*, users.full_name as teacher_name, classes.name as class_name')
                    ->join('users', 'users.id = markdown_quizzes.teacher_id', 'left')
                    ->join('classes', 'classes.id = markdown_quizzes.class_id', 'left')
                    ->where('teacher_id', $teacherId)
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }

    /**
     * Get quizzes assigned to a specific class (for student access).
     */
    public function getQuizzesByClass(int $classId)
    {
        return $this->select('markdown_quizzes.*, users.full_name as teacher_name')
                    ->join('users', 'users.id = markdown_quizzes.teacher_id', 'left')
                    ->where('markdown_quizzes.class_id', $classId)
                    ->where('markdown_quizzes.status', 'active')
                    ->orderBy('markdown_quizzes.id', 'DESC')
                    ->findAll();
    }

    public function getQuizBySlug(string $slug)
    {
        return $this->select('markdown_quizzes.*, users.full_name as teacher_name')
                    ->join('users', 'users.id = markdown_quizzes.teacher_id', 'left')
                    ->where('slug', $slug)
                    ->first();
    }

    public function generateUniqueSlug(string $title): string
    {
        $slug = url_title($title, '-', true);
        if (empty($slug)) {
            $slug = 'quiz-' . time();
        }

        $existing = $this->where('slug', $slug)->first();
        if ($existing) {
            $slug .= '-' . substr(md5(uniqid()), 0, 5);
        }

        return $slug;
    }
}
