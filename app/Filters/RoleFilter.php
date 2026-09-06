<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to(base_url('auth/login'));
        }

        $userRole = $session->get('user_role');
        if (!empty($arguments) && !in_array($userRole, $arguments)) {
            return redirect()->to(base_url('/'))->with('error', 'Bạn không có quyền truy cập trang này.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
