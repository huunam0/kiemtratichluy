<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTopicsAndSubjects extends Migration
{
    public function up()
    {
        // 1. Create `subjects` table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
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
        $this->forge->createTable('subjects');

        // 2. Create `topics` table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'subject_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'grade_level' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
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
        $this->forge->addForeignKey('subject_id', 'subjects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('topics');

        // 3. Add `topic_id` to questions
        $this->forge->addColumn('questions', [
            'topic_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);
        
        $this->forge->addColumn('fill_blank_questions', [
            'topic_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);

        // 4. Data Migration
        $db = \Config\Database::connect();
        
        // 4.1 Collect all distinct subjects
        $subjects = [];
        $query1 = $db->query("SELECT DISTINCT subject FROM questions WHERE subject IS NOT NULL AND subject != ''");
        foreach ($query1->getResult() as $row) {
            $subjects[$row->subject] = true;
        }
        $query2 = $db->query("SELECT DISTINCT subject FROM fill_blank_questions WHERE subject IS NOT NULL AND subject != ''");
        foreach ($query2->getResult() as $row) {
            $subjects[$row->subject] = true;
        }
        
        $subjectMap = []; // old_name => new_id
        foreach (array_keys($subjects) as $subjName) {
            $db->table('subjects')->insert([
                'name' => $subjName,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $subjectMap[$subjName] = $db->insertID();
        }

        // 4.2 Collect all distinct (subject, grade_level) pairs
        $topicsMap = []; // "subject_name|grade_level" => topic_id
        $pairsQuery1 = $db->query("SELECT DISTINCT subject, grade_level FROM questions WHERE subject IS NOT NULL AND subject != ''");
        foreach ($pairsQuery1->getResult() as $row) {
            $key = $row->subject . '|' . $row->grade_level;
            if (!isset($topicsMap[$key])) {
                $db->table('topics')->insert([
                    'subject_id' => $subjectMap[$row->subject],
                    'grade_level' => $row->grade_level ?: 1, // fallback to 1 if null
                    'name' => 'Chung',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $topicsMap[$key] = $db->insertID();
            }
        }
        $pairsQuery2 = $db->query("SELECT DISTINCT subject, grade_level FROM fill_blank_questions WHERE subject IS NOT NULL AND subject != ''");
        foreach ($pairsQuery2->getResult() as $row) {
            $key = $row->subject . '|' . $row->grade_level;
            if (!isset($topicsMap[$key])) {
                $db->table('topics')->insert([
                    'subject_id' => $subjectMap[$row->subject],
                    'grade_level' => $row->grade_level ?: 1, // fallback to 1 if null
                    'name' => 'Chung',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $topicsMap[$key] = $db->insertID();
            }
        }

        // 4.3 Update tables with topic_id
        $questions = $db->table('questions')->get()->getResult();
        foreach ($questions as $q) {
            if ($q->subject) {
                $key = $q->subject . '|' . $q->grade_level;
                if (isset($topicsMap[$key])) {
                    $db->table('questions')->where('id', $q->id)->update(['topic_id' => $topicsMap[$key]]);
                }
            }
        }

        $fbQuestions = $db->table('fill_blank_questions')->get()->getResult();
        foreach ($fbQuestions as $q) {
            if ($q->subject) {
                $key = $q->subject . '|' . $q->grade_level;
                if (isset($topicsMap[$key])) {
                    $db->table('fill_blank_questions')->where('id', $q->id)->update(['topic_id' => $topicsMap[$key]]);
                }
            }
        }
        
        // 5. Drop old columns
        $this->forge->dropColumn('questions', 'subject');
        $this->forge->dropColumn('questions', 'grade_level');
        
        $this->forge->dropColumn('fill_blank_questions', 'subject');
        $this->forge->dropColumn('fill_blank_questions', 'grade_level');
        
        // Setup foreign keys for topic_id
        $db->query("ALTER TABLE questions ADD CONSTRAINT fk_question_topic FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE SET NULL ON UPDATE CASCADE");
        $db->query("ALTER TABLE fill_blank_questions ADD CONSTRAINT fk_fb_question_topic FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE SET NULL ON UPDATE CASCADE");
    }

    public function down()
    {
        $db = \Config\Database::connect();
        
        $db->query("ALTER TABLE questions DROP FOREIGN KEY fk_question_topic");
        $db->query("ALTER TABLE fill_blank_questions DROP FOREIGN KEY fk_fb_question_topic");
        
        $this->forge->dropColumn('questions', 'topic_id');
        $this->forge->dropColumn('fill_blank_questions', 'topic_id');
        
        $this->forge->addColumn('questions', [
            'subject' => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'grade_level' => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'null' => true],
        ]);
        
        $this->forge->addColumn('fill_blank_questions', [
            'subject' => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'grade_level' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
        ]);
        
        $this->forge->dropTable('topics', true);
        $this->forge->dropTable('subjects', true);
    }
}
