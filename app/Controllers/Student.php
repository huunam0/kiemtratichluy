<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ClassModel;
use App\Models\ClassStudentModel;
use App\Models\TestModel;
use App\Models\TestQuestionPoolModel;
use App\Models\TestSessionModel;
use App\Models\SessionParticipantModel;
use App\Models\SessionQuestionAnswerModel;
use App\Models\AccumulatedScoreModel;
use App\Models\MockTestLogModel;
use App\Services\OptionShuffleService;

class Student extends BaseController
{
    protected $userModel;
    protected $classModel;
    protected $classStudentModel;
    protected $testModel;
    protected $poolModel;
    protected $sessionModel;
    protected $participantModel;
    protected $sqaModel;
    protected $accumulatedModel;
    protected $mockLogModel;
    protected $shuffleService;

    public function __construct()
    {
        $this->userModel         = new UserModel();
        $this->classModel        = new ClassModel();
        $this->classStudentModel = new ClassStudentModel();
        $this->testModel        = new TestModel();
        $this->poolModel        = new TestQuestionPoolModel();
        $this->sessionModel     = new TestSessionModel();
        $this->participantModel = new SessionParticipantModel();
        $this->sqaModel         = new SessionQuestionAnswerModel();
        $this->accumulatedModel = new AccumulatedScoreModel();
        $this->mockLogModel     = new MockTestLogModel();
        $this->shuffleService   = new OptionShuffleService();
    }

    public function dashboard()
    {
        $studentId = session()->get('user_id');
        $classes   = $this->classStudentModel->getStudentClasses($studentId);

        $activeSessions = [];
        $accumulatedScores = [];

        foreach ($classes as $cls) {
            $sessList = $this->sessionModel->getActiveSessionsForStudent($cls['id']);
            foreach ($sessList as &$sess) {
                $part = $this->participantModel->where('session_id', $sess['id'])
                                               ->where('student_id', $studentId)
                                               ->first();
                $sess['my_participant'] = $part;
            }
            unset($sess);
            $activeSessions = array_merge($activeSessions, $sessList);

            // Fetch tests for class and accumulated scores
            $tests = $this->testModel->getTestsByClass($cls['id']);
            foreach ($tests as $t) {
                $acc = $this->accumulatedModel->where('student_id', $studentId)
                                              ->where('test_id', $t['id'])
                                              ->first();
                $t['accumulated'] = $acc;
                $accumulatedScores[] = $t;
            }
        }

        return view('student/dashboard', [
            'classes'            => $classes,
            'active_sessions'    => $activeSessions,
            'accumulated_scores' => $accumulatedScores,
        ]);
    }

    // --------------------------------------------------------------------
    // REAL TEST: JOIN & WAITING ROOM (STRICTLY 1 ATTEMPT PER SESSION)
    // --------------------------------------------------------------------
    public function joinSession(int $sessionId)
    {
        $studentId = session()->get('user_id');
        $session   = $this->sessionModel->find($sessionId);

        if (!$session || $session['status'] === 'completed' || $session['status'] === 'cancelled') {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Lượt thi này đã đóng hoặc không còn tồn tại.');
        }

        // Check if participant record exists
        $part = $this->participantModel->where('session_id', $sessionId)
                                       ->where('student_id', $studentId)
                                       ->first();

        if ($part) {
            // Strictly 1 attempt check
            if ($part['test_status'] === 'submitted' || $part['test_status'] === 'timed_out') {
                return redirect()->to(base_url("student/exam-result/{$part['id']}"))
                                 ->with('error', 'Bạn đã hoàn thành lượt thi này rồi. Mỗi lượt thi chỉ được tham gia 1 lần!');
            }
            if ($part['test_status'] === 'in_test') {
                return redirect()->to(base_url("student/exam/{$part['id']}"));
            }
            if ($part['approval_status'] === 'absent') {
                return redirect()->to(base_url('student/dashboard'))
                                 ->with('error', 'Bạn đã bị giáo viên đánh vắng trong lượt thi này.');
            }
        } else {
            // New participant joining
            $partId = $this->participantModel->insert([
                'session_id'      => $sessionId,
                'student_id'      => $studentId,
                'approval_status' => 'pending',
                'test_status'     => 'waiting_approval',
                'joined_at'       => date('Y-m-d H:i:s'),
            ]);
            $part = $this->participantModel->find($partId);
        }

        return view('student/waiting_room', [
            'session'     => $session,
            'participant' => $part,
        ]);
    }

    public function checkApprovalStatus(int $participantId)
    {
        $part = $this->participantModel->find($participantId);
        if (!$part) {
            return $this->response->setJSON(['status' => 'not_found']);
        }

        $session = $this->sessionModel->find($part['session_id']);

        return $this->response->setJSON([
            'approval_status' => $part['approval_status'],
            'test_status'     => $part['test_status'],
            'session_status'  => $session['status'],
        ]);
    }

    // --------------------------------------------------------------------
    // REAL TEST TAKING FLOW (MOBILE-FIRST UI)
    // --------------------------------------------------------------------
    public function startExam(int $participantId)
    {
        $studentId = session()->get('user_id');
        $part      = $this->participantModel->find($participantId);

        if (!$part || (int)$part['student_id'] !== $studentId) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Không tìm thấy lượt thi.');
        }

        if ($part['approval_status'] !== 'approved') {
            return redirect()->to(base_url("student/waiting-room/{$part['session_id']}"))->with('error', 'Chờ giáo viên phê duyệt.');
        }

        if ($part['test_status'] === 'submitted' || $part['test_status'] === 'timed_out') {
            return redirect()->to(base_url("student/exam-result/{$participantId}"));
        }

        $session = $this->sessionModel->find($part['session_id']);

        // Check if student selected number of questions N (3 to 6)
        $numQuestions = (int)($this->request->getGet('num_questions') ?? $this->request->getPost('num_questions') ?? 5);
        $numQuestions = max(3, min(6, $numQuestions));

        // Generate N questions if not already generated
        $existingQuestions = $this->sqaModel->getParticipantQuestions($participantId);

        if (empty($existingQuestions)) {
            $poolQuestions = $this->poolModel->getRandomQuestionsFromPool($session['test_id'], $numQuestions);

            if (empty($poolQuestions)) {
                return redirect()->to(base_url('student/dashboard'))->with('error', 'Kho câu hỏi của bài kiểm tra chưa có câu hỏi nào.');
            }

            foreach ($poolQuestions as $order => $q) {
                // Generate Dynamic Shuffled Options (2, 3, or 4 options)
                $shuffled = $this->shuffleService->generateDynamicShuffledOptions($q);

                $this->sqaModel->insert([
                    'participant_id' => $participantId,
                    'question_id'    => $q['id'],
                    'question_order' => $order + 1,
                    'option_mapping' => json_encode($shuffled['option_mapping']),
                ]);
            }

            // Update participant status & store student selected total questions N
            $this->participantModel->update($participantId, [
                'test_status' => 'in_test',
                'started_at'  => date('Y-m-d H:i:s'),
                'score_total' => count($poolQuestions),
            ]);

            $existingQuestions = $this->sqaModel->getParticipantQuestions($participantId);
            $part['score_total'] = count($poolQuestions);
        }

        // Format questions with shuffled display options for JS
        $questionsData = [];
        foreach ($existingQuestions as $q) {
            $mapping = json_decode($q['option_mapping'], true);
            $displayOptions = [];

            foreach ($mapping as $displayLabel => $origKey) {
                $colName = 'option_' . strtolower($origKey);
                $displayOptions[] = [
                    'label'   => $displayLabel,
                    'content' => $q[$colName],
                ];
            }

            $questionsData[] = [
                'id'              => (int)$q['id'], // sqa record ID
                'question_id'     => (int)$q['question_id'],
                'question_order'  => (int)$q['question_order'],
                'content'         => $q['content'],
                'display_options' => $displayOptions,
                'selected_option' => $q['selected_option'],
            ];
        }

        // Calculate total countdown time based on student selected N (N * 30 seconds)
        $totalQuestions = (int)$part['score_total'] > 0 ? (int)$part['score_total'] : count($existingQuestions);
        $totalSeconds = $totalQuestions * 30;

        $elapsed = 0;
        if (!empty($part['started_at'])) {
            $elapsed = time() - strtotime($part['started_at']);
        }
        $remainingSeconds = max(0, $totalSeconds - $elapsed);

        return view('student/exam_taking', [
            'session'           => $session,
            'participant'       => $part,
            'questions_json'    => json_encode($questionsData),
            'remaining_seconds' => $remainingSeconds,
        ]);
    }

    public function saveAnswer()
    {
        $sqaId          = (int)$this->request->getPost('sqa_id');
        $selectedOption = (string)$this->request->getPost('selected_option'); // 'A', 'B', 'C', 'D'

        $sqa = $this->sqaModel->find($sqaId);
        if ($sqa) {
            $mapping = json_decode($sqa['option_mapping'], true);
            $origSelected = isset($mapping[$selectedOption]) ? $mapping[$selectedOption] : null;

            $this->sqaModel->update($sqaId, [
                'selected_option'          => $selectedOption,
                'original_selected_option' => $origSelected,
                'answered_at'              => date('Y-m-d H:i:s'),
            ]);

            return $this->response->setJSON(['status' => 'success']);
        }

        return $this->response->setJSON(['status' => 'error'], 400);
    }

    public function submitExam(int $participantId)
    {
        $studentId = session()->get('user_id');
        $part      = $this->participantModel->find($participantId);

        if (!$part || (int)$part['student_id'] !== $studentId) {
            return redirect()->to(base_url('student/dashboard'));
        }

        if ($part['test_status'] === 'submitted' || $part['test_status'] === 'timed_out') {
            return redirect()->to(base_url("student/exam-result/{$participantId}"));
        }

        $session = $this->sessionModel->find($part['session_id']);
        $questions = $this->sqaModel->getParticipantQuestions($participantId);

        $scoreCorrect = 0;
        $totalQuestions = count($questions);

        foreach ($questions as $q) {
            $mapping = json_decode($q['option_mapping'], true);
            $selectedDisplay = $q['selected_option'];

            $isCorrect = 0;
            if ($selectedDisplay && isset($mapping[$selectedDisplay])) {
                $origSelected = $mapping[$selectedDisplay];
                if ($origSelected === $q['correct_option']) {
                    $isCorrect = 1;
                    $scoreCorrect++;
                }
            }

            $this->sqaModel->update($q['id'], [
                'is_correct' => $isCorrect,
            ]);
        }

        $scoreBase10 = ($totalQuestions > 0) ? round(($scoreCorrect / $totalQuestions) * 10, 2) : 0.00;

        // 1. Update session participant record
        $this->participantModel->update($participantId, [
            'test_status'   => 'submitted',
            'score_correct' => $scoreCorrect,
            'score_total'   => $totalQuestions,
            'score_base10'  => $scoreBase10,
            'submitted_at'  => date('Y-m-d H:i:s'),
        ]);

        // 2. Incremental Update to Accumulated Score Table: (Total Correct / Total Attempted)
        $this->accumulatedModel->recordTestScore($studentId, $session['test_id'], $scoreCorrect, $totalQuestions);

        return redirect()->to(base_url("student/exam-result/{$participantId}"));
    }

    public function examResult(int $participantId)
    {
        $part = $this->participantModel->find($participantId);
        if (!$part) {
            return redirect()->to(base_url('student/dashboard'));
        }

        $session   = $this->sessionModel->find($part['session_id']);
        $questions = $this->sqaModel->getParticipantQuestions($participantId);

        // Prepare details for student view
        $detailedQuestions = [];
        foreach ($questions as $q) {
            $mapping = json_decode($q['option_mapping'], true);
            $displayOptions = [];

            foreach ($mapping as $displayLabel => $origKey) {
                $colName = 'option_' . strtolower($origKey);
                $displayOptions[] = [
                    'label'      => $displayLabel,
                    'content'    => $q[$colName],
                    'is_correct' => ($origKey === $q['correct_option']),
                ];
            }

            $detailedQuestions[] = [
                'sqa'             => $q,
                'display_options' => $displayOptions,
            ];
        }

        return view('student/exam_result', [
            'session'     => $session,
            'participant' => $part,
            'questions'   => $detailedQuestions,
        ]);
    }

    // --------------------------------------------------------------------
    // MOCK TEST (KIỂM TRA THỬ - STUDENT CHOOSES 3 TO 6 QUESTIONS, 5s COUNTDOWN, FREE PRACTICE)
    // --------------------------------------------------------------------
    public function mockTest(int $testId)
    {
        $test = $this->testModel->find($testId);
        if (!$test) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Bài kiểm tra không tồn tại.');
        }

        $numQuestions = (int)($this->request->getGet('num_questions') ?? $this->request->getPost('num_questions') ?? 0);

        if ($numQuestions < 3 || $numQuestions > 6) {
            return view('student/mock_test', [
                'test'           => $test,
                'select_mode'    => true,
                'mock_data_json' => json_encode([]),
            ]);
        }

        $questions = $this->poolModel->getRandomQuestionsFromPool($testId, $numQuestions);
        if (empty($questions)) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Kho câu hỏi của bài kiểm tra chưa có câu hỏi nào.');
        }

        $mockData = [];
        foreach ($questions as $order => $q) {
            $shuffled = $this->shuffleService->generateDynamicShuffledOptions($q);
            $mockData[] = [
                'order'           => $order + 1,
                'content'         => $q['content'],
                'display_options' => $shuffled['display_options'],
                'option_mapping'  => $shuffled['option_mapping'],
                'correct_option'  => $q['correct_option'],
                'explanation'     => $q['explanation'],
            ];
        }

        return view('student/mock_test', [
            'test'           => $test,
            'select_mode'    => false,
            'num_questions'  => count($questions),
            'mock_data_json' => json_encode($mockData),
        ]);
    }

    public function submitMockTest()
    {
        $studentId = session()->get('user_id');
        $testId    = (int)$this->request->getPost('test_id');
        $correct   = (int)$this->request->getPost('score_correct');
        $total     = (int)$this->request->getPost('score_total');

        if ($studentId && $testId) {
            $this->mockLogModel->insert([
                'student_id'      => $studentId,
                'test_id'         => $testId,
                'num_questions'   => $total,
                'score_correct'   => $correct,
                'total_questions' => $total,
                'completed_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->response->setJSON(['status' => 'success']);
    }
}
