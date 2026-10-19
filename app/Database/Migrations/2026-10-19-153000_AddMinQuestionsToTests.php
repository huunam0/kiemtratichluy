<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMinQuestionsToTests extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('min_questions', 'tests')) {
            $this->forge->addColumn('tests', [
                'min_questions' => [
                    'type'       => 'SMALLINT',
                    'constraint' => 5,
                    'unsigned'   => true,
                    'default'    => 20,
                    'null'       => false,
                    'comment'    => 'So cau hoi toi thieu hoc sinh phai lam',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('min_questions', 'tests')) {
            $this->forge->dropColumn('tests', 'min_questions');
        }
    }
}
