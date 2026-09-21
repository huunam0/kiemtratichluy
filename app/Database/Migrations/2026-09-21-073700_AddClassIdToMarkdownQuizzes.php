<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddClassIdToMarkdownQuizzes extends Migration
{
    public function up()
    {
        $this->forge->addColumn('markdown_quizzes', [
            'class_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'school_id',
            ],
        ]);

        $this->db->query('ALTER TABLE `markdown_quizzes` ADD INDEX `idx_mq_class` (`class_id`)');
        $this->db->query('ALTER TABLE `markdown_quizzes` ADD CONSTRAINT `fk_mq_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE `markdown_quizzes` DROP FOREIGN KEY `fk_mq_class`');
        $this->forge->dropColumn('markdown_quizzes', 'class_id');
    }
}
