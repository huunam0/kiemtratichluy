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
use App\Models\FillBlankQuizModel;
use App\Models\FillBlankQuestionModel;
use App\Models\FillBlankQuestionVariantModel;
use App\Models\FillBlankSessionModel;
use App\Models\FillBlankParticipantModel;
use App\Models\FillBlankResultModel;
use App\Libraries\FillBlankEngine;
use App\Models\ShortAnswerQuizModel;
use App\Models\ShortAnswerQuizQuestionModel;
use App\Models\ShortAnswerAcceptedAnswerModel;
use App\Models\ShortAnswerSessionModel;
use App\Models\ShortAnswerSubmissionModel;
use App\Libraries\ShortAnswerEngine;

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
    protected $fillBlankQuizModel;
    protected $fillBlankQuestionModel;
    protected $fillBlankVariantModel;
    protected $fillBlankSessionModel;
    protected $fillBlankParticipantModel;
    protected $fillBlankResultModel;
    protected $fillBlankEngine;
    protected $shortAnswerQuizModel;
    protected $shortAnswerQuizQuestionModel;
    protected $shortAnswerAcceptedModel;
    protected $shortAnswerSessionModel;
    protected $shortAnswerSubmissionModel;
    protected $shortAnswerEngine;

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

        $this->fillBlankQuizModel        = new FillBlankQuizModel();
        $this->fillBlankQuestionModel    = new FillBlankQuestionModel();
        $this->fillBlankVariantModel     = new FillBlankQuestionVariantModel();
        $this->fillBlankSessionModel     = new FillBlankSessionModel();
        $this->fillBlankParticipantModel = new FillBlankParticipantModel();
        $this->fillBlankResultModel      = new FillBlankResultModel();
        $this->fillBlankEngine           = new FillBlankEngine();

        $this->shortAnswerQuizModel         = new ShortAnswerQuizModel();
        $this->shortAnswerQuizQuestionModel = new ShortAnswerQuizQuestionModel();
        $this->shortAnswerAcceptedModel     = new ShortAnswerAcceptedAnswerModel();
        $this->shortAnswerSessionModel      = new ShortAnswerSessionModel();
        $this->shortAnswerSubmissionModel   = new ShortAnswerSubmissionModel();
        $this->shortAnswerEngine            = new ShortAnswerEngine();
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
                $t['accumulated']   = $acc;
                $t['min_questions'] = (int)($t['min_questions'] ?? 20);
                // Calculate temp GPA for display
                $totalAttempted     = $acc ? (int)$acc['total_attempted'] : 0;
                $totalCorrect       = $acc ? (int)$acc['total_correct']   : 0;
                $t['temp_gpa']      = ($totalAttempted > 0)
                    ? round($totalCorrect / $totalAttempted * 10, 2)
                    : null;
                $accumulatedScores[] = $t;
            }
        }

        $markdownQuizModel = new \App\Models\MarkdownQuizModel();
        $practiceQuizzes = [];
        foreach ($classes as $cls) {
            $classQuizzes = $markdownQuizModel->getQuizzesByClass($cls['id']);
            foreach ($classQuizzes as $q) {
                // Avoid duplicates if student is in multiple classes with same quiz (unlikely but safe)
                if (!isset($practiceQuizzes[$q['id']])) {
                    $q['class_name']           = $cls['name'];
                    $practiceQuizzes[$q['id']] = $q;
                }
            }
        }
        $practiceQuizzes = array_values($practiceQuizzes);

        // Fetch Fill-in-the-blank Quizzes & Active Real Sessions
        $schoolId         = session()->get('school_id');
        $fillBlankQuizzes = $this->fillBlankQuizModel->getQuizzesBySchool($schoolId);
        $fillBlankActiveSessions = [];
        foreach ($classes as $cls) {
            $fbSessList = $this->fillBlankSessionModel->getActiveSessionsForStudent($cls['id']);
            foreach ($fbSessList as &$fbSess) {
                $part = $this->fillBlankParticipantModel->where('session_id', $fbSess['id'])
                                                        ->where('student_id', $studentId)
                                                        ->first();
                $fbSess['my_participant'] = $part;
            }
            unset($fbSess);
            $fillBlankActiveSessions = array_merge($fillBlankActiveSessions, $fbSessList);
        }

        // Fetch Short Answer Quizzes for Student's classes
        $shortAnswerQuizzes = [];
        foreach ($classes as $cls) {
            $saList = $this->shortAnswerQuizModel->select('short_answer_quizzes.*, users.full_name as teacher_name')
                                                ->join('users', 'users.id = short_answer_quizzes.teacher_id', 'left')
                                                ->where('short_answer_quizzes.class_id', $cls['id'])
                                                ->findAll();
            foreach ($saList as &$sa) {
                $sa['class_name']     = $cls['name'];
                $sa['total_points']   = $this->shortAnswerQuizQuestionModel->getMaxPoints((int)$sa['id']);
                $sa['question_count'] = $this->shortAnswerQuizQuestionModel->where('quiz_id', $sa['id'])->countAllResults();
                
                // Check if current open session has been submitted by student
                $sa['has_submitted']     = false;
                $sa['latest_submission'] = null;
                if ((int)$sa['is_open'] === 1) {
                    $openSess = $this->shortAnswerSessionModel->getLatestOpenSession((int)$sa['id']);
                    if ($openSess) {
                        $sub = $this->shortAnswerSubmissionModel->getBySessionAndStudent((int)$openSess['id'], $studentId);
                        if ($sub) {
                            $sa['has_submitted']     = true;
                            $sa['latest_submission'] = $sub;
                        }
                    }
                }
                $shortAnswerQuizzes[] = $sa;
            }
            unset($sa);
        }

        return view('student/dashboard', [
            'classes'                   => $classes,
            'active_sessions'           => $activeSessions,
            'accumulated_scores'        => $accumulatedScores,
            'practice_quizzes'          => $practiceQuizzes,
            'fill_blank_quizzes'        => $fillBlankQuizzes,
            'fill_blank_active_sessions'=> $fillBlankActiveSessions,
            'short_answer_quizzes'      => $shortAnswerQuizzes,
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

    public function cancelSession(int $participantId)
    {
        $studentId = session()->get('user_id');
        $part = $this->participantModel->find($participantId);

        if (!$part || (int)$part['student_id'] !== (int)$studentId) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Không tìm thấy lượt đăng ký thi.');
        }

        if (in_array($part['test_status'], ['in_test', 'submitted', 'timed_out'])) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Bạn đã bắt đầu hoặc hoàn thành bài thi, không thể hủy yêu cầu.');
        }

        $this->participantModel->delete($participantId);

        return redirect()->to(base_url('student/dashboard'))->with('success', 'Đã rút lại yêu cầu tham gia lượt thi thành công.');
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

        // Check allow_mock permission
        if ((int)($test['allow_mock'] ?? 1) === 0) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Giáo viên đã tắt chức năng kiểm tra thử cho bài kiểm tra này.');
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

    // =========================================================================
    // FILL-IN-THE-BLANKS (TRẮC NGHIỆM ĐIỀN VÀO CHỖ TRỐNG) STUDENT FLOW
    // =========================================================================

    // --- MOCK / PRACTICE TEST (TỰ DO) ---
    public function fillBlankMock(int $quizId)
    {
        $studentId = session()->get('user_id');
        $quiz      = $this->fillBlankQuizModel->find($quizId);

        if (!$quiz) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Bài kiểm tra không tồn tại.');
        }

        // Check allow_mock permission
        if ((int)($quiz['allow_mock'] ?? 1) === 0) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Giáo viên đã tạm khóa chức năng kiểm tra thử cho bài kiểm tra này.');
        }

        $questionIds = json_decode($quiz['selected_question_ids'] ?: '[]', true);
        $questionsData = [];

        foreach ($questionIds as $qId) {
            $qInfo   = $this->fillBlankQuestionModel->find($qId);
            $variant = $this->fillBlankVariantModel->getRandomVariant($qId);
            if ($qInfo && $variant) {
                $parsed = $this->fillBlankEngine->parseQuestion($variant['content_raw']);
                $questionsData[] = [
                    'question'     => $qInfo,
                    'variant_name' => $variant['variant_name'],
                    'html'         => $parsed['html'],
                    'answers'      => $parsed['answers'],
                    'total_blanks' => $parsed['total_blanks'],
                ];
            }
        }

        return view('student/fill_blank/do_quiz', [
            'quiz'           => $quiz,
            'is_mock'        => true,
            'participant'    => null,
            'questionsData'  => $questionsData,
        ]);
    }

    public function submitFillBlankMock(int $quizId)
    {
        $studentId = session()->get('user_id');
        $quiz      = $this->fillBlankQuizModel->find($quizId);

        if (!$quiz) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Bài kiểm tra không tồn tại.');
        }

        // Check allow_mock permission
        if ((int)($quiz['allow_mock'] ?? 1) === 0) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Bài kiểm tra này đã bị khóa kiểm tra thử.');
        }

        $userAnswers = $this->request->getPost('answers') ?: [];
        $correctAnswersJson = $this->request->getPost('correct_answers_json');
        $correctAnswers = json_decode($correctAnswersJson ?: '[]', true);

        $totalBlanks = 0;
        $scoreCorrect = 0;

        foreach ($correctAnswers as $qIdx => $answersList) {
            foreach ($answersList as $bIdx => $cAns) {
                $totalBlanks++;
                $uAns = isset($userAnswers[$qIdx][$bIdx]) ? $userAnswers[$qIdx][$bIdx] : '';
                if ($this->fillBlankEngine->compareAnswer($uAns, $cAns, false, 0)) {
                    $scoreCorrect++;
                }
            }
        }

        $scoreBase10 = $totalBlanks > 0 ? round(($scoreCorrect / $totalBlanks) * 10, 2) : 0;

        $this->fillBlankResultModel->insert([
            'quiz_id'         => $quizId,
            'session_id'      => null,
            'student_id'      => $studentId,
            'is_mock'         => 1,
            'score_correct'   => $scoreCorrect,
            'total_blanks'    => $totalBlanks,
            'score_base10'    => $scoreBase10,
            'student_answers' => json_encode($userAnswers),
            'completed_at'    => date('Y-m-d H:i:s'),
        ]);

        return view('student/fill_blank/result', [
            'quiz'          => $quiz,
            'is_mock'       => true,
            'score_correct' => $scoreCorrect,
            'total_blanks'  => $totalBlanks,
            'score_base10'  => $scoreBase10,
        ]);
    }

    // --- REAL TEST (CẦN GIÁO VIÊN PHÊ DUYỆT) ---
    public function joinFillBlankSession(int $sessionId)
    {
        $studentId = session()->get('user_id');
        $session   = $this->fillBlankSessionModel->find($sessionId);

        if (!$session || $session['status'] === 'completed' || $session['status'] === 'cancelled') {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Phiên kiểm tra này đã kết thúc.');
        }

        $part = $this->fillBlankParticipantModel->where('session_id', $sessionId)
                                                ->where('student_id', $studentId)
                                                ->first();

        if ($part) {
            if ($part['test_status'] === 'submitted' || $part['test_status'] === 'timed_out') {
                return redirect()->to(base_url('student/dashboard'))->with('error', 'Bạn đã hoàn thành lượt thi này rồi.');
            }
            if ($part['approval_status'] === 'approved' && $part['test_status'] === 'in_test') {
                return redirect()->to(base_url("student/fill-blank/exam/{$part['id']}"));
            }
        } else {
            $partId = $this->fillBlankParticipantModel->insert([
                'session_id'      => $sessionId,
                'student_id'      => $studentId,
                'approval_status' => 'pending',
                'test_status'     => 'waiting_approval',
                'joined_at'       => date('Y-m-d H:i:s'),
            ]);
            $part = $this->fillBlankParticipantModel->find($partId);
        }

        return redirect()->to(base_url("student/fill-blank/waiting-room/{$part['id']}"));
    }

    public function fillBlankWaitingRoom(int $participantId)
    {
        $part = $this->fillBlankParticipantModel->find($participantId);
        if (!$part) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Không tìm thấy thông tin đăng ký thi.');
        }

        $session = $this->fillBlankSessionModel->find($part['session_id']);
        $quiz    = $this->fillBlankQuizModel->find($session['quiz_id']);

        if ($part['approval_status'] === 'approved') {
            return redirect()->to(base_url("student/fill-blank/exam/{$part['id']}"));
        }

        return view('student/fill_blank/waiting_room', [
            'participant' => $part,
            'session'     => $session,
            'quiz'        => $quiz,
        ]);
    }

    public function checkFillBlankApproval(int $participantId)
    {
        $part = $this->fillBlankParticipantModel->find($participantId);
        if (!$part) {
            return $this->response->setJSON(['approval_status' => 'not_found']);
        }

        return $this->response->setJSON([
            'approval_status' => $part['approval_status'],
            'test_status'     => $part['test_status'],
        ]);
    }

    public function cancelFillBlankSession(int $participantId)
    {
        $studentId = session()->get('user_id');
        $part = $this->fillBlankParticipantModel->find($participantId);

        if (!$part || (int)$part['student_id'] !== (int)$studentId) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Không tìm thấy lượt đăng ký thi.');
        }

        if (in_array($part['test_status'], ['in_test', 'submitted', 'timed_out'])) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Bạn đã bắt đầu hoặc hoàn thành bài thi, không thể hủy yêu cầu.');
        }

        $this->fillBlankParticipantModel->delete($participantId);

        return redirect()->to(base_url('student/dashboard'))->with('success', 'Đã rút lại yêu cầu tham gia lượt thi điền vào chỗ trống.');
    }

    public function startFillBlankExam(int $participantId)
    {
        $studentId = session()->get('user_id');
        $part      = $this->fillBlankParticipantModel->find($participantId);

        if (!$part || (int)$part['student_id'] !== $studentId || $part['approval_status'] !== 'approved') {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Bạn chưa được giáo viên phê duyệt vào thi.');
        }

        if ($part['test_status'] === 'submitted' || $part['test_status'] === 'timed_out') {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Bạn đã nộp bài thi này rồi.');
        }

        $session = $this->fillBlankSessionModel->find($part['session_id']);
        $quiz    = $this->fillBlankQuizModel->find($session['quiz_id']);

        $questionIds = json_decode($quiz['selected_question_ids'] ?: '[]', true);
        $questionsData = [];

        foreach ($questionIds as $qId) {
            $qInfo   = $this->fillBlankQuestionModel->find($qId);
            $variant = $this->fillBlankVariantModel->getRandomVariant($qId);
            if ($qInfo && $variant) {
                $parsed = $this->fillBlankEngine->parseQuestion($variant['content_raw']);
                $questionsData[] = [
                    'question'     => $qInfo,
                    'variant_name' => $variant['variant_name'],
                    'html'         => $parsed['html'],
                    'answers'      => $parsed['answers'],
                    'total_blanks' => $parsed['total_blanks'],
                ];
            }
        }

        return view('student/fill_blank/do_quiz', [
            'quiz'          => $quiz,
            'is_mock'       => false,
            'participant'   => $part,
            'questionsData' => $questionsData,
        ]);
    }

    public function submitFillBlankExam(int $participantId)
    {
        $studentId = session()->get('user_id');
        $part      = $this->fillBlankParticipantModel->find($participantId);

        if (!$part || (int)$part['student_id'] !== $studentId) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Thông tin tham gia thi không hợp lệ.');
        }

        $session = $this->fillBlankSessionModel->find($part['session_id']);
        $quiz    = $this->fillBlankQuizModel->find($session['quiz_id']);

        $userAnswers = $this->request->getPost('answers') ?: [];
        $correctAnswersJson = $this->request->getPost('correct_answers_json');
        $correctAnswers = json_decode($correctAnswersJson ?: '[]', true);

        $totalBlanks = 0;
        $scoreCorrect = 0;

        foreach ($correctAnswers as $qIdx => $answersList) {
            foreach ($answersList as $bIdx => $cAns) {
                $totalBlanks++;
                $uAns = isset($userAnswers[$qIdx][$bIdx]) ? $userAnswers[$qIdx][$bIdx] : '';
                if ($this->fillBlankEngine->compareAnswer($uAns, $cAns, false, 0)) {
                    $scoreCorrect++;
                }
            }
        }

        $scoreBase10 = $totalBlanks > 0 ? round(($scoreCorrect / $totalBlanks) * 10, 2) : 0;

        $this->fillBlankParticipantModel->update($participantId, [
            'test_status'   => 'submitted',
            'score_correct' => $scoreCorrect,
            'score_total'   => $totalBlanks,
            'score_base10'  => $scoreBase10,
            'submitted_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->fillBlankResultModel->insert([
            'quiz_id'         => $quiz['id'],
            'session_id'      => $session['id'],
            'student_id'      => $studentId,
            'is_mock'         => 0,
            'score_correct'   => $scoreCorrect,
            'total_blanks'    => $totalBlanks,
            'score_base10'    => $scoreBase10,
            'student_answers' => json_encode($userAnswers),
            'completed_at'    => date('Y-m-d H:i:s'),
        ]);

        $existingAcc = $this->accumulatedModel->where('student_id', $studentId)
                                              ->where('class_id', $session['class_id'])
                                              ->first();
        if ($existingAcc) {
            $this->accumulatedModel->update($existingAcc['id'], [
                'score' => $existingAcc['score'] + $scoreBase10,
            ]);
        } else {
            $this->accumulatedModel->insert([
                'student_id' => $studentId,
                'class_id'   => $session['class_id'],
                'test_id'    => $quiz['id'],
                'score'      => $scoreBase10,
            ]);
        }

        return view('student/fill_blank/result', [
            'quiz'          => $quiz,
            'is_mock'       => false,
            'score_correct' => $scoreCorrect,
            'total_blanks'  => $totalBlanks,
            'score_base10'  => $scoreBase10,
        ]);
    }

    // =========================================================================
    // HỌC SINH: LÀM BÀI TRẢ LỜI NGẮN (SHORT ANSWER)
    // =========================================================================

    public function shortAnswerExam(int $quizId)
    {
        $studentId = session()->get('user_id');
        $quiz = $this->shortAnswerQuizModel->find($quizId);

        if (!$quiz) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Bài kiểm tra không tồn tại.');
        }

        // Kiểm tra học sinh có thuộc lớp được giao không
        if (!empty($quiz['class_id'])) {
            $inClass = $this->classStudentModel->where('student_id', $studentId)->where('class_id', $quiz['class_id'])->first();
            if (!$inClass) {
                return redirect()->to(base_url('student/dashboard'))->with('error', 'Bạn không thuộc lớp được giao bài kiểm tra này.');
            }
        }

        if ((int)$quiz['is_open'] !== 1) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Bài kiểm tra hiện đang đóng. Vui lòng chờ giáo viên mở bài.');
        }

        $openSession = $this->shortAnswerSessionModel->getLatestOpenSession($quizId);
        if (!$openSession) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Không tìm thấy phiên làm bài đang mở.');
        }

        // Kiểm tra học sinh đã nộp bài trong session này chưa
        $existing = $this->shortAnswerSubmissionModel->getBySessionAndStudent((int)$openSession['id'], $studentId);
        if ($existing) {
            return redirect()->to(base_url("student/short-answer/result/{$existing['id']}"))->with('info', 'Bạn đã nộp bài trong lượt kiểm tra này.');
        }

        $allQuestions = $this->shortAnswerQuizQuestionModel->getQuestionsWithContent($quizId);
        if (empty($allQuestions)) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Đề kiểm tra chưa có câu hỏi nào.');
        }

        // Phân phối câu hỏi: nhóm câu hỏi tương đương chỉ lấy ngẫu nhiên 1 câu cho học sinh này
        $sessionKey  = "sa_assigned_qids_{$openSession['id']}_{$studentId}";
        $assignedIds = session()->get($sessionKey);

        if (!empty($assignedIds) && is_array($assignedIds)) {
            $questions = array_values(array_filter($allQuestions, function($q) use ($assignedIds) {
                return in_array((int)$q['question_id'], $assignedIds);
            }));
        } else {
            $questions = $this->pickEquivalentQuestions($allQuestions);
            $assignedIds = array_column($questions, 'question_id');
            session()->set($sessionKey, $assignedIds);
        }

        if ((int)$quiz['shuffle_questions'] === 1) {
            shuffle($questions);
        }

        // Điểm tối đa của đề được tính theo các câu hỏi mà học sinh nhận được
        $totalPoints = array_sum(array_column($questions, 'points'));

        return view('student/short_answer/do_quiz', [
            'quiz'        => $quiz,
            'session'     => $openSession,
            'questions'   => $questions,
            'is_mock'     => false,
            'totalPoints' => $totalPoints,
            'timeLimit'   => (int)$quiz['time_limit'],
        ]);
    }

    public function submitShortAnswerExam(int $sessionId)
    {
        $studentId = session()->get('user_id');
        $session   = $this->shortAnswerSessionModel->find($sessionId);

        if (!$session) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Phiên kiểm tra không tồn tại.');
        }

        $quiz = $this->shortAnswerQuizModel->find($session['quiz_id']);
        if (!$quiz) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Bài kiểm tra không tồn tại.');
        }

        // Kiểm tra đã nộp chưa
        $existing = $this->shortAnswerSubmissionModel->getBySessionAndStudent($sessionId, $studentId);
        if ($existing) {
            return redirect()->to(base_url("student/short-answer/result/{$existing['id']}"))->with('info', 'Bạn đã nộp bài rồi.');
        }

        $allQuizQuestions = $this->shortAnswerQuizQuestionModel->getQuestionsWithContent($quiz['id']);
        $userAnswers      = (array)($this->request->getPost('answers') ?? []);

        // Lấy đúng danh sách câu hỏi đã giao cho học sinh
        $sessionKey  = "sa_assigned_qids_{$sessionId}_{$studentId}";
        $assignedIds = session()->get($sessionKey) ?: array_map('intval', array_keys($userAnswers));

        $quizQuestions = array_values(array_filter($allQuizQuestions, function($qq) use ($assignedIds) {
            return in_array((int)$qq['question_id'], $assignedIds);
        }));

        if (empty($quizQuestions)) {
            $quizQuestions = $allQuizQuestions;
        }

        // Lấy danh sách đáp án được chấp nhận cho từng câu
        $acceptedAnswersMap = [];
        foreach ($quizQuestions as $qq) {
            $qId = (int)$qq['question_id'];
            $acceptedAnswersMap[$qId] = $this->shortAnswerAcceptedModel->getAnswersByQuestion($qId);
        }

        $grade = $this->shortAnswerEngine->gradeSubmission($quizQuestions, $acceptedAnswersMap, $userAnswers);

        $subId = $this->shortAnswerSubmissionModel->insert([
            'session_id'       => $sessionId,
            'quiz_id'          => $quiz['id'],
            'student_id'       => $studentId,
            'is_mock'          => 0,
            'student_answers'  => json_encode($grade['results']),
            'raw_score'        => $grade['raw_score'],
            'final_score'      => $grade['raw_score'],
            'penalty_deducted' => 0.00,
            'submitted_at'     => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url("student/short-answer/result/{$subId}"))->with('success', 'Nộp bài kiểm tra thành công!');
    }

    public function shortAnswerResult(int $submissionId)
    {
        $studentId  = session()->get('user_id');
        $submission = $this->shortAnswerSubmissionModel->find($submissionId);

        if (!$submission || (int)$submission['student_id'] !== $studentId) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Không tìm thấy kết quả làm bài.');
        }

        $quiz = $this->shortAnswerQuizModel->find($submission['quiz_id']);
        $questionResults = json_decode($submission['student_answers'] ?? '[]', true) ?: [];

        // Tính tổng điểm max của bài làm từ kết quả các câu đã làm
        $totalPoints = 0.0;
        foreach ($questionResults as $qr) {
            $totalPoints += (float)($qr['max_points'] ?? 0);
        }
        if ($totalPoints <= 0) {
            $totalPoints = $this->shortAnswerQuizQuestionModel->getMaxPoints((int)$submission['quiz_id']);
        }

        $quizQuestions = $this->shortAnswerQuizQuestionModel->getQuestionsWithContent((int)$submission['quiz_id']);
        $questionInfoMap = [];
        foreach ($quizQuestions as $qq) {
            $questionInfoMap[(int)$qq['question_id']] = $qq;
        }

        return view('student/short_answer/result', [
            'submission'      => $submission,
            'quiz'            => $quiz,
            'totalPoints'     => $totalPoints,
            'questionResults' => $questionResults,
            'questionInfoMap' => $questionInfoMap,
        ]);
    }

    public function shortAnswerMock(int $quizId)
    {
        $studentId = session()->get('user_id');
        $quiz = $this->shortAnswerQuizModel->find($quizId);

        if (!$quiz || (int)$quiz['allow_mock'] !== 1) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Bài kiểm tra không cho phép thi thử.');
        }

        $allQuestions = $this->shortAnswerQuizQuestionModel->getQuestionsWithContent($quizId);
        if (empty($allQuestions)) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Đề kiểm tra chưa có câu hỏi nào.');
        }

        // Thi thử: bốc ngẫu nhiên 1 câu cho mỗi nhóm tương đương
        $questions = $this->pickEquivalentQuestions($allQuestions);
        session()->set("sa_mock_assigned_qids_{$quizId}_{$studentId}", array_column($questions, 'question_id'));

        if ((int)$quiz['shuffle_questions'] === 1) {
            shuffle($questions);
        }

        $totalPoints = array_sum(array_column($questions, 'points'));

        return view('student/short_answer/do_quiz', [
            'quiz'        => $quiz,
            'session'     => null,
            'questions'   => $questions,
            'is_mock'     => true,
            'totalPoints' => $totalPoints,
            'timeLimit'   => (int)$quiz['time_limit'],
        ]);
    }

    public function submitShortAnswerMock(int $quizId)
    {
        $studentId = session()->get('user_id');
        $quiz = $this->shortAnswerQuizModel->find($quizId);

        if (!$quiz) {
            return redirect()->to(base_url('student/dashboard'))->with('error', 'Bài kiểm tra không tồn tại.');
        }

        $allQuizQuestions = $this->shortAnswerQuizQuestionModel->getQuestionsWithContent($quiz['id']);
        $userAnswers      = (array)($this->request->getPost('answers') ?? []);

        $sessionKey  = "sa_mock_assigned_qids_{$quizId}_{$studentId}";
        $assignedIds = session()->get($sessionKey) ?: array_map('intval', array_keys($userAnswers));

        $quizQuestions = array_values(array_filter($allQuizQuestions, function($qq) use ($assignedIds) {
            return in_array((int)$qq['question_id'], $assignedIds);
        }));

        if (empty($quizQuestions)) {
            $quizQuestions = $allQuizQuestions;
        }

        $acceptedAnswersMap = [];
        foreach ($quizQuestions as $qq) {
            $qId = (int)$qq['question_id'];
            $acceptedAnswersMap[$qId] = $this->shortAnswerAcceptedModel->getAnswersByQuestion($qId);
        }

        $grade = $this->shortAnswerEngine->gradeSubmission($quizQuestions, $acceptedAnswersMap, $userAnswers);

        $subId = $this->shortAnswerSubmissionModel->insert([
            'session_id'       => null,
            'quiz_id'          => $quiz['id'],
            'student_id'       => $studentId,
            'is_mock'          => 1,
            'student_answers'  => json_encode($grade['results']),
            'raw_score'        => $grade['raw_score'],
            'final_score'      => $grade['raw_score'],
            'penalty_deducted' => 0.00,
            'submitted_at'     => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url("student/short-answer/result/{$subId}"))->with('success', 'Nộp bài thi thử thành công!');
    }

    /**
     * Lọc danh sách câu hỏi: Trong các câu cùng nhóm tương đương (equivalent_id), chỉ chọn ngẫu nhiên 1 câu.
     */
    private function pickEquivalentQuestions(array $allQuestions): array
    {
        $grouped = [];
        foreach ($allQuestions as $q) {
            $equivKey = !empty($q['equivalent_id']) ? (int)$q['equivalent_id'] : ('q_' . $q['question_id']);
            $grouped[$equivKey][] = $q;
        }

        $selected = [];
        foreach ($grouped as $qList) {
            // Chọn ngẫu nhiên 1 câu trong nhóm tương đương này
            $selected[] = $qList[array_rand($qList)];
        }

        return $selected;
    }
}
