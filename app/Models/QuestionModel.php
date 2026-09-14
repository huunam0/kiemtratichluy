<?php

namespace App\Models;

use CodeIgniter\Model;

class QuestionModel extends Model
{
    protected $table            = 'questions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'creator_id', 'subject', 'grade_level', 'content', 
        'option_a', 'option_b', 'option_c', 'option_d', 
        'correct_option', 'explanation'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getAllGlobalQuestions(?string $subject = null, ?int $gradeLevel = null)
    {
        $builder = $this->select('questions.*, users.full_name as creator_name')
                        ->join('users', 'users.id = questions.creator_id', 'left');

        if ($subject) {
            $builder->where('questions.subject', $subject);
        }
        if ($gradeLevel) {
            $builder->where('questions.grade_level', $gradeLevel);
        }

        return $builder->orderBy('questions.id', 'DESC')->findAll();
    }

    /**
     * Search questions with optional filters: subject, grade_level, keyword.
     * Keyword matches against question content and answer options.
     */
    public function searchQuestions(?string $subject = null, ?int $gradeLevel = null, ?string $keyword = null): array
    {
        $builder = $this->select('questions.*, users.full_name as creator_name')
                        ->join('users', 'users.id = questions.creator_id', 'left');

        if (!empty($subject)) {
            $builder->where('questions.subject', $subject);
        }
        if (!empty($gradeLevel)) {
            $builder->where('questions.grade_level', $gradeLevel);
        }
        if (!empty($keyword)) {
            $kw = '%' . $keyword . '%';
            $builder->groupStart()
                        ->like('questions.content', $keyword)
                        ->orLike('questions.option_a', $keyword)
                        ->orLike('questions.option_b', $keyword)
                        ->orLike('questions.option_c', $keyword)
                        ->orLike('questions.option_d', $keyword)
                    ->groupEnd();
        }

        return $builder->orderBy('questions.id', 'DESC')->findAll();
    }

    /**
     * Return distinct subject values that exist in the questions table.
     */
    public function getDistinctSubjects(): array
    {
        return $this->select('subject')
                    ->distinct()
                    ->orderBy('subject', 'ASC')
                    ->findAll();
    }

    public function canEditOrDelete(int $questionId, int $teacherId): bool
    {
        $question = $this->find($questionId);
        if (!$question) {
            return false;
        }
        return (int)$question['creator_id'] === $teacherId;
    }
}
