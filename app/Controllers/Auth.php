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
                    return redirect()->back()->with('error', 'Tài khoản của bạn đang chờ phê duyệt. Bạn hãy nhờ 1 giáo viên trong trường phê duyệt tài khoản của bạn.');
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
                return redirect()->to(base_url('auth/login'))->with('success', 'Đăng ký thành công! Bạn hãy nhờ 1 giáo viên trong trường phê duyệt tài khoản của bạn.');
            }

            return redirect()->to(base_url('auth/login'))->with('success', 'Đăng ký thành công! Bạn hãy nhờ 1 giáo viên trong trường phê duyệt tài khoản của bạn.');
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

    public function profile()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('auth/login'));
        }

        $userId = (int)session()->get('user_id');
        $user   = $this->userModel->find($userId);

        if (!$user) {
            session()->destroy();
            return redirect()->to(base_url('auth/login'));
        }

        if ($this->request->getMethod() === 'POST') {
            $action = $this->request->getPost('action');

            if ($action === 'update_info') {
                $fullName = trim((string)$this->request->getPost('full_name'));
                $email    = trim((string)$this->request->getPost('email'));

                if (empty($fullName) || empty($email)) {
                    return redirect()->back()->with('error', 'Họ tên và Email không được để trống.')->withInput();
                }

                // Check email unique if changed
                if ($email !== $user['email']) {
                    $exist = $this->userModel->findByEmail($email);
                    if ($exist) {
                        return redirect()->back()->with('error', 'Email này đã được tài khoản khác sử dụng.')->withInput();
                    }
                }

                $this->userModel->update($userId, [
                    'full_name' => $fullName,
                    'email'     => $email,
                ]);

                session()->set('full_name', $fullName);

                return redirect()->to(base_url('profile'))->with('success', 'Cập nhật thông tin cá nhân thành công!');

            } elseif ($action === 'change_password') {
                $currentPassword = (string)$this->request->getPost('current_password');
                $newPassword     = (string)$this->request->getPost('new_password');
                $confirmPassword = (string)$this->request->getPost('confirm_password');

                if (!password_verify($currentPassword, $user['password_hash'])) {
                    return redirect()->back()->with('error', 'Mật khẩu hiện tại không chính xác.');
                }

                if (strlen($newPassword) < 6) {
                    return redirect()->back()->with('error', 'Mật khẩu mới phải có ít nhất 6 ký tự.');
                }

                if ($newPassword !== $confirmPassword) {
                    return redirect()->back()->with('error', 'Mật khẩu xác nhận không khớp.');
                }

                $this->userModel->update($userId, [
                    'password_hash' => password_hash($newPassword, PASSWORD_BCRYPT),
                ]);

                return redirect()->to(base_url('profile'))->with('success', 'Đổi mật khẩu thành công!');
            }
        }

        return view('auth/profile', ['user' => $user]);
    }

    public function forgotPassword()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to($this->getRedirectUrl(session()->get('user_role')));
        }

        if ($this->request->getMethod() === 'POST') {
            $identity = trim((string)$this->request->getPost('username')); // username or email

            if (empty($identity)) {
                return redirect()->back()->with('error', 'Vui lòng nhập Tên đăng nhập hoặc Email.')->withInput();
            }

            $user = $this->userModel->findByUsername($identity);
            if (!$user) {
                $user = $this->userModel->findByEmail($identity);
            }

            if ($user) {
                $resetModel = new \App\Models\PasswordResetRequestModel();
                $existing = $resetModel->where('user_id', $user['id'])
                                       ->where('status', 'pending')
                                       ->first();

                if ($existing) {
                    return redirect()->to(base_url('auth/login'))->with('success', 'Bạn đã gửi yêu cầu đặt lại mật khẩu rồi! Hãy báo Giáo viên trong trường của bạn để được duyệt và nhận mật khẩu mới.');
                }

                $resetModel->insert([
                    'user_id'    => $user['id'],
                    'school_id'  => $user['school_id'] ?: 1,
                    'username'   => $user['username'],
                    'full_name'  => $user['full_name'],
                    'role'       => $user['role'],
                    'status'     => 'pending',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                return redirect()->to(base_url('auth/login'))->with('success', 'Đã gửi Yêu cầu đặt lại mật khẩu thành công! Bạn hãy báo Giáo viên trong trường của bạn duyệt và đặt lại mật khẩu.');
            }

            return redirect()->back()->with('error', 'Không tìm thấy tài khoản tương ứng với thông tin nhập vào.')->withInput();
        }

        return view('auth/forgot_password');
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
