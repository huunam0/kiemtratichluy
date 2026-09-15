<?php

namespace App\Models;

use CodeIgniter\Model;

class FillBlankParticipantModel extends Model
{
    protected $table            = 'fill_blank_participants';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'session_id', 'student_id', 'approval_status', 'test_status',
        'score_correct', 'score_total', 'score_base10',
        'joined_at', 'approved_at', 'started_at', 'submitted_at'
    ];

    public function getParticipantsBySession(int $sessionId)
    {
        return $this->select('fill_blank_participants.*, users.full_name as student_name, users.username')
                    ->join('users', 'users.id = fill_blank_participants.student_id')
                    ->where('fill_blank_participants.session_id', $sessionId)
                    ->orderBy('users.full_name', 'ASC')
                    ->findAll();
    }
}
