<?php

namespace App\Models;

use CodeIgniter\Model;

class ClassStudentModel extends Model
{
    protected $table            = 'class_students';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['class_id', 'student_id', 'joined_at'];

    public function getStudentClasses(int $studentId)
    {
        return $this->select('classes.*, schools.name as school_name, users.full_name as teacher_name')
                    ->join('classes', 'classes.id = class_students.class_id')
                    ->join('schools', 'schools.id = classes.school_id', 'left')
                    ->join('users', 'users.id = classes.creator_teacher_id', 'left')
                    ->where('class_students.student_id', $studentId)
                    ->findAll();
    }
}
