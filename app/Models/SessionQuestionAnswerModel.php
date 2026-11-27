<?php

namespace App\Models;

use CodeIgniter\Model;

class SessionQuestionAnswerModel extends Model
{
    protected $table            = 'session_question_answers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'participant_id', 'question_id', 'question_order', 
        'option_mapping', 'selected_option', 'original_selected_option', 
        'is_correct', 'answered_at'
    ];

    public function getParticipantQuestions(int $participantId)
    {
        return $this->select('session_question_answers.*, questions.content, questions.option_a, questions.option_b, questions.option_c, questions.option_d, questions.correct_option, questions.explanation')
                    ->join('questions', 'questions.id = session_question_answers.question_id')
                    ->where('session_question_answers.participant_id', $participantId)
                    ->orderBy('session_question_answers.question_order', 'ASC')
                    ->findAll();
    }

    /**
     * Lấy thống kê số lần câu hỏi được bốc vào đề và số lần thí sinh làm đúng
     *
     * @param array $questionIds Danh sách ID câu hỏi
     * @param int|null $testId ID bài kiểm tra (nếu có để thống kê riêng theo bài kiểm tra)
     * @return array [question_id => ['picked' => int, 'correct' => int, 'test_picked' => int, 'test_correct' => int]]
     */
    public function getQuestionsStats(array $questionIds, ?int $testId = null): array
    {
        if (empty($questionIds)) {
            return [];
        }

        $builder = $this->builder();
        
        $selectCols = "session_question_answers.question_id, 
                       COUNT(session_question_answers.id) as picked_count, 
                       SUM(CASE WHEN session_question_answers.is_correct = 1 THEN 1 ELSE 0 END) as correct_count";
        
        if ($testId !== null) {
            $selectCols .= ", 
                           SUM(CASE WHEN ts.test_id = " . (int)$testId . " THEN 1 ELSE 0 END) as test_picked_count,
                           SUM(CASE WHEN ts.test_id = " . (int)$testId . " AND session_question_answers.is_correct = 1 THEN 1 ELSE 0 END) as test_correct_count";
        }

        $rows = $builder->select($selectCols, false)
                        ->join('session_participants sp', 'sp.id = session_question_answers.participant_id')
                        ->join('test_sessions ts', 'ts.id = sp.session_id')
                        ->whereIn('session_question_answers.question_id', $questionIds)
                        ->where('sp.test_status', 'submitted')
                        ->where('sp.is_cancelled', 0)
                        ->where('ts.status !=', 'cancelled')
                        ->groupBy('session_question_answers.question_id')
                        ->get()
                        ->getResultArray();

        $stats = [];
        foreach ($rows as $row) {
            $qid = (int)$row['question_id'];
            $stats[$qid] = [
                'picked'       => (int)($row['picked_count'] ?? 0),
                'correct'      => (int)($row['correct_count'] ?? 0),
                'test_picked'  => isset($row['test_picked_count']) ? (int)$row['test_picked_count'] : (int)($row['picked_count'] ?? 0),
                'test_correct' => isset($row['test_correct_count']) ? (int)$row['test_correct_count'] : (int)($row['correct_count'] ?? 0),
            ];
        }

        return $stats;
    }

    /**
     * Lấy thống kê số lần mỗi phương án của danh sách câu hỏi được học sinh chọn
     *
     * @param array $questionIds Danh sách ID câu hỏi
     * @param int|null $testId ID bài kiểm tra (nếu có để lọc riêng theo bài)
     * @return array [question_id => [
     *     'total'        => int,
     *     'test_total'   => int,
     *     'options'      => ['A' => ['total' => int, 'test' => int], ...],
     *     'unanswered'   => ['total' => int, 'test' => int],
     * ]]
     */
    public function getQuestionsOptionStats(array $questionIds, ?int $testId = null): array
    {
        if (empty($questionIds)) {
            return [];
        }

        $builder = $this->builder();
        $selectCols = "session_question_answers.question_id,
                       session_question_answers.original_selected_option,
                       COUNT(session_question_answers.id) as picked_count";

        if ($testId !== null) {
            $selectCols .= ", SUM(CASE WHEN ts.test_id = " . (int)$testId . " THEN 1 ELSE 0 END) as test_picked_count";
        }

        $rows = $builder->select($selectCols, false)
                        ->join('session_participants sp', 'sp.id = session_question_answers.participant_id')
                        ->join('test_sessions ts', 'ts.id = sp.session_id')
                        ->whereIn('session_question_answers.question_id', $questionIds)
                        ->where('sp.test_status', 'submitted')
                        ->where('sp.is_cancelled', 0)
                        ->where('ts.status !=', 'cancelled')
                        ->groupBy('session_question_answers.question_id, session_question_answers.original_selected_option')
                        ->get()
                        ->getResultArray();

        $stats = [];
        foreach ($questionIds as $qid) {
            $stats[(int)$qid] = [
                'total'      => 0,
                'test_total' => 0,
                'options'    => [
                    'A' => ['total' => 0, 'test' => 0],
                    'B' => ['total' => 0, 'test' => 0],
                    'C' => ['total' => 0, 'test' => 0],
                    'D' => ['total' => 0, 'test' => 0],
                ],
                'unanswered' => ['total' => 0, 'test' => 0],
            ];
        }

        foreach ($rows as $row) {
            $qid = (int)$row['question_id'];
            $opt = !empty($row['original_selected_option']) ? strtoupper(trim($row['original_selected_option'])) : null;
            $count = (int)($row['picked_count'] ?? 0);
            $testCount = isset($row['test_picked_count']) ? (int)$row['test_picked_count'] : $count;

            if (!isset($stats[$qid])) {
                continue;
            }

            $stats[$qid]['total'] += $count;
            $stats[$qid]['test_total'] += $testCount;

            if ($opt && in_array($opt, ['A', 'B', 'C', 'D'])) {
                $stats[$qid]['options'][$opt]['total'] = $count;
                $stats[$qid]['options'][$opt]['test'] = $testCount;
            } else {
                $stats[$qid]['unanswered']['total'] += $count;
                $stats[$qid]['unanswered']['test'] += $testCount;
            }
        }

        return $stats;
    }
}
