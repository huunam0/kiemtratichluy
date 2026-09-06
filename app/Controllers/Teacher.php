<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\SchoolModel;
use App\Models\ClassModel;
use App\Models\QuestionModel;
use App\Models\TestModel;
use App\Models\TestQuestionPoolModel;
use App\Models\TestSessionModel;
use App\Models\SessionParticipantModel;
use App\Models\AccumulatedScoreModel;

class Teacher extends BaseController
{
    protected $userModel;
    protected $schoolModel;
    protected $classModel;
    protected $questionModel;
    protected $testModel;
    protected $poolModel;
    protected $sessionModel;
    protected $participantModel;
    protected $accumulatedModel;

    public function __construct()
    {
        $this->userModel        = new UserModel();
        $this->schoolModel      = new SchoolModel();
        $this->classModel       = new ClassModel();
        $this->questionModel    = new QuestionModel();
        $this->testModel        = new TestModel();
        $this->poolModel        = new TestQuestionPoolModel();
        $this->sessionModel     = new TestSessionModel();
        $this->participantModel = new SessionParticipantModel();
        $this->accumulatedModel = new AccumulatedScoreModel();
    }

    public function dashboard()
    {
        $schoolId = session()->get('school_id');
        $teacherId = session()->get('user_id');

        $data = [
            'classes'          => $this->classModel->getClassesBySchool($schoolId),
            'questions'        => $this->questionModel->getAllGlobalQuestions(),
            'my_questions'     => $this->questionModel->where('creator_id', $teacherId)->countAllResults(),
            'active_sessions'  => $this->sessionModel->where('teacher_id', $teacherId)->whereIn('status', ['waiting', 'in_progress'])->findAll(),
            'pending_students' => $this->userModel->getPendingStudentsBySchool($schoolId),
        ];

        return view('teacher/dashboard', $data);
    }

    public function approveStudent(int $studentId)
    {
        $schoolId = session()->get('school_id');
        $student = $this->userModel->find($studentId);

        if ($student && $student['role'] === 'student' && (int)$student['school_id'] === (int)$schoolId) {
            $this->userModel->update($studentId, ['status' => 'approved']);
            return redirect()->to(base_url('teacher/dashboard'))->with('success', 'Đã phê duyệt tài khoản Học sinh!');
        }

        return redirect()->to(base_url('teacher/dashboard'))->with('error', 'Không thể phê duyệt tài khoản này.');
    }

    public function rejectStudent(int $studentId)
    {
        $schoolId = session()->get('school_id');
        $student = $this->userModel->find($studentId);

        if ($student && $student['role'] === 'student' && (int)$student['school_id'] === (int)$schoolId) {
            $this->userModel->update($studentId, ['status' => 'rejected']);
            return redirect()->to(base_url('teacher/dashboard'))->with('success', 'Đã từ chối tài khoản Học sinh.');
        }

        return redirect()->to(base_url('teacher/dashboard'))->with('error', 'Không thể thao tác.');
    }

    // --------------------------------------------------------------------
    // CLASS MANAGEMENT (Supports Single & Batch/Bulk Creation e.g. 10A1, 10A2, 10A3)
    // --------------------------------------------------------------------
    public function classes()
    {
        $schoolId = session()->get('school_id');

        if ($this->request->getMethod() === 'POST') {
            $rawNames     = trim((string)$this->request->getPost('name'));
            $gradeLevel   = (int)$this->request->getPost('grade_level');
            $academicYear = trim((string)$this->request->getPost('academic_year'));

            // Split by comma, semicolon, or line breaks (e.g. 10A1, 10A2, 10A3)
            $classNames = preg_split('/[\r\n,;]+/', $rawNames);
            $createdCount = 0;
            $createdList = [];

            foreach ($classNames as $name) {
                $trimmedName = trim($name);
                if (!empty($trimmedName)) {
                    // Check if class already exists in school
                    $existing = $this->classModel->where('school_id', $schoolId)
                                                 ->where('name', $trimmedName)
                                                 ->first();
                    if (!$existing) {
                        $this->classModel->insert([
                            'school_id'          => $schoolId,
                            'creator_teacher_id' => session()->get('user_id'),
                            'name'               => $trimmedName,
                            'grade_level'        => $gradeLevel ?: 10,
                            'academic_year'      => $academicYear ?: '2025-2026',
                            'status'             => 'active',
                        ]);
                        $createdCount++;
                        $createdList[] = $trimmedName;
                    }
                }
            }

            if ($createdCount > 0) {
                $joinedNames = implode(', ', $createdList);
                return redirect()->to(base_url('teacher/classes'))->with('success', "Khởi tạo thành công {$createdCount} lớp học: [{$joinedNames}]!");
            }

            return redirect()->back()->with('error', 'Vui lòng nhập tên lớp học hợp lệ.');
        }

        $classes = $this->classModel->getClassesBySchool($schoolId);
        return view('teacher/classes', ['classes' => $classes]);
    }

    // --------------------------------------------------------------------
    // GLOBAL QUESTION BANK (WYSIWYG, Image Upload, Code Snippets)
    // --------------------------------------------------------------------
    public function questions()
    {
        $questions = $this->questionModel->getAllGlobalQuestions();
        return view('teacher/questions', ['questions' => $questions, 'teacher_id' => session()->get('user_id')]);
    }

    public function createQuestion()
    {
        if ($this->request->getMethod() === 'POST') {
            $content       = (string)$this->request->getPost('content');
            $optionA       = (string)$this->request->getPost('option_a');
            $optionB       = (string)$this->request->getPost('option_b');
            $optionC       = $this->request->getPost('option_c') ? (string)$this->request->getPost('option_c') : null;
            $optionD       = $this->request->getPost('option_d') ? (string)$this->request->getPost('option_d') : null;
            $correctOption = (string)$this->request->getPost('correct_option');
            $subject       = (string)$this->request->getPost('subject');
            $gradeLevel    = (int)$this->request->getPost('grade_level');
            $explanation   = (string)$this->request->getPost('explanation');

            if (empty($content) || empty($optionA) || empty($optionB) || empty($correctOption)) {
                return redirect()->back()->with('error', 'Nội dung câu hỏi và Đáp án A, B là bắt buộc.')->withInput();
            }

            $this->questionModel->insert([
                'creator_id'     => session()->get('user_id'),
                'subject'        => $subject ?: 'Chung',
                'grade_level'    => $gradeLevel ?: 10,
                'content'        => $content,
                'option_a'       => $optionA,
                'option_b'       => $optionB,
                'option_c'       => !empty(trim((string)$optionC)) ? $optionC : null,
                'option_d'       => !empty(trim((string)$optionD)) ? $optionD : null,
                'correct_option' => $correctOption,
                'explanation'    => $explanation,
            ]);

            return redirect()->to(base_url('teacher/questions'))->with('success', 'Thêm câu hỏi mới vào Ngân hàng chung thành công!');
        }

        return view('teacher/question_form');
    }

    public function editQuestion(int $id)
    {
        $teacherId = session()->get('user_id');
        if (!$this->questionModel->canEditOrDelete($id, $teacherId)) {
            return redirect()->to(base_url('teacher/questions'))->with('error', 'Bạn CHỈ có quyền chỉnh sửa câu hỏi do chính bạn tạo!');
        }

        $question = $this->questionModel->find($id);

        if ($this->request->getMethod() === 'POST') {
            $content       = (string)$this->request->getPost('content');
            $optionA       = (string)$this->request->getPost('option_a');
            $optionB       = (string)$this->request->getPost('option_b');
            $optionC       = $this->request->getPost('option_c') ? (string)$this->request->getPost('option_c') : null;
            $optionD       = $this->request->getPost('option_d') ? (string)$this->request->getPost('option_d') : null;
            $correctOption = (string)$this->request->getPost('correct_option');
            $subject       = (string)$this->request->getPost('subject');
            $gradeLevel    = (int)$this->request->getPost('grade_level');
            $explanation   = (string)$this->request->getPost('explanation');

            $this->questionModel->update($id, [
                'subject'        => $subject ?: 'Chung',
                'grade_level'    => $gradeLevel ?: 10,
                'content'        => $content,
                'option_a'       => $optionA,
                'option_b'       => $optionB,
                'option_c'       => !empty(trim((string)$optionC)) ? $optionC : null,
                'option_d'       => !empty(trim((string)$optionD)) ? $optionD : null,
                'correct_option' => $correctOption,
                'explanation'    => $explanation,
            ]);

            return redirect()->to(base_url('teacher/questions'))->with('success', 'Cập nhật câu hỏi thành công!');
        }

        return view('teacher/question_form', ['question' => $question]);
    }

    public function deleteQuestion(int $id)
    {
        $teacherId = session()->get('user_id');
        if (!$this->questionModel->canEditOrDelete($id, $teacherId)) {
            return redirect()->to(base_url('teacher/questions'))->with('error', 'Bạn CHỈ có quyền xoá câu hỏi do chính bạn tạo!');
        }

        $this->questionModel->delete($id);
        return redirect()->to(base_url('teacher/questions'))->with('success', 'Đã xoá câu hỏi!');
    }

    // Image Upload Handler for WYSIWYG Editor
    public function uploadImage()
    {
        $file = $this->request->getFile('image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/questions/', $newName);
            $url = base_url('uploads/questions/' . $newName);
            return $this->response->setJSON(['url' => $url]);
        }
        return $this->response->setJSON(['error' => 'Upload thất bại'], 400);
    }

    // --------------------------------------------------------------------
    // CUMULATIVE TESTS & QUESTION POOLS
    // --------------------------------------------------------------------
    public function tests()
    {
        $schoolId = session()->get('school_id');
        $classes = $this->classModel->getClassesBySchool($schoolId);

        if ($this->request->getMethod() === 'POST') {
            $classId     = (int)$this->request->getPost('class_id');
            $title       = trim((string)$this->request->getPost('title'));
            $description = trim((string)$this->request->getPost('description'));

            if ($classId && !empty($title)) {
                $this->testModel->insert([
                    'class_id'    => $classId,
                    'teacher_id'  => session()->get('user_id'),
                    'title'       => $title,
                    'description' => $description,
                    'status'      => 'active',
                ]);
                return redirect()->to(base_url('teacher/tests'))->with('success', 'Tạo bài kiểm tra tích luỹ thành công!');
            }
        }

        $tests = [];
        foreach ($classes as $cls) {
            $clsTests = $this->testModel->getTestsByClass($cls['id']);
            $tests = array_merge($tests, $clsTests);
        }

        return view('teacher/tests', ['tests' => $tests, 'classes' => $classes]);
    }

    public function managePool(int $testId)
    {
        $test = $this->testModel->find($testId);
        if (!$test) {
            return redirect()->to(base_url('teacher/tests'))->with('error', 'Bài kiểm tra không tồn tại.');
        }

        $poolQuestions   = $this->poolModel->getQuestionsInPool($testId);
        $globalQuestions = $this->questionModel->getAllGlobalQuestions();

        $poolIds = array_column($poolQuestions, 'id');

        return view('teacher/pool_manage', [
            'test'             => $test,
            'pool_questions'   => $poolQuestions,
            'global_questions' => $globalQuestions,
            'pool_ids'         => $poolIds,
        ]);
    }

    public function addQuestionToPool(int $testId, int $questionId)
    {
        $existing = $this->poolModel->where('test_id', $testId)->where('question_id', $questionId)->first();
        if (!$existing) {
            $this->poolModel->insert([
                'test_id'             => $testId,
                'question_id'         => $questionId,
                'added_by_teacher_id' => session()->get('user_id'),
            ]);
        }
        return redirect()->to(base_url("teacher/tests/pool/{$testId}"))->with('success', 'Đã thêm câu hỏi vào Kho tích luỹ!');
    }

    public function removeQuestionFromPool(int $testId, int $questionId)
    {
        $this->poolModel->where('test_id', $testId)->where('question_id', $questionId)->delete();
        return redirect()->to(base_url("teacher/tests/pool/{$testId}"))->with('success', 'Đã gỡ câu hỏi khỏi Kho tích luỹ.');
    }

    // --------------------------------------------------------------------
    // REAL-TIME SESSION CONTROL (LƯỢT THI THỰC TẾ)
    // --------------------------------------------------------------------
    public function sessions()
    {
        $teacherId = session()->get('user_id');
        $schoolId = session()->get('school_id');
        $classes = $this->classModel->getClassesBySchool($schoolId);

        if ($this->request->getMethod() === 'POST') {
            $testId       = (int)$this->request->getPost('test_id');
            $sessionTitle = trim((string)$this->request->getPost('session_title'));

            $code = 'SESS-' . strtoupper(substr(md5(uniqid()), 0, 6));

            $sessionId = $this->sessionModel->insert([
                'test_id'               => $testId,
                'teacher_id'            => $teacherId,
                'session_code'          => $code,
                'session_title'         => $sessionTitle ?: 'Lượt thi mới',
                'time_per_question_sec' => 30,
                'status'                => 'waiting',
            ]);

            return redirect()->to(base_url("teacher/sessions/control/{$sessionId}"));
        }

        $sessions = $this->sessionModel->select('test_sessions.*, tests.title as test_title, classes.name as class_name')
                                       ->join('tests', 'tests.id = test_sessions.test_id')
                                       ->join('classes', 'classes.id = tests.class_id')
                                       ->where('test_sessions.teacher_id', $teacherId)
                                       ->orderBy('test_sessions.id', 'DESC')
                                       ->findAll();

        $availableTests = $this->testModel->select('tests.*, classes.name as class_name, (SELECT COUNT(*) FROM test_questions_pool WHERE test_questions_pool.test_id = tests.id) as pool_count')
                                           ->join('classes', 'classes.id = tests.class_id')
                                           ->where('classes.school_id', $schoolId)
                                           ->findAll();

        return view('teacher/sessions', ['sessions' => $sessions, 'tests' => $availableTests]);
    }

    public function sessionControl(int $sessionId)
    {
        $session = $this->sessionModel->find($sessionId);
        if (!$session) {
            return redirect()->to(base_url('teacher/sessions'))->with('error', 'Lượt thi không tồn tại.');
        }

        $test = $this->testModel->find($session['test_id']);
        $participants = $this->participantModel->getParticipantsBySession($sessionId);

        // Fetch all students in class for manual selection / absent check
        $classStudents = $this->userModel->getStudentsByClass($test['class_id']);

        return view('teacher/session_control', [
            'session'        => $session,
            'test'           => $test,
            'participants'   => $participants,
            'class_students' => $classStudents,
        ]);
    }

    public function approveParticipant(int $participantId)
    {
        $part = $this->participantModel->find($participantId);
        if ($part) {
            $this->participantModel->update($participantId, [
                'approval_status' => 'approved',
                'test_status'     => 'ready',
                'approved_at'     => date('Y-m-d H:i:s'),
            ]);
        }
        return $this->response->setJSON(['status' => 'success']);
    }

    public function markAbsent(int $sessionId, int $studentId)
    {
        $session = $this->sessionModel->find($sessionId);
        if (!$session) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Lượt thi không tồn tại'], 404);
        }

        // 1. Update or Insert participant record with approval_status = 'absent'
        $part = $this->participantModel->where('session_id', $sessionId)
                                       ->where('student_id', $studentId)
                                       ->first();
        if ($part) {
            $this->participantModel->update($part['id'], [
                'approval_status' => 'absent',
                'test_status'     => 'absent',
            ]);
        } else {
            $this->participantModel->insert([
                'session_id'      => $sessionId,
                'student_id'      => $studentId,
                'approval_status' => 'absent',
                'test_status'     => 'absent',
            ]);
        }

        // 2. Apply Absent Penalty: +0 correct, +3 attempted added to accumulated scores!
        $this->accumulatedModel->applyAbsentPenalty($studentId, $session['test_id']);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Đã đánh vắng và cộng phạt (+0 đúng / +3 tổng) vào điểm tích luỹ!']);
    }

    public function startSession(int $sessionId)
    {
        $this->sessionModel->update($sessionId, [
            'status'     => 'in_progress',
            'started_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['status' => 'success']);
    }

    public function endSession(int $sessionId)
    {
        $this->sessionModel->update($sessionId, [
            'status'   => 'completed',
            'ended_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['status' => 'success']);
    }

    // Classroom Screen Projector Leaderboard
    public function sessionLeaderboard(int $sessionId)
    {
        $session = $this->sessionModel->find($sessionId);
        $test    = $this->testModel->find($session['test_id']);
        $results = $this->participantModel->getSessionLeaderboard($sessionId);

        return view('teacher/session_leaderboard', [
            'session' => $session,
            'test'    => $test,
            'results' => $results,
        ]);
    }

    // --------------------------------------------------------------------
    // ACCUMULATED SCORE LEADERBOARD & STUDENT SESSION BREAKDOWN REPORT
    // --------------------------------------------------------------------
    public function accumulatedScores(int $testId)
    {
        $test = $this->testModel->find($testId);
        if (!$test) {
            return redirect()->to(base_url('teacher/tests'))->with('error', 'Bài kiểm tra không tồn tại.');
        }

        $class = $this->classModel->find($test['class_id']);
        $students = $this->userModel->getStudentsByClass($test['class_id']);

        // Fetch accumulated score records for all enrolled students
        $scoresList = [];
        foreach ($students as $st) {
            $acc = $this->accumulatedModel->where('student_id', $st['id'])
                                          ->where('test_id', $testId)
                                          ->first();
            $st['total_correct']   = $acc ? (int)$acc['total_correct'] : 0;
            $st['total_attempted'] = $acc ? (int)$acc['total_attempted'] : 0;
            $st['accumulated_gpa'] = $acc ? (float)$acc['accumulated_gpa'] : 0.00;
            $st['has_score']       = $acc ? true : false;
            $scoresList[] = $st;
        }

        // Sort by accumulated_gpa DESC, then total_correct DESC
        usort($scoresList, function ($a, $b) {
            if ($a['accumulated_gpa'] == $b['accumulated_gpa']) {
                return $b['total_correct'] <=> $a['total_correct'];
            }
            return $b['accumulated_gpa'] <=> $a['accumulated_gpa'];
        });

        return view('teacher/accumulated_scores', [
            'test'        => $test,
            'class'       => $class,
            'scores_list' => $scoresList,
        ]);
    }

    public function studentScoreBreakdown(int $testId, int $studentId)
    {
        $test = $this->testModel->find($testId);
        $student = $this->userModel->find($studentId);

        if (!$test || !$student) {
            return redirect()->to(base_url('teacher/tests'))->with('error', 'Không tìm thấy dữ liệu.');
        }

        $class = $this->classModel->find($test['class_id']);
        $accumulated = $this->accumulatedModel->where('student_id', $studentId)
                                              ->where('test_id', $testId)
                                              ->first();

        // Get all real test session participation history for this student on this test
        $history = $this->participantModel->select('session_participants.*, test_sessions.session_title, test_sessions.session_code, test_sessions.created_at as session_date')
                                           ->join('test_sessions', 'test_sessions.id = session_participants.session_id')
                                           ->where('test_sessions.test_id', $testId)
                                           ->where('session_participants.student_id', $studentId)
                                           ->orderBy('session_participants.id', 'ASC')
                                           ->findAll();

        return view('teacher/student_breakdown', [
            'test'        => $test,
            'class'       => $class,
            'student'     => $student,
            'accumulated' => $accumulated,
            'history'     => $history,
        ]);
    }
}
