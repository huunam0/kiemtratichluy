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
     * Record accumulated score for a student on a specific test.
     */
    public function recordTestScore(int $studentId, int $testId, int $correctDelta = 0, int $attemptedDelta = 0)
    {
        $this->recalculateStudentScore($studentId, $testId);
    }

    /**
     * Apply absent penalty: +0 correct, +3 attempted.
     */
    public function applyAbsentPenalty(int $studentId, int $testId)
    {
        $this->recalculateStudentScore($studentId, $testId);
    }

    /**
     * Recalculate accumulated scores for a student on a specific test
     * from all valid (non-cancelled session AND non-cancelled participant) records.
     */
    public function recalculateStudentScore(int $studentId, int $testId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('session_participants');
        $builder->select('session_participants.approval_status, session_participants.test_status, session_participants.score_correct, session_participants.score_total')
                ->join('test_sessions', 'test_sessions.id = session_participants.session_id')
                ->where('test_sessions.test_id', $testId)
                ->where('session_participants.student_id', $studentId)
                ->where('test_sessions.status !=', 'cancelled')
                ->where('session_participants.is_cancelled', 0);

        $records = $builder->get()->getResultArray();

        $totalCorrect = 0;
        $totalAttempted = 0;

        foreach ($records as $r) {
            if ($r['approval_status'] === 'absent') {
                $totalAttempted += 3;
            } elseif ($r['test_status'] === 'submitted') {
                $totalCorrect += (int)$r['score_correct'];
                $totalAttempted += (int)$r['score_total'];
            }
        }

        $existing = $this->where('student_id', $studentId)
                         ->where('test_id', $testId)
                         ->first();

        if ($existing) {
            $this->update($existing['id'], [
                'total_correct'   => $totalCorrect,
                'total_attempted' => $totalAttempted,
                'last_updated_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            if ($totalAttempted > 0) {
                $this->insert([
                    'student_id'      => $studentId,
                    'test_id'         => $testId,
                    'total_correct'   => $totalCorrect,
                    'total_attempted' => $totalAttempted,
                    'last_updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
    }

    /**
     * Recalculate accumulated scores for all students in a specific session.
     */
    public function recalculateSessionScores(int $sessionId)
    {
        $db = \Config\Database::connect();
        $session = $db->table('test_sessions')->where('id', $sessionId)->get()->getRowArray();
        if (!$session) {
            return;
        }

        $participants = $db->table('session_participants')
                           ->where('session_id', $sessionId)
                           ->get()
                           ->getResultArray();

        foreach ($participants as $p) {
            $this->recalculateStudentScore((int)$p['student_id'], (int)$session['test_id']);
        }
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
