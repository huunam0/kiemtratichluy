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
        'creator_id', 'topic_id', 'content', 
        'option_a', 'option_b', 'option_c', 'option_d', 
        'correct_option', 'explanation'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getAllGlobalQuestions(?int $subjectId = null, ?int $gradeLevel = null, ?int $topicId = null)
    {
        $builder = $this->select('questions.*, users.full_name as creator_name, topics.name as topic_name, subjects.name as subject_name, topics.grade_level, topics.subject_id')
                        ->join('users', 'users.id = questions.creator_id', 'left')
                        ->join('topics', 'topics.id = questions.topic_id', 'left')
                        ->join('subjects', 'subjects.id = topics.subject_id', 'left');

        if ($subjectId) {
            $builder->where('topics.subject_id', $subjectId);
        }
        if ($gradeLevel) {
            $builder->where('topics.grade_level', $gradeLevel);
        }
        if ($topicId) {
            $builder->where('questions.topic_id', $topicId);
        }

        return $builder->orderBy('questions.id', 'DESC')->findAll();
    }

    /**
     * Search questions with optional filters: subject, grade_level, topic, keyword.
     * Keyword matches against question content and answer options.
     */
    public function searchQuestions(?int $subjectId = null, ?int $gradeLevel = null, ?int $topicId = null, ?string $keyword = null): array
    {
        $builder = $this->select('questions.*, users.full_name as creator_name, topics.name as topic_name, subjects.name as subject_name, topics.grade_level, topics.subject_id')
                        ->join('users', 'users.id = questions.creator_id', 'left')
                        ->join('topics', 'topics.id = questions.topic_id', 'left')
                        ->join('subjects', 'subjects.id = topics.subject_id', 'left');

        if (!empty($subjectId)) {
            $builder->where('topics.subject_id', $subjectId);
        }
        if (!empty($gradeLevel)) {
            $builder->where('topics.grade_level', $gradeLevel);
        }
        if (!empty($topicId)) {
            $builder->where('questions.topic_id', $topicId);
        }
        if (!empty($keyword)) {
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

    public function canEditOrDelete(int $questionId, int $teacherId): bool
    {
        $question = $this->find($questionId);
        if (!$question) {
            return false;
        }
        return (int)$question['creator_id'] === $teacherId;
    }
}
