<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAllowMockToTestsAndMarkdownQuizzes extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('allow_mock', 'tests')) {
            $this->forge->addColumn('tests', [
                'allow_mock' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                    'null'       => false,
                    'comment'    => 'Cho phép HS kiểm tra thử (1: Có, 0: Không)',
                ],
            ]);
        }

        if (!$this->db->fieldExists('allow_mock', 'markdown_quizzes')) {
            $this->forge->addColumn('markdown_quizzes', [
                'allow_mock' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                    'null'       => false,
                    'comment'    => 'Cho phép HS kiểm tra thử (1: Có, 0: Không)',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('allow_mock', 'tests')) {
            $this->forge->dropColumn('tests', 'allow_mock');
        }
        if ($this->db->fieldExists('allow_mock', 'markdown_quizzes')) {
            $this->forge->dropColumn('markdown_quizzes', 'allow_mock');
        }
    }
}
