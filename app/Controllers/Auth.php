<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\SchoolModel;
use App\Models\ClassModel;
use App\Models\ClassStudentModel;

class Auth extends BaseController
{
    protected $userModel;
    protected $schoolModel;
    protected $classModel;
    protected $classStudentModel;

    public function __construct()
    {
        $this->userModel         = new UserModel();
        $this->schoolModel       = new SchoolModel();
        $this->classModel        = new ClassModel();
        $this->classStudentModel = new ClassStudentModel();
    }

    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to($this->getRedirectUrl(session()->get('user_role')));
        }

        if ($this->request->getMethod() === 'POST') {
            $username = trim((string)$this->request->getPost('username'));
            $password = (string)$this->request->getPost('password');

            $user = $this->userModel->findByUsername($username);
            if (!$user) {
                $user = $this->userModel->findByEmail($username);
            }

            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['status'] === 'pending') {
                    $msg = ($user['role'] === 'teacher') 
                        ? 'Tài khoản Giáo viên của bạn đang chờ Quản trị viên (Admin) phê duyệt.' 
                        : 'Tài khoản Học sinh của bạn đang chờ Giáo viên trong trường phê duyệt.';
                    return redirect()->back()->with('error', $msg);
                }
                if ($user['status'] === 'blocked' || $user['status'] === 'rejected') {
                    return redirect()->back()->with('error', 'Tài khoản của bạn đã bị từ chối hoặc khoá.');
                }

                session()->set([
                    'isLoggedIn' => true,
                    'user_id'    => (int)$user['id'],
                    'school_id'  => $user['school_id'] ? (int)$user['school_id'] : null,
                    'username'   => $user['username'],
                    'full_name'  => $user['full_name'],
                    'user_role'  => $user['role'],
                ]);

                return redirect()->to($this->getRedirectUrl($user['role']))->with('success', 'Đăng nhập thành công!');
            }

            return redirect()->back()->with('error', 'Tên đăng nhập hoặc mật khẩu không chính xác.');
        }

        return view('auth/login');
    }

    public function register()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to($this->getRedirectUrl(session()->get('user_role')));
        }

        if ($this->request->getMethod() === 'POST') {
            $role      = $this->request->getPost('role'); // teacher or student
            $schoolId  = (int)$this->request->getPost('school_id');
            $classId   = $this->request->getPost('class_id') ? (int)$this->request->getPost('class_id') : null;
            $username  = trim((string)$this->request->getPost('username'));
            $email     = trim((string)$this->request->getPost('email'));
            $password  = (string)$this->request->getPost('password');
            $fullName  = trim((string)$this->request->getPost('full_name'));

            // Basic validation
            if (empty($username) || empty($email) || empty($password) || empty($fullName) || empty($schoolId)) {
                return redirect()->back()->with('error', 'Vui lòng điền đầy đủ các thông tin bắt buộc.')->withInput();
            }

            if ($role === 'student' && empty($classId)) {
                return redirect()->back()->with('error', 'Học sinh bắt buộc phải chọn Lớp học.')->withInput();
            }

            if ($this->userModel->findByUsername($username)) {
                return redirect()->back()->with('error', 'Tên đăng nhập đã tồn tại.')->withInput();
            }

            if ($this->userModel->findByEmail($email)) {
                return redirect()->back()->with('error', 'Email đã được đăng ký.')->withInput();
            }

            // Student & Teacher both start as 'pending' for approval!
            $status = 'pending';

            $userId = $this->userModel->insert([
                'school_id'     => $schoolId,
                'username'      => $username,
                'email'         => $email,
                'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                'full_name'     => $fullName,
                'role'          => $role,
                'status'        => $status,
            ]);

            if ($role === 'student' && $classId) {
                $this->classStudentModel->insert([
                    'class_id'   => $classId,
                    'student_id' => $userId,
                ]);
                return redirect()->to(base_url('auth/login'))->with('success', 'Đăng ký thành công! Tài khoản Học sinh của bạn đang chờ Giáo viên trong trường phê duyệt trước khi đăng nhập.');
            }

            return redirect()->to(base_url('auth/login'))->with('success', 'Đăng ký thành công! Tài khoản Giáo viên của bạn đang chờ Admin phê duyệt trước khi đăng nhập.');
        }

        $schools = $this->schoolModel->where('status', 'active')->orderBy('name', 'ASC')->findAll();
        return view('auth/register', ['schools' => $schools]);
    }

    public function getClassesBySchool(int $schoolId)
    {
        $classes = $this->classModel->getClassesBySchool($schoolId);
        return $this->response->setJSON($classes);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('auth/login'))->with('success', 'Đã đăng xuất tài khoản.');
    }

    private function getRedirectUrl(string $role): string
    {
        switch ($role) {
            case 'admin':
                return base_url('admin/dashboard');
            case 'teacher':
                return base_url('teacher/dashboard');
            case 'student':
                return base_url('student/dashboard');
            default:
                return base_url('/');
        }
    }
}
