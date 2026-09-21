<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\SchoolModel;
use App\Models\ClassModel;
use App\Models\QuestionModel;

class Admin extends BaseController
{
    protected $userModel;
    protected $schoolModel;
    protected $classModel;
    protected $questionModel;

    public function __construct()
    {
        $this->userModel     = new UserModel();
        $this->schoolModel   = new SchoolModel();
        $this->classModel    = new ClassModel();
        $this->questionModel = new QuestionModel();
    }

    public function dashboard()
    {
        $resetModel = new \App\Models\PasswordResetRequestModel();
        $pendingResets = $resetModel->getAllPendingRequests();

        $data = [
            'pending_teachers'        => $this->userModel->getPendingTeachers(),
            'pending_reset_requests'  => $pendingResets,
            'schools_count'           => $this->schoolModel->countAllResults(),
            'teachers_count'          => $this->userModel->where('role', 'teacher')->where('status', 'approved')->countAllResults(),
            'students_count'          => $this->userModel->where('role', 'student')->countAllResults(),
            'questions_count'         => $this->questionModel->countAllResults(),
        ];

        return view('admin/dashboard', $data);
    }

    public function approveTeacher(int $teacherId)
    {
        $this->userModel->update($teacherId, ['status' => 'approved']);
        return redirect()->to(base_url('admin/dashboard'))->with('success', 'Đã phê duyệt tài khoản Giáo viên!');
    }

    public function rejectTeacher(int $teacherId)
    {
        $this->userModel->update($teacherId, ['status' => 'rejected']);
        return redirect()->to(base_url('admin/dashboard'))->with('success', 'Đã từ chối tài khoản Giáo viên.');
    }

    public function schools()
    {
        if ($this->request->getMethod() === 'POST') {
            $name    = trim((string)$this->request->getPost('name'));
            $code    = trim((string)$this->request->getPost('code'));
            $address = trim((string)$this->request->getPost('address'));

            if (!empty($name) && !empty($code)) {
                $this->schoolModel->insert([
                    'name'    => $name,
                    'code'    => strtoupper($code),
                    'address' => $address,
                    'status'  => 'active'
                ]);
                return redirect()->to(base_url('admin/schools'))->with('success', 'Thêm trường học thành công!');
            }
            return redirect()->back()->with('error', 'Tên và Mã trường không được để trống.');
        }

        $schools = $this->schoolModel->orderBy('id', 'DESC')->findAll();
        return view('admin/schools', ['schools' => $schools]);
    }

    public function subjects()
    {
        $subjectModel = new \App\Models\SubjectModel();
        
        if ($this->request->getMethod() === 'POST') {
            $action = $this->request->getPost('action');
            if ($action === 'create') {
                $name = trim((string)$this->request->getPost('name'));
                if (!empty($name)) {
                    $subjectModel->insert(['name' => $name]);
                    return redirect()->to(base_url('admin/subjects'))->with('success', 'Thêm môn học thành công!');
                }
                return redirect()->back()->with('error', 'Tên môn học không được để trống.');
            } elseif ($action === 'update') {
                $id = (int)$this->request->getPost('id');
                $name = trim((string)$this->request->getPost('name'));
                if ($id > 0 && !empty($name)) {
                    $subjectModel->update($id, ['name' => $name]);
                    return redirect()->to(base_url('admin/subjects'))->with('success', 'Cập nhật môn học thành công!');
                }
                return redirect()->back()->with('error', 'Dữ liệu không hợp lệ.');
            } elseif ($action === 'delete') {
                $id = (int)$this->request->getPost('id');
                if ($id > 0) {
                    $subjectModel->delete($id);
                    return redirect()->to(base_url('admin/subjects'))->with('success', 'Xóa môn học thành công!');
                }
            }
        }

        $subjects = $subjectModel->orderBy('id', 'DESC')->findAll();
        return view('admin/subjects', ['subjects' => $subjects]);
    }

    // --------------------------------------------------------------------
    // USER MANAGEMENT & PASSWORD RESET
    // --------------------------------------------------------------------
    public function users()
    {
        $resetModel = new \App\Models\PasswordResetRequestModel();

        $filters = [
            'keyword'   => trim((string)$this->request->getGet('keyword')),
            'role'      => trim((string)$this->request->getGet('role')),
            'school_id' => $this->request->getGet('school_id') ? (int)$this->request->getGet('school_id') : '',
            'status'    => trim((string)$this->request->getGet('status')),
        ];

        $users         = $this->userModel->getUsersFiltered($filters, 100);
        $totalUsers    = $this->userModel->countUsersFiltered($filters);
        $schools       = $this->schoolModel->orderBy('name', 'ASC')->findAll();
        $pendingResets = $resetModel->getAllPendingRequests();

        return view('admin/users', [
            'users'                  => $users,
            'totalUsers'             => $totalUsers,
            'schools'                => $schools,
            'filters'                => $filters,
            'pending_reset_requests' => $pendingResets,
        ]);
    }

    public function resetUserPassword(int $userId)
    {
        $adminId = (int)session()->get('user_id');

        $targetUser = $this->userModel->find($userId);
        if (!$targetUser) {
            return redirect()->back()->with('error', 'Không tìm thấy tài khoản người dùng.');
        }

        if ($userId === $adminId) {
            return redirect()->back()->with('error', 'Bạn không thể reset tài khoản của chính mình ở đây. Vui lòng vào trang Hồ sơ để đổi mật khẩu.');
        }

        if ($this->request->getMethod() === 'POST') {
            $newPass = trim((string)$this->request->getPost('new_password')) ?: '123456';

            if (strlen($newPass) < 6) {
                return redirect()->back()->with('error', 'Mật khẩu mới phải từ 6 ký tự trở lên.');
            }

            // Update user password
            $this->userModel->update($userId, [
                'password_hash' => password_hash($newPass, PASSWORD_BCRYPT),
            ]);

            // If this user has pending password reset requests, mark them completed
            $resetModel = new \App\Models\PasswordResetRequestModel();
            $pendingReqs = $resetModel->where('user_id', $userId)->where('status', 'pending')->findAll();
            foreach ($pendingReqs as $pr) {
                $resetModel->update($pr['id'], [
                    'status'              => 'completed',
                    'reset_by_teacher_id' => $adminId,
                    'completed_at'        => date('Y-m-d H:i:s'),
                ]);
            }

            $roleText = $targetUser['role'] === 'student' ? 'học sinh' : ($targetUser['role'] === 'teacher' ? 'giáo viên' : 'quản trị');
            return redirect()->back()->with('success', "Đã đặt lại mật khẩu cho {$roleText} \"{$targetUser['full_name']}\" (@{$targetUser['username']}) thành: {$newPass}");
        }

        return redirect()->back();
    }

    public function approvePasswordRequest(int $requestId)
    {
        $adminId    = (int)session()->get('user_id');
        $resetModel = new \App\Models\PasswordResetRequestModel();

        $req = $resetModel->find($requestId);
        if (!$req || $req['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Yêu cầu đặt lại mật khẩu không tồn tại hoặc đã được xử lý.');
        }

        $targetUser = $this->userModel->find($req['user_id']);
        if (!$targetUser) {
            return redirect()->back()->with('error', 'Tài khoản người dùng không tồn tại.');
        }

        $newPass = trim((string)$this->request->getPost('new_password')) ?: '123456';
        if (strlen($newPass) < 6) {
            return redirect()->back()->with('error', 'Mật khẩu mới phải từ 6 ký tự trở lên.');
        }

        // Update password
        $this->userModel->update($targetUser['id'], [
            'password_hash' => password_hash($newPass, PASSWORD_BCRYPT),
        ]);

        // Complete request
        $resetModel->update($requestId, [
            'status'              => 'completed',
            'reset_by_teacher_id' => $adminId,
            'completed_at'        => date('Y-m-d H:i:s'),
        ]);

        $roleText = $targetUser['role'] === 'student' ? 'học sinh' : 'giáo viên';
        return redirect()->back()->with('success', "Đã phê duyệt và đặt lại mật khẩu cho {$roleText} \"{$targetUser['full_name']}\" (@{$targetUser['username']}) thành: {$newPass}");
    }

    public function cancelPasswordRequest(int $requestId)
    {
        $adminId    = (int)session()->get('user_id');
        $resetModel = new \App\Models\PasswordResetRequestModel();

        $req = $resetModel->find($requestId);
        if ($req && $req['status'] === 'pending') {
            $resetModel->update($requestId, [
                'status'              => 'cancelled',
                'reset_by_teacher_id' => $adminId,
                'completed_at'        => date('Y-m-d H:i:s'),
            ]);
            return redirect()->back()->with('success', 'Đã hủy yêu cầu đặt lại mật khẩu.');
        }

        return redirect()->back()->with('error', 'Yêu cầu không tồn tại hoặc đã được xử lý.');
    }
}
