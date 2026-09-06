<?php

namespace App\Controllers;

use App\Models\TestSessionModel;
use App\Models\SessionParticipantModel;
use App\Models\AccumulatedScoreModel;

class Api extends BaseController
{
    protected $sessionModel;
    protected $participantModel;
    protected $accumulatedModel;

    public function __construct()
    {
        $this->sessionModel     = new TestSessionModel();
        $this->participantModel = new SessionParticipantModel();
        $this->accumulatedModel = new AccumulatedScoreModel();
    }

    public function getSessionParticipants(int $sessionId)
    {
        $session = $this->sessionModel->find($sessionId);
        if (!$session) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Lượt thi không tồn tại'], 404);
        }

        $participants = $this->participantModel->getParticipantsBySession($sessionId);

        return $this->response->setJSON([
            'status'         => 'success',
            'session_status' => $session['status'],
            'participants'   => $participants,
        ]);
    }

    public function getSessionLeaderboard(int $sessionId)
    {
        $session = $this->sessionModel->find($sessionId);
        if (!$session) {
            return $this->response->setJSON(['status' => 'error'], 404);
        }

        $results = $this->participantModel->getSessionLeaderboard($sessionId);

        return $this->response->setJSON([
            'status'  => 'success',
            'results' => $results,
        ]);
    }
}
