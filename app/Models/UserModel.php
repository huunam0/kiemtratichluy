<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'school_id', 'username', 'email', 'password_hash', 
        'full_name', 'role', 'status'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findByUsername(string $username)
    {
        return $this->where('username', $username)->first();
    }

    public function findByEmail(string $email)
    {
        return $this->where('email', $email)->first();
    }

    public function getPendingTeachers()
    {
        return $this->select('users.*, schools.name as school_name')
                    ->join('schools', 'schools.id = users.school_id', 'left')
                    ->where('users.role', 'teacher')
                    ->where('users.status', 'pending')
                    ->findAll();
    }

    public function getStudentsByClass(int $classId)
    {
        return $this->select('users.*, class_students.joined_at')
                    ->join('class_students', 'class_students.student_id = users.id')
                    ->where('class_students.class_id', $classId)
                    ->where('users.role', 'student')
                    ->findAll();
    }

    public function getPendingStudentsBySchool(int $schoolId)
    {
        return $this->select('users.*, classes.name as class_name')
                    ->join('class_students', 'class_students.student_id = users.id', 'left')
                    ->join('classes', 'classes.id = class_students.class_id', 'left')
                    ->where('users.school_id', $schoolId)
                    ->where('users.role', 'student')
                    ->where('users.status', 'pending')
                    ->orderBy('users.id', 'DESC')
                    ->findAll();
    }
}
