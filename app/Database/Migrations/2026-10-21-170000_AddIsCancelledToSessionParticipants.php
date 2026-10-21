<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsCancelledToSessionParticipants extends Migration
{
    public function up()
    {
        $fields = [];
        if (!$this->db->fieldExists('is_cancelled', 'session_participants')) {
            $fields['is_cancelled'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'comment'    => '1 = Đã bị giáo viên huỷ bỏ kết quả (không tính vào điểm tích luỹ)',
            ];
        }
        if (!$this->db->fieldExists('cancelled_at', 'session_participants')) {
            $fields['cancelled_at'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }
        if (!$this->db->fieldExists('cancelled_reason', 'session_participants')) {
            $fields['cancelled_reason'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ];
        }

        if (!empty($fields)) {
            $this->forge->addColumn('session_participants', $fields);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('is_cancelled', 'session_participants')) {
            $this->forge->dropColumn('session_participants', 'is_cancelled');
        }
        if ($this->db->fieldExists('cancelled_at', 'session_participants')) {
            $this->forge->dropColumn('session_participants', 'cancelled_at');
        }
        if ($this->db->fieldExists('cancelled_reason', 'session_participants')) {
            $this->forge->dropColumn('session_participants', 'cancelled_reason');
        }
    }
}
