<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFillBlankTables extends Migration
{
    public function up()
    {
        // 1. fill_blank_questions
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'teacher_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'school_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'grade_level' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('fill_blank_questions', true);

        // 2. fill_blank_question_variants
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'question_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'variant_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'content_raw' => [
                'type' => 'TEXT',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('question_id');
        $this->forge->createTable('fill_blank_question_variants', true);

        // 3. fill_blank_quizzes
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'teacher_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'school_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'class_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'time_limit' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 15,
            ],
            'selected_question_ids' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('fill_blank_quizzes', true);

        // 4. fill_blank_sessions
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'quiz_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'teacher_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'class_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'session_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['waiting', 'in_progress', 'completed', 'cancelled'],
                'default'    => 'waiting',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('fill_blank_sessions', true);

        // 5. fill_blank_participants
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'session_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'student_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'approval_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected', 'absent'],
                'default'    => 'pending',
            ],
            'test_status' => [
                'type'       => 'ENUM',
                'constraint' => ['waiting_approval', 'in_test', 'submitted', 'timed_out'],
                'default'    => 'waiting_approval',
            ],
            'score_correct' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'score_total' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'score_base10' => [
                'type'       => 'FLOAT',
                'default'    => 0,
            ],
            'joined_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'approved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'started_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'submitted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('fill_blank_participants', true);

        // 6. fill_blank_results
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'quiz_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'session_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'student_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'is_mock' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'score_correct' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'total_blanks' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'score_base10' => [
                'type'       => 'FLOAT',
                'default'    => 0,
            ],
            'student_answers' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('fill_blank_results', true);
    }

    public function down()
    {
        $this->forge->dropTable('fill_blank_results', true);
        $this->forge->dropTable('fill_blank_participants', true);
        $this->forge->dropTable('fill_blank_sessions', true);
        $this->forge->dropTable('fill_blank_quizzes', true);
        $this->forge->dropTable('fill_blank_question_variants', true);
        $this->forge->dropTable('fill_blank_questions', true);
    }
}
