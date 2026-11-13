<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShortAnswerTables extends Migration
{
    public function up()
    {
        // 1. short_answer_questions
        $this->forge->addField([
            'id'           => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'teacher_id'   => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'school_id'    => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'null'=>true],
            'topic_id'     => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'null'=>true],
            'title'        => ['type'=>'VARCHAR','constraint'=>255],
            'question_text'=> ['type'=>'TEXT'],
            'created_at'   => ['type'=>'DATETIME','null'=>true],
            'updated_at'   => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('teacher_id');
        $this->forge->createTable('short_answer_questions', true);

        // 2. short_answer_accepted_answers
        $this->forge->addField([
            'id'             => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'question_id'    => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'answer_text'    => ['type'=>'VARCHAR','constraint'=>500],
            'case_sensitive' => ['type'=>'TINYINT','constraint'=>1,'default'=>0],
            'space_sensitive'=> ['type'=>'TINYINT','constraint'=>1,'default'=>0],
            'sort_order'     => ['type'=>'INT','constraint'=>11,'default'=>0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('question_id');
        $this->forge->createTable('short_answer_accepted_answers', true);

        // 3. short_answer_quizzes
        $this->forge->addField([
            'id'                  => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'teacher_id'          => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'school_id'           => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'null'=>true],
            'class_id'            => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'null'=>true],
            'title'               => ['type'=>'VARCHAR','constraint'=>255],
            'time_limit'          => ['type'=>'INT','constraint'=>11,'default'=>15],
            'shuffle_questions'   => ['type'=>'TINYINT','constraint'=>1,'default'=>1],
            'penalty_coefficient' => ['type'=>'DECIMAL','constraint'=>'5,2','default'=>'0.00'],
            'is_open'             => ['type'=>'TINYINT','constraint'=>1,'default'=>0],
            'allow_mock'          => ['type'=>'TINYINT','constraint'=>1,'default'=>1],
            'created_at'          => ['type'=>'DATETIME','null'=>true],
            'updated_at'          => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('teacher_id');
        $this->forge->addKey('class_id');
        $this->forge->createTable('short_answer_quizzes', true);

        // 4. short_answer_quiz_questions
        $this->forge->addField([
            'id'          => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'quiz_id'     => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'question_id' => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'points'      => ['type'=>'DECIMAL','constraint'=>'8,2','default'=>'1.00'],
            'sort_order'  => ['type'=>'INT','constraint'=>11,'default'=>0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('quiz_id');
        $this->forge->createTable('short_answer_quiz_questions', true);

        // 5. short_answer_sessions
        $this->forge->addField([
            'id'         => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'quiz_id'    => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'teacher_id' => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'class_id'   => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'status'     => ['type'=>'ENUM','constraint'=>['open','closed'],'default'=>'open'],
            'opened_at'  => ['type'=>'DATETIME','null'=>true],
            'closed_at'  => ['type'=>'DATETIME','null'=>true],
            'created_at' => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('quiz_id');
        $this->forge->createTable('short_answer_sessions', true);

        // 6. short_answer_submissions
        $this->forge->addField([
            'id'               => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'session_id'       => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'null'=>true],
            'quiz_id'          => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'student_id'       => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'is_mock'          => ['type'=>'TINYINT','constraint'=>1,'default'=>0],
            'student_answers'  => ['type'=>'TEXT','null'=>true],
            'raw_score'        => ['type'=>'DECIMAL','constraint'=>'8,2','default'=>'0.00'],
            'final_score'      => ['type'=>'DECIMAL','constraint'=>'8,2','default'=>'0.00'],
            'penalty_deducted' => ['type'=>'DECIMAL','constraint'=>'8,2','default'=>'0.00'],
            'submitted_at'     => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('session_id');
        $this->forge->addKey('student_id');
        $this->forge->createTable('short_answer_submissions', true);
    }

    public function down()
    {
        $this->forge->dropTable('short_answer_submissions', true);
        $this->forge->dropTable('short_answer_sessions', true);
        $this->forge->dropTable('short_answer_quiz_questions', true);
        $this->forge->dropTable('short_answer_quizzes', true);
        $this->forge->dropTable('short_answer_accepted_answers', true);
        $this->forge->dropTable('short_answer_questions', true);
    }
}
