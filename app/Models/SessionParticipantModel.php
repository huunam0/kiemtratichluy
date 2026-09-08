<?php

namespace App\Models;

use CodeIgniter\Model;

class SessionParticipantModel extends Model
{
    protected $table            = 'session_participants';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'session_id', 'student_id', 'approval_status', 'test_status', 
        'score_correct', 'score_total', 'score_base10', 
        'joined_at', 'approved_at', 'started_at', 'submitted_at'
    ];

    public function getParticipantsBySession(int $sessionId)
    {
        return $this->select('session_participants.*, users.full_name as student_name, users.username')
                    ->join('users', 'users.id = session_participants.student_id')
                    ->where('session_participants.session_id', $sessionId)
                    ->orderBy('users.full_name', 'ASC')
                    ->findAll();
    }

    public function getSessionLeaderboard(int $sessionId)
    {
        return $this->select('session_participants.*, users.full_name as student_name')
                    ->join('users', 'users.id = session_participants.student_id')
                    ->where('session_participants.session_id', $sessionId)
                    ->orderBy('session_participants.score_base10', 'DESC')
                    ->orderBy('session_participants.score_correct', 'DESC')
                    ->orderBy('session_participants.submitted_at', 'ASC')
                    ->findAll();
    }

    /**
     * Count real test sessions a student has participated in for a specific test.
     * Counts sessions where student submitted or was marked absent.
     */
    public function countRealTestsByStudent(int $studentId, int $testId): int
    {
        return (int) $this->join('test_sessions', 'test_sessions.id = session_participants.session_id')
                          ->where('test_sessions.test_id', $testId)
                          ->where('session_participants.student_id', $studentId)
                          ->groupStart()
                              ->where('session_participants.test_status', 'submitted')
                              ->orWhere('session_participants.approval_status', 'absent')
                          ->groupEnd()
                          ->countAllResults();
    }
}
