<?php

namespace App\Models;

use CodeIgniter\Model;

class TestQuestionPoolModel extends Model
{
    protected $table            = 'test_questions_pool';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['test_id', 'question_id', 'added_by_teacher_id', 'added_at'];

    public function getQuestionsInPool(int $testId)
    {
        return $this->select('questions.*, topics.name as topic_name, subjects.name as subject_name, topics.grade_level, test_questions_pool.added_at, users.full_name as added_by_name')
                    ->join('questions', 'questions.id = test_questions_pool.question_id')
                    ->join('topics', 'topics.id = questions.topic_id', 'left')
                    ->join('subjects', 'subjects.id = topics.subject_id', 'left')
                    ->join('users', 'users.id = test_questions_pool.added_by_teacher_id', 'left')
                    ->where('test_questions_pool.test_id', $testId)
                    ->orderBy('test_questions_pool.id', 'ASC')
                    ->findAll();
    }

    public function getRandomQuestionsFromPool(int $testId, int $count)
    {
        $allQuestions = $this->select('questions.*, topics.name as topic_name, subjects.name as subject_name, topics.grade_level')
                             ->join('questions', 'questions.id = test_questions_pool.question_id')
                             ->join('topics', 'topics.id = questions.topic_id', 'left')
                             ->join('subjects', 'subjects.id = topics.subject_id', 'left')
                             ->where('test_questions_pool.test_id', $testId)
                             ->orderBy('RAND()')
                             ->findAll();

        if (empty($allQuestions)) {
            return [];
        }

        // Nhóm các câu hỏi theo mã nhóm tương đương (equivalent_id).
        // Trong một đề thi không chọn 2 câu cùng nhóm: mỗi nhóm chỉ bốc ngẫu nhiên 1 câu đại diện.
        $grouped = [];
        foreach ($allQuestions as $q) {
            $equivKey = !empty($q['equivalent_id']) ? (int)$q['equivalent_id'] : ('q_' . $q['id']);
            $grouped[$equivKey][] = $q;
        }

        $picked = [];
        foreach ($grouped as $group) {
            $picked[] = $group[array_rand($group)];
        }

        // Xáo trộn thứ tự các câu đã chọn và lấy số lượng theo yêu cầu $count
        shuffle($picked);
        return array_slice($picked, 0, $count);
    }
}
