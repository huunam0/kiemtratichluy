<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEquivalentIdToQuestions extends Migration
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

        $this->forge->addColumn('questions', $fields);

        // Tạo index cho equivalent_id để tối ưu truy vấn theo nhóm
        $this->db->query("CREATE INDEX idx_questions_equivalent_id ON questions (equivalent_id)");

        // Khởi tạo các câu hỏi hiện có có equivalent_id bằng chính id của nó
        $this->db->query("UPDATE questions SET equivalent_id = id WHERE equivalent_id IS NULL");
    }

    public function down()
    {
        // Xóa index trước khi xóa cột
        $this->db->query("DROP INDEX idx_questions_equivalent_id ON questions");
        $this->forge->dropColumn('questions', 'equivalent_id');
    }
}
