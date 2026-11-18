<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPrefilledTextToShortAnswerQuestions extends Migration
{
    public function up()
    {
        $fields = [
            'prefilled_text' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'question_text',
            ],
        ];

        $this->forge->addColumn('short_answer_questions', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('short_answer_questions', 'prefilled_text');
    }
}
