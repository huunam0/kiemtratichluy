<?php

namespace App\Controllers;

use App\Models\MarkdownQuizModel;
use App\Models\MarkdownQuizResultModel;
use App\Models\ClassStudentModel;
use App\Libraries\QuizHelper;
require_once APPPATH . 'Libraries/Parsedown.php';

class PracticeQuiz extends BaseController
{
    protected $quizModel;
    protected $resultModel;
    protected $classStudentModel;

    public function __construct()
    {
        $this->quizModel         = new MarkdownQuizModel();
        $this->resultModel       = new MarkdownQuizResultModel();
        $this->classStudentModel = new ClassStudentModel();
    }

    public function take(string $slug)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('auth/login'))->with('error', 'Vui lòng đăng nhập để xem và làm bài luyện tập.');
        }

        $quiz = $this->quizModel->getQuizBySlug($slug);
        if (!$quiz) {
            return redirect()->to('/')->with('error', 'Đề trắc nghiệm luyện tập không tồn tại hoặc đã bị ẩn.');
        }

        // Check that the student belongs to the quiz's assigned class
        if (!empty($quiz['class_id']) && session()->get('role') === 'student') {
            $studentId  = session()->get('user_id');
            $membership = $this->classStudentModel
                               ->where('class_id', $quiz['class_id'])
                               ->where('student_id', $studentId)
                               ->first();
            if (!$membership) {
                return redirect()->to(base_url('student/dashboard'))
                                 ->with('error', 'Bài luyện tập này chỉ dành cho học sinh của lớp được gán. Bạn không có quyền truy cập.');
            }
        }

        // Check allow_mock permission — teacher may lock the quiz
        if ((int)($quiz['allow_mock'] ?? 1) === 0 && session()->get('role') === 'student') {
            return redirect()->to(base_url('student/dashboard'))
                             ->with('error', 'Giáo viên đã tạm khóa bài luyện tập này. Vui lòng thử lại sau.');
        }

        // Render markdown with QuizHelper and Parsedown
        $txt = QuizHelper::giaimadethi($quiz['content_markdown']);
        $txt = htmlspecialchars_decode($txt);
        $parsedown = new \Parsedown();
        $renderedHtml = $parsedown->text($txt);

        $results = $this->resultModel->getResultsByQuiz($quiz['id']);

        return view('quiz/take', [
            'quiz'          => $quiz,
            'rendered_html' => $renderedHtml,
            'results'       => $results,
        ]);
    }

    public function submit(string $slug)
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Vui lòng đăng nhập để thực hiện bài làm.'], 401);
        }

        $quiz = $this->quizModel->getQuizBySlug($slug);
        if (!$quiz) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Đề thi không tồn tại.'], 404);
        }

        // Verify class membership before accepting submission
        if (!empty($quiz['class_id']) && session()->get('role') === 'student') {
            $studentId  = session()->get('user_id');
            $membership = $this->classStudentModel
                               ->where('class_id', $quiz['class_id'])
                               ->where('student_id', $studentId)
                               ->first();
            if (!$membership) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Bạn không có quyền nộp bài này.'], 403);
            }
        }

        // Check allow_mock permission before accepting submission
        if ((int)($quiz['allow_mock'] ?? 1) === 0 && session()->get('role') === 'student') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Bài luyện tập này đã bị khóa. Không thể nộp bài.'], 403);
        }

        $studentName    = session()->get('full_name') ?: 'Học sinh';
        $scoreCorrect   = (int)$this->request->getPost('score_correct');
        $totalQuestions = (int)$this->request->getPost('total_questions');
        $wrongCount     = (int)$this->request->getPost('wrong_count');
        $wrongJson      = (string)$this->request->getPost('wrong_questions_json');
        $userId         = session()->get('user_id');

        $this->resultModel->insert([
            'quiz_id'              => $quiz['id'],
            'user_id'              => $userId,
            'student_name'         => $studentName,
            'score_correct'        => $scoreCorrect,
            'total_questions'      => $totalQuestions,
            'wrong_questions_json' => $wrongJson,
            'completed_at'         => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Đã ghi nhận kết quả bài luyện tập!'
        ]);
    }
}

