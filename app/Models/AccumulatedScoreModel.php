<?php

namespace App\Models;

use CodeIgniter\Model;

class AccumulatedScoreModel extends Model
{
    protected $table            = 'accumulated_scores';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'student_id', 'test_id', 'total_correct', 'total_attempted', 'last_updated_at'
    ];

    /**
     * Increment accumulated scores for a student on a specific test.
     */
    public function recordTestScore(int $studentId, int $testId, int $correctDelta, int $attemptedDelta)
    {
        $existing = $this->where('student_id', $studentId)
                         ->where('test_id', $testId)
                         ->first();

        if ($existing) {
            $newCorrect   = (int)$existing['total_correct'] + $correctDelta;
            $newAttempted = (int)$existing['total_attempted'] + $attemptedDelta;

            $this->update($existing['id'], [
                'total_correct'   => $newCorrect,
                'total_attempted' => $newAttempted,
                'last_updated_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            $this->insert([
                'student_id'      => $studentId,
                'test_id'         => $testId,
                'total_correct'   => $correctDelta,
                'total_attempted' => $attemptedDelta,
                'last_updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Apply absent penalty: +0 correct, +3 attempted.
     */
    public function applyAbsentPenalty(int $studentId, int $testId)
    {
        $this->recordTestScore($studentId, $testId, 0, 3);
    }

    /**
     * Get Accumulated Score Leaderboard for a Test
     */
    public function getLeaderboardByTest(int $testId)
    {
        return $this->select('accumulated_scores.*, users.full_name as student_name, users.username')
                    ->join('users', 'users.id = accumulated_scores.student_id')
                    ->where('accumulated_scores.test_id', $testId)
                    ->orderBy('accumulated_scores.accumulated_gpa', 'DESC')
                    ->orderBy('accumulated_scores.total_correct', 'DESC')
                    ->findAll();
    }
}
