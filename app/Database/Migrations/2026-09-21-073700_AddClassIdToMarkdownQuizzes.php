<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddClassIdToMarkdownQuizzes extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('class_id', 'markdown_quizzes')) {
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

            try {
                $this->db->query('ALTER TABLE `markdown_quizzes` ADD INDEX `idx_mq_class` (`class_id`)');
            } catch (\Throwable $e) {}

            try {
                $this->db->query('ALTER TABLE `markdown_quizzes` ADD CONSTRAINT `fk_mq_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE');
            } catch (\Throwable $e) {}
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('class_id', 'markdown_quizzes')) {
            try {
                $this->db->query('ALTER TABLE `markdown_quizzes` DROP FOREIGN KEY `fk_mq_class`');
            } catch (\Throwable $e) {}
            $this->forge->dropColumn('markdown_quizzes', 'class_id');
        }
    }
}
