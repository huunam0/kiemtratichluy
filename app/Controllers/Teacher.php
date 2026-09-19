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
use App\Models\MarkdownQuizModel;
use App\Models\MarkdownQuizResultModel;
use App\Models\FillBlankQuestionModel;
use App\Models\FillBlankQuestionVariantModel;
use App\Models\FillBlankQuizModel;
use App\Models\FillBlankSessionModel;
use App\Models\FillBlankParticipantModel;
use App\Models\FillBlankResultModel;

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
    protected $markdownQuizModel;
    protected $markdownResultModel;
    protected $fillBlankQuestionModel;
    protected $fillBlankVariantModel;
    protected $fillBlankQuizModel;
    protected $fillBlankSessionModel;
    protected $fillBlankParticipantModel;
    protected $fillBlankResultModel;

    public function __construct()
    {
        $this->userModel           = new UserModel();
        $this->schoolModel         = new SchoolModel();
        $this->classModel          = new ClassModel();
        $this->questionModel       = new QuestionModel();
        $this->testModel           = new TestModel();
        $this->poolModel           = new TestQuestionPoolModel();
        $this->sessionModel        = new TestSessionModel();
        $this->participantModel    = new SessionParticipantModel();
        $this->accumulatedModel    = new AccumulatedScoreModel();
        $this->markdownQuizModel   = new MarkdownQuizModel();
        $this->markdownResultModel = new MarkdownQuizResultModel();
        $this->fillBlankQuestionModel    = new FillBlankQuestionModel();
        $this->fillBlankVariantModel     = new FillBlankQuestionVariantModel();
        $this->fillBlankQuizModel        = new FillBlankQuizModel();
        $this->fillBlankSessionModel     = new FillBlankSessionModel();
        $this->fillBlankParticipantModel = new FillBlankParticipantModel();
        $this->fillBlankResultModel      = new FillBlankResultModel();
    }

    public function dashboard()
    {
        $schoolId = session()->get('school_id');
        $teacherId = session()->get('user_id');

        $resetModel = new \App\Models\PasswordResetRequestModel();

        $data = [
            'classes'                => $this->classModel->getClassesBySchool($schoolId),
            'questions'              => $this->questionModel->getAllGlobalQuestions(),
            'my_questions'           => $this->questionModel->where('creator_id', $teacherId)->countAllResults(),
            'active_sessions'        => $this->sessionModel->where('teacher_id', $teacherId)->whereIn('status', ['waiting', 'in_progress'])->findAll(),
            'pending_students'       => $this->userModel->getPendingStudentsBySchool($schoolId),
            'pending_teachers'       => $this->userModel->getPendingTeachersBySchool($schoolId, $teacherId),
            'pending_reset_requests' => $resetModel->getPendingRequestsBySchool($schoolId),
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

    // Approve / Reject a COLLEAGUE teacher in the same school
    public function approveColleague(int $targetId)
    {
        $schoolId  = session()->get('school_id');
        $myId      = session()->get('user_id');
        $target    = $this->userModel->find($targetId);

        if (
            $target &&
            $target['role'] === 'teacher' &&
            $target['status'] === 'pending' &&
            (int)$target['school_id'] === (int)$schoolId &&
            (int)$targetId !== (int)$myId
        ) {
            $this->userModel->update($targetId, ['status' => 'approved']);
            return redirect()->to(base_url('teacher/dashboard'))->with('success', "Đã phê duyệt tài khoản Giáo viên: {$target['full_name']}!");
        }

        return redirect()->to(base_url('teacher/dashboard'))->with('error', 'Không thể phê duyệt tài khoản này.');
    }

    public function rejectColleague(int $targetId)
    {
        $schoolId  = session()->get('school_id');
        $myId      = session()->get('user_id');
        $target    = $this->userModel->find($targetId);

        if (
            $target &&
            $target['role'] === 'teacher' &&
            $target['status'] === 'pending' &&
            (int)$target['school_id'] === (int)$schoolId &&
            (int)$targetId !== (int)$myId
        ) {
            $this->userModel->update($targetId, ['status' => 'rejected']);
            return redirect()->to(base_url('teacher/dashboard'))->with('success', "Đã từ chối tài khoản Giáo viên: {$target['full_name']}.");
        }

        return redirect()->to(base_url('teacher/dashboard'))->with('error', 'Không thể thao tác với tài khoản này.');
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
    // TOPICS MANAGEMENT
    // --------------------------------------------------------------------
    public function topics()
    {
        $topicModel = new \App\Models\TopicModel();
        $subjectModel = new \App\Models\SubjectModel();
        
        if ($this->request->getMethod() === 'POST') {
            $action = $this->request->getPost('action');
            
            if ($action === 'create') {
                $subjectId = (int)$this->request->getPost('subject_id');
                $gradeLevel = (int)$this->request->getPost('grade_level');
                $name = trim((string)$this->request->getPost('name'));
                
                if ($subjectId > 0 && $gradeLevel > 0 && !empty($name)) {
                    $topicModel->insert([
                        'subject_id' => $subjectId,
                        'grade_level' => $gradeLevel,
                        'name' => $name,
                        'created_by' => session()->get('user_id'),
                    ]);
                    return redirect()->to(base_url('teacher/topics'))->with('success', 'Thêm chủ đề thành công!');
                }
                return redirect()->back()->with('error', 'Vui lòng điền đủ thông tin chủ đề.');
            } elseif ($action === 'update') {
                $id = (int)$this->request->getPost('id');
                $name = trim((string)$this->request->getPost('name'));
                if ($id > 0 && !empty($name)) {
                    // Cần kiểm tra quyền sửa nếu cần, tạm thời cho giáo viên sửa tên chủ đề
                    $topicModel->update($id, ['name' => $name]);
                    return redirect()->to(base_url('teacher/topics'))->with('success', 'Cập nhật chủ đề thành công!');
                }
            } elseif ($action === 'delete') {
                $id = (int)$this->request->getPost('id');
                if ($id > 0) {
                    $topicModel->delete($id);
                    return redirect()->to(base_url('teacher/topics'))->with('success', 'Xóa chủ đề thành công!');
                }
            }
        }

        // Fetch topics with subject names
        $topics = $topicModel->select('topics.*, subjects.name as subject_name')
                             ->join('subjects', 'subjects.id = topics.subject_id')
                             ->orderBy('topics.id', 'DESC')
                             ->findAll();
                             
        $subjects = $subjectModel->orderBy('name', 'ASC')->findAll();

        return view('teacher/topics', ['topics' => $topics, 'subjects' => $subjects]);
    }

    public function getTopicsBySubjectGrade()
    {
        $subjectId = (int)$this->request->getGet('subject_id');
        $gradeLevel = (int)$this->request->getGet('grade_level');
        
        $topicModel = new \App\Models\TopicModel();
        $topics = $topicModel->where('subject_id', $subjectId)
                             ->where('grade_level', $gradeLevel)
                             ->orderBy('name', 'ASC')
                             ->findAll();
                             
        return $this->response->setJSON($topics);
    }

    // --------------------------------------------------------------------
    // GLOBAL QUESTION BANK (WYSIWYG, Image Upload, Code Snippets)
    // --------------------------------------------------------------------
    public function questions()
    {
        $subjectId  = (int)$this->request->getGet('subject_id');
        $gradeLevel = (int)$this->request->getGet('grade_level');
        $topicId    = (int)$this->request->getGet('topic_id');
        $keyword    = trim((string)$this->request->getGet('keyword'));

        $questions = $this->questionModel->searchQuestions(
            $subjectId  ?: null,
            $gradeLevel ?: null,
            $topicId    ?: null,
            $keyword    ?: null
        );

        $subjectModel = new \App\Models\SubjectModel();
        $subjects = $subjectModel->orderBy('name', 'ASC')->findAll();

        return view('teacher/questions', [
            'questions'   => $questions,
            'teacher_id'  => session()->get('user_id'),
            'subjects'    => $subjects,
            'filter'      => [
                'subject_id'  => $subjectId ?: '',
                'grade_level' => $gradeLevel ?: '',
                'topic_id'    => $topicId ?: '',
                'keyword'     => $keyword,
            ],
        ]);
    }

    public function createQuestion()
    {
        $subjectModel = new \App\Models\SubjectModel();
        
        if ($this->request->getMethod() === 'POST') {
            $content       = (string)$this->request->getPost('content');
            $optionA       = (string)$this->request->getPost('option_a');
            $optionB       = (string)$this->request->getPost('option_b');
            $optionC       = $this->request->getPost('option_c') ? (string)$this->request->getPost('option_c') : null;
            $optionD       = $this->request->getPost('option_d') ? (string)$this->request->getPost('option_d') : null;
            $correctOption = (string)$this->request->getPost('correct_option');
            $topicId       = (int)$this->request->getPost('topic_id');
            $explanation   = (string)$this->request->getPost('explanation');

            if (empty($content) || empty($optionA) || empty($optionB) || empty($correctOption)) {
                return redirect()->back()->with('error', 'Nội dung câu hỏi và Đáp án A, B là bắt buộc.')->withInput();
            }

            if ($topicId <= 0) {
                return redirect()->back()->with('error', 'Vui lòng chọn Chủ đề.')->withInput();
            }

            $this->questionModel->insert([
                'creator_id'     => session()->get('user_id'),
                'topic_id'       => $topicId,
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

        $subjects = $subjectModel->orderBy('name', 'ASC')->findAll();
        return view('teacher/question_form', ['subjects' => $subjects]);
    }

    public function editQuestion(int $id)
    {
        $teacherId = session()->get('user_id');
        if (!$this->questionModel->canEditOrDelete($id, $teacherId)) {
            return redirect()->to(base_url('teacher/questions'))->with('error', 'Bạn CHỈ có quyền chỉnh sửa câu hỏi do chính bạn tạo!');
        }

        $question = $this->questionModel->find($id);
        $subjectModel = new \App\Models\SubjectModel();
        
        // Cần join lấy thêm thông tin topic để pre-select môn và khối lớp trên form
        $topicModel = new \App\Models\TopicModel();
        $topic = $topicModel->find($question['topic_id']);
        if ($topic) {
            $question['subject_id'] = $topic['subject_id'];
            $question['grade_level'] = $topic['grade_level'];
        }

        if ($this->request->getMethod() === 'POST') {
            $content       = (string)$this->request->getPost('content');
            $optionA       = (string)$this->request->getPost('option_a');
            $optionB       = (string)$this->request->getPost('option_b');
            $optionC       = $this->request->getPost('option_c') ? (string)$this->request->getPost('option_c') : null;
            $optionD       = $this->request->getPost('option_d') ? (string)$this->request->getPost('option_d') : null;
            $correctOption = (string)$this->request->getPost('correct_option');
            $topicId       = (int)$this->request->getPost('topic_id');
            $explanation   = (string)$this->request->getPost('explanation');

            if ($topicId > 0) {
                $this->questionModel->update($id, [
                    'topic_id'       => $topicId,
                    'content'        => $content,
                    'option_a'       => $optionA,
                    'option_b'       => $optionB,
                    'option_c'       => !empty(trim((string)$optionC)) ? $optionC : null,
                    'option_d'       => !empty(trim((string)$optionD)) ? $optionD : null,
                    'correct_option' => $correctOption,
                    'explanation'    => $explanation,
                ]);
            }

            return redirect()->to(base_url('teacher/questions'))->with('success', 'Cập nhật câu hỏi thành công!');
        }

        $subjects = $subjectModel->orderBy('name', 'ASC')->findAll();
        return view('teacher/question_form', ['question' => $question, 'subjects' => $subjects]);
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
    // AIKEN FORMAT BULK IMPORT
    // --------------------------------------------------------------------
    public function importAiken()
    {
        $subjectModel = new \App\Models\SubjectModel();
        
        if ($this->request->getMethod() === 'POST') {
            $rawText    = trim((string)$this->request->getPost('aiken_text'));
            $topicId    = (int)$this->request->getPost('topic_id');
            $teacherId  = session()->get('user_id');

            if (empty($rawText)) {
                return redirect()->back()->with('error', 'Vui lòng dán nội dung Aiken vào ô văn bản.')->withInput();
            }
            if ($topicId <= 0) {
                return redirect()->back()->with('error', 'Vui lòng chọn Chủ đề.')->withInput();
            }

            $parsed = $this->parseAikenText($rawText);

            if (empty($parsed['questions'])) {
                $hint = !empty($parsed['errors']) ? ' Lỗi đầu tiên: ' . $parsed['errors'][0] : '';
                return redirect()->back()->with('error', 'Không tìm thấy câu hỏi hợp lệ trong văn bản đã nhập.' . $hint)->withInput();
            }

            $inserted = 0;
            $skipped  = 0;
            foreach ($parsed['questions'] as $q) {
                // Skip duplicate: same content + topic already in DB
                $existing = $this->questionModel
                    ->where('topic_id', $topicId)
                    ->where('content', $q['content'])
                    ->first();
                if ($existing) {
                    $skipped++;
                    continue;
                }

                $this->questionModel->insert([
                    'creator_id'     => $teacherId,
                    'topic_id'       => $topicId,
                    'content'        => esc($q['content']),
                    'option_a'       => esc($q['option_a']),
                    'option_b'       => esc($q['option_b']),
                    'option_c'       => isset($q['option_c']) ? esc($q['option_c']) : null,
                    'option_d'       => isset($q['option_d']) ? esc($q['option_d']) : null,
                    'correct_option' => $q['answer'],
                    'explanation'    => '',
                ]);
                $inserted++;
            }

            $parseErrors = count($parsed['errors']);
            $msg = "Import Aiken thành công: đã thêm {$inserted} câu hỏi";
            if ($skipped > 0) {
                $msg .= ", bỏ qua {$skipped} câu trùng lặp";
            }
            if ($parseErrors > 0) {
                $msg .= ", {$parseErrors} câu bị lỗi định dạng";
            }
            $msg .= ".";
            
            return redirect()->to(base_url('teacher/questions'))->with('success', $msg);
        }

        $subjects = $subjectModel->orderBy('name', 'ASC')->findAll();
        return view('teacher/aiken_import', ['subjects' => $subjects]);
    }

    /**
     * Parse Aiken-format text into an array of questions.
     *
     * Aiken format:
     *   Question text
     *   A. Option text
     *   B. Option text
     *   C. Option text   (optional)
     *   D. Option text   (optional)
     *   ANSWER: A
     *
     * Returns ['questions' => [...], 'errors' => [...]]
     */
    private function parseAikenText(string $text): array
    {
        $questions = [];
        $errors    = [];

        // Normalise line endings
        $text  = str_replace(["\r\n", "\r"], "\n", $text);
        // Split into blocks by blank lines
        $blocks = preg_split('/\n{2,}/', trim($text));

        foreach ($blocks as $blockIndex => $block) {
            $block = trim($block);
            if (empty($block)) {
                continue;
            }

            $lines = array_map('trim', explode("\n", $block));
            // Remove empty lines inside block
            $lines = array_values(array_filter($lines, fn($l) => $l !== ''));

            if (count($lines) < 4) {
                // Too few lines to be a valid question (need content + A + B + ANSWER at minimum)
                $errors[] = "Khối " . ($blockIndex + 1) . ": Không đủ dòng (cần ít nhất 4 dòng).";
                continue;
            }

            // The first line is the question content (may span multiple lines before the first option)
            $optionPattern  = '/^([A-Da-d])\.\s+/';
            $answerPattern  = '/^ANSWER\s*:\s*([A-Da-d])\s*$/i';

            $contentLines = [];
            $options      = [];
            $answer       = null;
            $parsingOpts  = false;

            foreach ($lines as $line) {
                if (preg_match($answerPattern, $line, $m)) {
                    $answer = strtoupper($m[1]);
                } elseif (preg_match($optionPattern, $line, $m)) {
                    $parsingOpts = true;
                    $letter = strtoupper($m[1]);
                    $optText = preg_replace($optionPattern, '', $line);
                    $options[$letter] = trim($optText);
                } elseif (!$parsingOpts) {
                    $contentLines[] = $line;
                }
                // Lines after options but before ANSWER that don't match are ignored
            }

            $content = implode(' ', $contentLines);
            $content = trim($content);

            // Validate
            if (empty($content)) {
                $errors[] = "Khối " . ($blockIndex + 1) . ": Không có nội dung câu hỏi.";
                continue;
            }
            if (!isset($options['A']) || !isset($options['B'])) {
                $errors[] = "Khối " . ($blockIndex + 1) . " [\"" . mb_substr($content, 0, 40) . "\"]: Thiếu đáp án A hoặc B.";
                continue;
            }
            if ($answer === null) {
                $errors[] = "Khối " . ($blockIndex + 1) . " [\"" . mb_substr($content, 0, 40) . "\"]: Thiếu dòng ANSWER.";
                continue;
            }
            if (!isset($options[$answer])) {
                $errors[] = "Khối " . ($blockIndex + 1) . " [\"" . mb_substr($content, 0, 40) . "\"]: Đáp án đúng ({$answer}) không có trong danh sách lựa chọn.";
                continue;
            }

            $questions[] = [
                'content'  => $content,
                'option_a' => $options['A'],
                'option_b' => $options['B'],
                'option_c' => $options['C'] ?? null,
                'option_d' => $options['D'] ?? null,
                'answer'   => $answer,
            ];
        }

        return ['questions' => $questions, 'errors' => $errors];
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
        $poolIds = array_column($poolQuestions, 'id');

        return view('teacher/pool_manage', [
            'test'             => $test,
            'pool_questions'   => $poolQuestions,
            'pool_ids'         => $poolIds,
        ]);
    }

    public function selectQuestionsForPool(int $testId)
    {
        $test = $this->testModel->find($testId);
        if (!$test) {
            return redirect()->to(base_url('teacher/tests'))->with('error', 'Bài kiểm tra không tồn tại.');
        }

        $subjectModel = new \App\Models\SubjectModel();
        $topicModel = new \App\Models\TopicModel();
        
        $subjects = $subjectModel->findAll();
        // Lấy tất cả bài kiểm tra của giáo viên để làm tuỳ chọn lọc từ đề khác
        $otherTests = $this->testModel->where('teacher_id', session()->get('user_id'))->findAll();

        $filters = [
            'subject_id'   => $this->request->getGet('subject_id'),
            'grade_level'  => $this->request->getGet('grade_level'),
            'topic_id'     => $this->request->getGet('topic_id'),
            'keyword'      => $this->request->getGet('keyword'),
            'from_test_id' => $this->request->getGet('from_test_id'),
        ];

        // Retrieve existing pool questions to exclude them
        $poolQuestions = $this->poolModel->getQuestionsInPool($testId);
        $excludeIds = array_column($poolQuestions, 'id');

        $questions = $this->questionModel->getQuestionsForPoolSelection($filters, $excludeIds);

        // Fetch topics for the selected subject and grade to repopulate topic dropdown if needed
        $topics = [];
        if (!empty($filters['subject_id']) && !empty($filters['grade_level'])) {
            $topics = $topicModel->where('subject_id', $filters['subject_id'])
                                 ->where('grade_level', $filters['grade_level'])
                                 ->findAll();
        }

        return view('teacher/pool_select_questions', [
            'test'       => $test,
            'questions'  => $questions,
            'subjects'   => $subjects,
            'topics'     => $topics,
            'otherTests' => $otherTests,
            'filters'    => $filters
        ]);
    }

    public function bulkAddQuestionsToPool(int $testId)
    {
        $questionIds = $this->request->getPost('question_ids');
        if (!empty($questionIds) && is_array($questionIds)) {
            $userId = session()->get('user_id');
            foreach ($questionIds as $qid) {
                $existing = $this->poolModel->where('test_id', $testId)->where('question_id', $qid)->first();
                if (!$existing) {
                    $this->poolModel->insert([
                        'test_id'             => $testId,
                        'question_id'         => $qid,
                        'added_by_teacher_id' => $userId,
                    ]);
                }
            }
            return redirect()->to(base_url("teacher/tests/pool/{$testId}"))->with('success', 'Đã thêm các câu hỏi đã chọn vào Kho tích luỹ!');
        }
        
        return redirect()->back()->with('error', 'Chưa có câu hỏi nào được chọn.');
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

    // --------------------------------------------------------------------
    // RANDOM STUDENT PICKER (GỌI NGẪU NHIÊN HS KIỂM TRA THẬT)
    // --------------------------------------------------------------------
    public function randomPickStudents(int $testId)
    {
        $test = $this->testModel->find($testId);
        if (!$test) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Bài kiểm tra không tồn tại.'], 404);
        }

        $quantity = (int)$this->request->getPost('quantity');
        if ($quantity < 1) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Số lượng phải lớn hơn 0.'], 400);
        }

        // 1. Get all students in the class with their real test count
        $students = $this->userModel->getStudentsByClass($test['class_id']);
        if (empty($students)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Lớp học chưa có học sinh nào.']);
        }

        $studentData = [];
        foreach ($students as $st) {
            $realCount = $this->participantModel->countRealTestsByStudent((int)$st['id'], $testId);
            $studentData[] = [
                'id'         => $st['id'],
                'full_name'  => $st['full_name'],
                'username'   => $st['username'],
                'real_count' => $realCount,
            ];
        }

        // 2. Sort by real_count ascending
        usort($studentData, function ($a, $b) {
            return $a['real_count'] <=> $b['real_count'];
        });

        // 3. Find the median of real_count values
        $counts = array_column($studentData, 'real_count');
        $n = count($counts);
        if ($n % 2 === 0) {
            $median = ($counts[$n / 2 - 1] + $counts[$n / 2]) / 2;
        } else {
            $median = $counts[intdiv($n, 2)];
        }

        // 4. Filter students with real_count <= median
        $pool = array_filter($studentData, function ($st) use ($median) {
            return $st['real_count'] <= $median;
        });
        $pool = array_values($pool);

        // 5. Shuffle and pick N students
        shuffle($pool);
        $picked = array_slice($pool, 0, min($quantity, count($pool)));

        return $this->response->setJSON([
            'status'       => 'success',
            'picked'       => $picked,
            'total_class'  => count($studentData),
            'median'       => $median,
            'pool_size'    => count($pool),
            'requested'    => $quantity,
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

    // --------------------------------------------------------------------
    // MARKDOWN PRACTICE QUIZZES MANAGEMENT
    // --------------------------------------------------------------------
    public function practiceQuizzes()
    {
        $teacherId = session()->get('user_id');
        $quizzes   = $this->markdownQuizModel->getQuizzesByTeacher($teacherId);

        return view('teacher/practice_quizzes', [
            'quizzes' => $quizzes
        ]);
    }

    public function createPracticeQuiz()
    {
        if ($this->request->getMethod() === 'POST') {
            $title      = trim((string)$this->request->getPost('title'));
            $subject    = trim((string)$this->request->getPost('subject')) ?: 'Chung';
            $gradeLevel = (int)$this->request->getPost('grade_level') ?: 10;
            $markdown   = trim((string)$this->request->getPost('content_markdown'));

            if (empty($title) || empty($markdown)) {
                return redirect()->back()->with('error', 'Tiêu đề và Nội dung Markdown không được để trống.')->withInput();
            }

            $slug = $this->markdownQuizModel->generateUniqueSlug($title);
            $schoolId = session()->get('school_id');

            $this->markdownQuizModel->insert([
                'teacher_id'       => session()->get('user_id'),
                'school_id'        => $schoolId,
                'title'            => $title,
                'slug'             => $slug,
                'subject'          => $subject,
                'grade_level'      => $gradeLevel,
                'content_markdown' => $markdown,
                'status'           => 'active',
            ]);

            return redirect()->to(base_url('teacher/practice-quizzes'))->with('success', 'Tạo đề trắc nghiệm luyện tập thành công!');
        }

        return view('teacher/practice_quiz_form');
    }

    public function editPracticeQuiz(int $id)
    {
        $teacherId = session()->get('user_id');
        $quiz      = $this->markdownQuizModel->find($id);

        if (!$quiz || (int)$quiz['teacher_id'] !== $teacherId) {
            return redirect()->to(base_url('teacher/practice-quizzes'))->with('error', 'Bạn không có quyền sửa đề trắc nghiệm này.');
        }

        if ($this->request->getMethod() === 'POST') {
            $title      = trim((string)$this->request->getPost('title'));
            $subject    = trim((string)$this->request->getPost('subject')) ?: 'Chung';
            $gradeLevel = (int)$this->request->getPost('grade_level') ?: 10;
            $markdown   = trim((string)$this->request->getPost('content_markdown'));

            if (empty($title) || empty($markdown)) {
                return redirect()->back()->with('error', 'Tiêu đề và Nội dung Markdown không được để trống.')->withInput();
            }

            $this->markdownQuizModel->update($id, [
                'title'            => $title,
                'subject'          => $subject,
                'grade_level'      => $gradeLevel,
                'content_markdown' => $markdown,
            ]);

            return redirect()->to(base_url('teacher/practice-quizzes'))->with('success', 'Cập nhật đề trắc nghiệm luyện tập thành công!');
        }

        return view('teacher/practice_quiz_form', ['quiz' => $quiz]);
    }

    public function deletePracticeQuiz(int $id)
    {
        $teacherId = session()->get('user_id');
        $quiz      = $this->markdownQuizModel->find($id);

        if ($quiz && (int)$quiz['teacher_id'] === $teacherId) {
            $this->markdownQuizModel->delete($id);
            return redirect()->to(base_url('teacher/practice-quizzes'))->with('success', 'Đã xoá đề trắc nghiệm.');
        }

        return redirect()->to(base_url('teacher/practice-quizzes'))->with('error', 'Không thể xoá đề trắc nghiệm này.');
    }

    public function practiceQuizResults(int $id)
    {
        $teacherId = session()->get('user_id');
        $quiz      = $this->markdownQuizModel->find($id);

        if (!$quiz || (int)$quiz['teacher_id'] !== $teacherId) {
            return redirect()->to(base_url('teacher/practice-quizzes'))->with('error', 'Đề trắc nghiệm không tồn tại.');
        }

        $results = $this->markdownResultModel->getResultsByQuiz($id);

        return view('teacher/practice_quiz_results', [
            'quiz'    => $quiz,
            'results' => $results
        ]);
    }

    // =========================================================================
    // FILL-IN-THE-BLANKS (TRẮC NGHIỆM ĐIỀN VÀO CHỖ TRỐNG) MANAGEMENT
    // =========================================================================

    public function fillBlankQuestions()
    {
        $teacherId = session()->get('user_id');
        $questions = $this->fillBlankQuestionModel->getQuestionsByTeacher($teacherId);

        return view('teacher/fill_blank/questions', [
            'questions' => $questions
        ]);
    }

    public function createFillBlankQuestion()
    {
        $teacherId = session()->get('user_id');
        $schoolId  = session()->get('school_id');
        $subjectModel = new \App\Models\SubjectModel();

        if ($this->request->getMethod() === 'POST') {
            $title      = trim((string)$this->request->getPost('title'));
            $topicId    = (int)$this->request->getPost('topic_id');
            $vName      = trim((string)$this->request->getPost('variant_name')) ?: 'var_1';
            $vContent   = trim((string)$this->request->getPost('variant_content'));

            if (empty($title) || empty($vContent)) {
                return redirect()->back()->with('error', 'Vui lòng nhập Tên câu hỏi và Nội dung biến thể mẫu.')->withInput();
            }
            if ($topicId <= 0) {
                return redirect()->back()->with('error', 'Vui lòng chọn Chủ đề.')->withInput();
            }

            $qId = $this->fillBlankQuestionModel->insert([
                'teacher_id'  => $teacherId,
                'school_id'   => $schoolId,
                'title'       => $title,
                'topic_id'    => $topicId,
            ]);

            $this->fillBlankVariantModel->insert([
                'question_id'  => $qId,
                'variant_name' => $vName,
                'content_raw'  => $vContent,
            ]);

            return redirect()->to(base_url('teacher/fill-blank/questions'))->with('success', 'Tạo câu hỏi điền chỗ trống thành công!');
        }

        $subjects = $subjectModel->orderBy('name', 'ASC')->findAll();
        return view('teacher/fill_blank/question_form', ['question' => null, 'variants' => [], 'subjects' => $subjects]);
    }

    public function editFillBlankQuestion(int $id)
    {
        $teacherId = session()->get('user_id');
        $question  = $this->fillBlankQuestionModel->find($id);
        $subjectModel = new \App\Models\SubjectModel();

        if (!$question || (int)$question['teacher_id'] !== $teacherId) {
            return redirect()->to(base_url('teacher/fill-blank/questions'))->with('error', 'Không tìm thấy câu hỏi.');
        }

        $topicModel = new \App\Models\TopicModel();
        $topic = $topicModel->find($question['topic_id']);
        if ($topic) {
            $question['subject_id'] = $topic['subject_id'];
            $question['grade_level'] = $topic['grade_level'];
        }

        if ($this->request->getMethod() === 'POST') {
            $action = $this->request->getPost('action');

            if ($action === 'add_variant') {
                $vName    = trim((string)$this->request->getPost('new_variant_name')) ?: ('var_' . time());
                $vContent = trim((string)$this->request->getPost('new_variant_content'));

                if (!empty($vContent)) {
                    $this->fillBlankVariantModel->insert([
                        'question_id'  => $id,
                        'variant_name' => $vName,
                        'content_raw'  => $vContent,
                    ]);
                    return redirect()->to(base_url("teacher/fill-blank/questions/edit/{$id}"))->with('success', 'Thêm biến thể mới thành công!');
                }
            } elseif ($action === 'delete_variant') {
                $varId = (int)$this->request->getPost('variant_id');
                $this->fillBlankVariantModel->delete($varId);
                return redirect()->to(base_url("teacher/fill-blank/questions/edit/{$id}"))->with('success', 'Đã xoá biến thể.');
            } else {
                $title      = trim((string)$this->request->getPost('title'));
                $topicId    = (int)$this->request->getPost('topic_id');

                if ($topicId > 0) {
                    $this->fillBlankQuestionModel->update($id, [
                        'title'       => $title,
                        'topic_id'    => $topicId,
                    ]);
                }

                return redirect()->to(base_url('teacher/fill-blank/questions'))->with('success', 'Cập nhật câu hỏi thành công!');
            }
        }

        $variants = $this->fillBlankVariantModel->getVariantsByQuestion($id);
        $subjects = $subjectModel->orderBy('name', 'ASC')->findAll();

        return view('teacher/fill_blank/question_form', [
            'question' => $question,
            'variants' => $variants,
            'subjects' => $subjects
        ]);
    }

    public function deleteFillBlankQuestion(int $id)
    {
        $teacherId = session()->get('user_id');
        $question  = $this->fillBlankQuestionModel->find($id);

        if ($question && (int)$question['teacher_id'] === $teacherId) {
            $this->fillBlankQuestionModel->delete($id);
            $this->fillBlankVariantModel->where('question_id', $id)->delete();
            return redirect()->to(base_url('teacher/fill-blank/questions'))->with('success', 'Đã xoá câu hỏi và các biến thể.');
        }

        return redirect()->to(base_url('teacher/fill-blank/questions'))->with('error', 'Không thể xoá câu hỏi này.');
    }

    // --- QUIZZES ---
    public function fillBlankQuizzes()
    {
        $teacherId = session()->get('user_id');
        $quizzes   = $this->fillBlankQuizModel->getQuizzesByTeacher($teacherId);

        return view('teacher/fill_blank/quizzes', [
            'quizzes' => $quizzes
        ]);
    }

    public function createFillBlankQuiz()
    {
        $teacherId = session()->get('user_id');
        $schoolId  = session()->get('school_id');

        if ($this->request->getMethod() === 'POST') {
            $title       = trim((string)$this->request->getPost('title'));
            $classId     = (int)$this->request->getPost('class_id');
            $timeLimit   = (int)$this->request->getPost('time_limit') ?: 15;
            $selectedQs  = $this->request->getPost('selected_questions') ?: [];

            if (empty($title) || empty($selectedQs)) {
                return redirect()->back()->with('error', 'Vui lòng nhập Tên bài kiểm tra và chọn ít nhất 1 câu hỏi.')->withInput();
            }

            $this->fillBlankQuizModel->insert([
                'teacher_id'            => $teacherId,
                'school_id'             => $schoolId,
                'class_id'              => $classId,
                'title'                 => $title,
                'subject'               => 'Tin học',
                'time_limit'            => $timeLimit,
                'selected_question_ids' => json_encode($selectedQs),
                'status'                => 'active',
            ]);

            return redirect()->to(base_url('teacher/fill-blank/quizzes'))->with('success', 'Tạo bài kiểm tra điền chỗ trống thành công!');
        }

        $classes   = $this->classModel->getClassesBySchool($schoolId);
        $questions = $this->fillBlankQuestionModel->getQuestionsByTeacher($teacherId);

        return view('teacher/fill_blank/quiz_form', [
            'quiz'      => null,
            'classes'   => $classes,
            'questions' => $questions
        ]);
    }

    public function editFillBlankQuiz(int $id)
    {
        $teacherId = session()->get('user_id');
        $schoolId  = session()->get('school_id');
        $quiz      = $this->fillBlankQuizModel->find($id);

        if (!$quiz || (int)$quiz['teacher_id'] !== $teacherId) {
            return redirect()->to(base_url('teacher/fill-blank/quizzes'))->with('error', 'Không tìm thấy bài kiểm tra.');
        }

        if ($this->request->getMethod() === 'POST') {
            $title       = trim((string)$this->request->getPost('title'));
            $classId     = (int)$this->request->getPost('class_id');
            $timeLimit   = (int)$this->request->getPost('time_limit') ?: 15;
            $selectedQs  = $this->request->getPost('selected_questions') ?: [];

            if (empty($title) || empty($selectedQs)) {
                return redirect()->back()->with('error', 'Vui lòng chọn ít nhất 1 câu hỏi.')->withInput();
            }

            $this->fillBlankQuizModel->update($id, [
                'title'                 => $title,
                'class_id'              => $classId,
                'time_limit'            => $timeLimit,
                'selected_question_ids' => json_encode($selectedQs),
            ]);

            return redirect()->to(base_url('teacher/fill-blank/quizzes'))->with('success', 'Cập nhật bài kiểm tra thành công!');
        }

        $classes   = $this->classModel->getClassesBySchool($schoolId);
        $questions = $this->fillBlankQuestionModel->getQuestionsByTeacher($teacherId);

        return view('teacher/fill_blank/quiz_form', [
            'quiz'      => $quiz,
            'classes'   => $classes,
            'questions' => $questions
        ]);
    }

    public function deleteFillBlankQuiz(int $id)
    {
        $teacherId = session()->get('user_id');
        $quiz      = $this->fillBlankQuizModel->find($id);

        if ($quiz && (int)$quiz['teacher_id'] === $teacherId) {
            $this->fillBlankQuizModel->delete($id);
            return redirect()->to(base_url('teacher/fill-blank/quizzes'))->with('success', 'Đã xoá bài kiểm tra.');
        }

        return redirect()->to(base_url('teacher/fill-blank/quizzes'))->with('error', 'Không thể xoá bài kiểm tra này.');
    }

    // --- REAL TEST SESSIONS & APPROVAL ---
    public function createFillBlankSession(int $quizId)
    {
        $teacherId = session()->get('user_id');
        $quiz      = $this->fillBlankQuizModel->find($quizId);

        if (!$quiz || (int)$quiz['teacher_id'] !== $teacherId) {
            return redirect()->to(base_url('teacher/fill-blank/quizzes'))->with('error', 'Bài kiểm tra không tồn tại.');
        }

        if (empty($quiz['class_id'])) {
            return redirect()->to(base_url('teacher/fill-blank/quizzes'))->with('error', 'Bài kiểm tra chưa được gán cho Lớp học nào.');
        }

        $sessionCode = strtoupper(substr(md5(uniqid()), 0, 6));
        $sessionId = $this->fillBlankSessionModel->insert([
            'quiz_id'      => $quizId,
            'teacher_id'   => $teacherId,
            'class_id'     => $quiz['class_id'],
            'session_code' => $sessionCode,
            'status'       => 'waiting',
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url("teacher/fill-blank/sessions/control/{$sessionId}"))->with('success', 'Đã tạo lượt kiểm tra thật thành công!');
    }

    public function fillBlankSessionControl(int $sessionId)
    {
        $teacherId = session()->get('user_id');
        $session   = $this->fillBlankSessionModel->find($sessionId);

        if (!$session || (int)$session['teacher_id'] !== $teacherId) {
            return redirect()->to(base_url('teacher/fill-blank/quizzes'))->with('error', 'Không tìm thấy phiên kiểm tra.');
        }

        $quiz         = $this->fillBlankQuizModel->find($session['quiz_id']);
        $participants = $this->fillBlankParticipantModel->getParticipantsBySession($sessionId);

        return view('teacher/fill_blank/session_control', [
            'session'      => $session,
            'quiz'         => $quiz,
            'participants' => $participants,
        ]);
    }

    public function approveFillBlankParticipant(int $partId)
    {
        $teacherId = session()->get('user_id');
        $part      = $this->fillBlankParticipantModel->find($partId);

        if ($part) {
            $session = $this->fillBlankSessionModel->find($part['session_id']);
            if ($session && (int)$session['teacher_id'] === $teacherId) {
                $this->fillBlankParticipantModel->update($partId, [
                    'approval_status' => 'approved',
                    'test_status'     => 'in_test',
                    'approved_at'     => date('Y-m-d H:i:s'),
                    'started_at'      => date('Y-m-d H:i:s'),
                ]);

                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['status' => 'success']);
                }
                return redirect()->back()->with('success', 'Đã phê duyệt học sinh vào thi.');
            }
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error'], 400);
        }
        return redirect()->back()->with('error', 'Không thể phê duyệt.');
    }

    public function rejectFillBlankParticipant(int $partId)
    {
        $teacherId = session()->get('user_id');
        $part      = $this->fillBlankParticipantModel->find($partId);

        if ($part) {
            $session = $this->fillBlankSessionModel->find($part['session_id']);
            if ($session && (int)$session['teacher_id'] === $teacherId) {
                $this->fillBlankParticipantModel->update($partId, [
                    'approval_status' => 'rejected',
                ]);

                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['status' => 'success']);
                }
                return redirect()->back()->with('success', 'Đã từ chối học sinh.');
            }
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error'], 400);
        }
        return redirect()->back()->with('error', 'Không thể từ chối.');
    }

    public function resetUserPassword(int $requestId)
    {
        $teacherId  = (int)session()->get('user_id');
        $mySchoolId = (int)session()->get('school_id');
        $resetModel = new \App\Models\PasswordResetRequestModel();

        $req = $resetModel->find($requestId);
        if (!$req || $req['status'] !== 'pending' || (int)$req['school_id'] !== $mySchoolId) {
            return redirect()->back()->with('error', 'Yêu cầu đặt lại mật khẩu không tồn tại, đã xử lý hoặc không thuộc trường của bạn.');
        }

        $targetUser = $this->userModel->find($req['user_id']);
        if (!$targetUser || (int)$targetUser['school_id'] !== $mySchoolId) {
            return redirect()->back()->with('error', 'Tài khoản cần reset không thuộc trường của bạn.');
        }

        if ($this->request->getMethod() === 'POST') {
            $newPass = trim((string)$this->request->getPost('new_password')) ?: '123456';

            if (strlen($newPass) < 6) {
                return redirect()->back()->with('error', 'Mật khẩu mới phải từ 6 ký tự trở lên.');
            }

            // Update user password
            $this->userModel->update($targetUser['id'], [
                'password_hash' => password_hash($newPass, PASSWORD_BCRYPT),
            ]);

            // Update request status to completed
            $resetModel->update($requestId, [
                'status'               => 'completed',
                'reset_by_teacher_id' => $teacherId,
                'completed_at'         => date('Y-m-d H:i:s'),
            ]);

            $roleText = $targetUser['role'] === 'student' ? 'học sinh' : 'giáo viên';
            return redirect()->back()->with('success', "Đã phê duyệt và đặt lại mật khẩu cho {$roleText} \"{$targetUser['full_name']}\" (@{$targetUser['username']}) thành: {$newPass}");
        }

        return redirect()->back();
    }

    public function cancelPasswordReset(int $requestId)
    {
        $teacherId  = (int)session()->get('user_id');
        $mySchoolId = (int)session()->get('school_id');
        $resetModel = new \App\Models\PasswordResetRequestModel();

        $req = $resetModel->find($requestId);
        if ($req && (int)$req['school_id'] === $mySchoolId) {
            $resetModel->update($requestId, [
                'status'               => 'cancelled',
                'reset_by_teacher_id' => $teacherId,
                'completed_at'         => date('Y-m-d H:i:s'),
            ]);
            return redirect()->back()->with('success', 'Đã hủy yêu cầu đặt lại mật khẩu.');
        }

        return redirect()->back()->with('error', 'Không thể thao tác.');
    }
}
