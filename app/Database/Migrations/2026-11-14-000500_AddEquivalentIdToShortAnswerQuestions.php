<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEquivalentIdToShortAnswerQuestions extends Migration
{
    public function up()
    {
        $fields = [
            'equivalent_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'topic_id',
            ],
        ];

        $this->forge->addColumn('short_answer_questions', $fields);

        // Khởi tạo các câu hỏi hiện có có equivalent_id bằng chính id của nó
        $this->db->query("UPDATE short_answer_questions SET equivalent_id = id WHERE equivalent_id IS NULL");
    }

    public function down()
    {
        $this->forge->dropColumn('short_answer_questions', 'equivalent_id');
    }
}