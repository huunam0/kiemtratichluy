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
        $data = [
            'pending_teachers' => $this->userModel->getPendingTeachers(),
            'schools_count'    => $this->schoolModel->countAllResults(),
            'teachers_count'   => $this->userModel->where('role', 'teacher')->where('status', 'approved')->countAllResults(),
            'students_count'   => $this->userModel->where('role', 'student')->countAllResults(),
            'questions_count'  => $this->questionModel->countAllResults(),
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
}
