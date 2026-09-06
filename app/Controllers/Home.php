<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        if (session()->get('isLoggedIn')) {
            $role = session()->get('user_role');
            switch ($role) {
                case 'admin':
                    return redirect()->to(base_url('admin/dashboard'));
                case 'teacher':
                    return redirect()->to(base_url('teacher/dashboard'));
                case 'student':
                    return redirect()->to(base_url('student/dashboard'));
            }
        }

        return redirect()->to(base_url('auth/login'));
    }
}
