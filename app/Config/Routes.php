<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'Home::index');

// Auth routes
$routes->group('auth', function ($routes) {
    $routes->match(['get', 'post'], 'login', 'Auth::login');
    $routes->match(['get', 'post'], 'register', 'Auth::register');
    $routes->get('logout', 'Auth::logout');
    $routes->get('get-classes/(:num)', 'Auth::getClassesBySchool/$1');
});

// Admin routes
$routes->group('admin', ['filter' => 'role:admin'], function ($routes) {
    $routes->get('dashboard', 'Admin::dashboard');
    $routes->get('approve-teacher/(:num)', 'Admin::approveTeacher/$1');
    $routes->get('reject-teacher/(:num)', 'Admin::rejectTeacher/$1');
    $routes->match(['get', 'post'], 'schools', 'Admin::schools');
});

// Teacher routes
$routes->group('teacher', ['filter' => 'role:teacher'], function ($routes) {
    $routes->get('dashboard', 'Teacher::dashboard');
    $routes->get('approve-student/(:num)', 'Teacher::approveStudent/$1');
    $routes->get('reject-student/(:num)', 'Teacher::rejectStudent/$1');
    $routes->get('approve-teacher/(:num)', 'Teacher::approveColleague/$1');
    $routes->get('reject-teacher/(:num)', 'Teacher::rejectColleague/$1');
    
    // Classes
    $routes->match(['get', 'post'], 'classes', 'Teacher::classes');

    // Questions (Global Bank)
    $routes->get('questions', 'Teacher::questions');
    $routes->match(['get', 'post'], 'questions/create', 'Teacher::createQuestion');
    $routes->match(['get', 'post'], 'questions/edit/(:num)', 'Teacher::editQuestion/$1');
    $routes->get('questions/delete/(:num)', 'Teacher::deleteQuestion/$1');
    $routes->post('questions/upload-image', 'Teacher::uploadImage');
    $routes->match(['get', 'post'], 'questions/import-aiken', 'Teacher::importAiken');

    // Tests & Question Pools
    $routes->match(['get', 'post'], 'tests', 'Teacher::tests');
    $routes->get('tests/pool/(:num)', 'Teacher::managePool/$1');
    $routes->get('tests/pool/add/(:num)/(:num)', 'Teacher::addQuestionToPool/$1/$2');
    $routes->get('tests/pool/remove/(:num)/(:num)', 'Teacher::removeQuestionFromPool/$1/$2');
    $routes->get('tests/accumulated-scores/(:num)', 'Teacher::accumulatedScores/$1');
    $routes->post('tests/random-pick/(:num)', 'Teacher::randomPickStudents/$1');
    $routes->get('tests/student-breakdown/(:num)/(:num)', 'Teacher::studentScoreBreakdown/$1/$2');

    // Real-Time Sessions
    $routes->match(['get', 'post'], 'sessions', 'Teacher::sessions');
    $routes->get('sessions/control/(:num)', 'Teacher::sessionControl/$1');
    $routes->post('sessions/approve/(:num)', 'Teacher::approveParticipant/$1');
    $routes->post('sessions/absent/(:num)/(:num)', 'Teacher::markAbsent/$1/$2');
    $routes->post('sessions/start/(:num)', 'Teacher::startSession/$1');
    $routes->post('sessions/end/(:num)', 'Teacher::endSession/$1');
    $routes->get('sessions/leaderboard/(:num)', 'Teacher::sessionLeaderboard/$1');

    // Markdown Practice Quizzes Management
    $routes->get('practice-quizzes', 'Teacher::practiceQuizzes');
    $routes->match(['get', 'post'], 'practice-quizzes/create', 'Teacher::createPracticeQuiz');
    $routes->match(['get', 'post'], 'practice-quizzes/edit/(:num)', 'Teacher::editPracticeQuiz/$1');
    $routes->get('practice-quizzes/delete/(:num)', 'Teacher::deletePracticeQuiz/$1');
    $routes->get('practice-quizzes/results/(:num)', 'Teacher::practiceQuizResults/$1');

    // Fill-in-the-blanks (Điền vào chỗ trống) Management
    $routes->get('fill-blank/questions', 'Teacher::fillBlankQuestions');
    $routes->match(['get', 'post'], 'fill-blank/questions/create', 'Teacher::createFillBlankQuestion');
    $routes->match(['get', 'post'], 'fill-blank/questions/edit/(:num)', 'Teacher::editFillBlankQuestion/$1');
    $routes->get('fill-blank/questions/delete/(:num)', 'Teacher::deleteFillBlankQuestion/$1');
    
    $routes->get('fill-blank/quizzes', 'Teacher::fillBlankQuizzes');
    $routes->match(['get', 'post'], 'fill-blank/quizzes/create', 'Teacher::createFillBlankQuiz');
    $routes->match(['get', 'post'], 'fill-blank/quizzes/edit/(:num)', 'Teacher::editFillBlankQuiz/$1');
    $routes->get('fill-blank/quizzes/delete/(:num)', 'Teacher::deleteFillBlankQuiz/$1');

    $routes->post('fill-blank/sessions/create/(:num)', 'Teacher::createFillBlankSession/$1');
    $routes->get('fill-blank/sessions/control/(:num)', 'Teacher::fillBlankSessionControl/$1');
    $routes->post('fill-blank/sessions/approve/(:num)', 'Teacher::approveFillBlankParticipant/$1');
    $routes->post('fill-blank/sessions/reject/(:num)', 'Teacher::rejectFillBlankParticipant/$1');
});

// Student routes
$routes->group('student', ['filter' => 'role:student'], function ($routes) {
    $routes->get('dashboard', 'Student::dashboard');
    
    // Real Test Taking
    $routes->get('join-session/(:num)', 'Student::joinSession/$1');
    $routes->get('waiting-room/(:num)', 'Student::joinSession/$1');
    $routes->get('check-approval/(:num)', 'Student::checkApprovalStatus/$1');
    $routes->get('exam/(:num)', 'Student::startExam/$1');
    $routes->post('save-answer', 'Student::saveAnswer');
    $routes->post('submit-exam/(:num)', 'Student::submitExam/$1');
    $routes->get('exam-result/(:num)', 'Student::examResult/$1');

    // Mock Test
    $routes->get('mock-test/(:num)', 'Student::mockTest/$1');
    $routes->post('submit-mock-test', 'Student::submitMockTest');

    // Fill-in-the-blanks Student routes
    $routes->get('fill-blank/mock/(:num)', 'Student::fillBlankMock/$1');
    $routes->post('fill-blank/submit-mock/(:num)', 'Student::submitFillBlankMock/$1');

    $routes->get('fill-blank/join-session/(:num)', 'Student::joinFillBlankSession/$1');
    $routes->get('fill-blank/waiting-room/(:num)', 'Student::fillBlankWaitingRoom/$1');
    $routes->get('fill-blank/check-approval/(:num)', 'Student::checkFillBlankApproval/$1');
    $routes->get('fill-blank/exam/(:num)', 'Student::startFillBlankExam/$1');
    $routes->post('fill-blank/submit-exam/(:num)', 'Student::submitFillBlankExam/$1');
});

// Public Practice Quiz routes (accessible via shared link)
$routes->get('practice/(:segment)', 'PracticeQuiz::take/$1');
$routes->post('practice/(:segment)/submit', 'PracticeQuiz::submit/$1');

// API Polling routes
$routes->group('api', function ($routes) {
    $routes->get('session-participants/(:num)', 'Api::getSessionParticipants/$1');
    $routes->get('session-leaderboard/(:num)', 'Api::getSessionLeaderboard/$1');
});
