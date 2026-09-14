<?php

namespace App\Controllers;

use App\Models\MarkdownQuizModel;
use App\Models\MarkdownQuizResultModel;
use App\Libraries\QuizHelper;
require_once APPPATH . 'Libraries/Parsedown.php';

class PracticeQuiz extends BaseController
{
    protected $quizModel;
    protected $resultModel;

    public function __construct()
    {
        $this->quizModel   = new MarkdownQuizModel();
        $this->resultModel = new MarkdownQuizResultModel();
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
