<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordResetRequestModel extends Model
{
    protected $table            = 'password_reset_requests';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 'school_id', 'username', 'full_name', 
        'role', 'status', 'reset_by_teacher_id', 
        'created_at', 'completed_at'
    ];

    public function getPendingRequestsBySchool(int $schoolId)
    {
        return $this->where('school_id', $schoolId)
                    ->where('status', 'pending')
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }

    public function getAllPendingRequests()
    {
        return $this->select('password_reset_requests.*, schools.name as school_name')
                    ->join('schools', 'schools.id = password_reset_requests.school_id', 'left')
                    ->where('password_reset_requests.status', 'pending')
                    ->orderBy('password_reset_requests.id', 'DESC')
                    ->findAll();
    }
}
