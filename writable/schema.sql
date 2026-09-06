-- ==============================================================================
-- HỆ THỐNG KIỂM TRA TÍCH LUỸ (ACCUMULATED STUDENT TESTING SYSTEM)
-- Complete Database Schema (MySQL 8.0 / MariaDB 10.4+)
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `tichluy_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tichluy_db`;

-- 1. SCHOOLS TABLE
CREATE TABLE IF NOT EXISTS `schools` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL COMMENT 'Tên trường học',
    `code` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Mã trường học (duy nhất)',
    `address` VARCHAR(255) DEFAULT NULL COMMENT 'Địa chỉ',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Danh sách trường học';

-- 2. USERS TABLE
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT UNSIGNED DEFAULT NULL COMMENT 'Trường học của user (Admin có thể NULL)',
    `username` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Tên đăng nhập',
    `email` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Email liên hệ',
    `password_hash` VARCHAR(255) NOT NULL COMMENT 'Mật khẩu mã hoá bcrypt',
    `full_name` VARCHAR(100) NOT NULL COMMENT 'Họ và tên',
    `role` ENUM('admin', 'teacher', 'student') NOT NULL DEFAULT 'student' COMMENT 'Vai trò người dùng',
    `status` ENUM('pending', 'approved', 'rejected', 'blocked') NOT NULL DEFAULT 'pending' COMMENT 'Trạng thái tài khoản (Giáo viên cần Admin duyệt)',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_users_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX `idx_users_role_status` (`role`, `status`),
    INDEX `idx_users_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng người dùng hệ thống';

-- 3. CLASSES TABLE
CREATE TABLE IF NOT EXISTS `classes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `school_id` INT UNSIGNED NOT NULL COMMENT 'Thuộc trường học nào',
    `creator_teacher_id` INT UNSIGNED NOT NULL COMMENT 'Giáo viên khởi tạo',
    `name` VARCHAR(100) NOT NULL COMMENT 'Tên lớp (ví dụ: 10A1, 11B2)',
    `grade_level` TINYINT UNSIGNED NOT NULL DEFAULT 10 COMMENT 'Khối lớp (10, 11, 12)',
    `academic_year` VARCHAR(20) NOT NULL DEFAULT '2025-2026' COMMENT 'Năm học',
    `status` ENUM('active', 'archived') NOT NULL DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_classes_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_classes_creator` FOREIGN KEY (`creator_teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_classes_school` (`school_id`),
    INDEX `idx_classes_teacher` (`creator_teacher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng quản lý lớp học (GV cùng trường dùng chung)';

-- 4. CLASS_STUDENTS PIVOT TABLE
CREATE TABLE IF NOT EXISTS `class_students` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `class_id` INT UNSIGNED NOT NULL COMMENT 'Lớp học',
    `student_id` INT UNSIGNED NOT NULL COMMENT 'Học sinh',
    `joined_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_cs_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_cs_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `uk_class_student` (`class_id`, `student_id`),
    INDEX `idx_cs_student` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Liên kết Học sinh vào Lớp học';

-- 5. QUESTIONS TABLE (GLOBAL QUESTION BANK - DYNAMIC 2, 3, OR 4 OPTIONS)
CREATE TABLE IF NOT EXISTS `questions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `creator_id` INT UNSIGNED NOT NULL COMMENT 'Giáo viên tạo câu hỏi (Chỉ creator_id mới được sửa/xoá)',
    `subject` VARCHAR(100) DEFAULT 'Chung' COMMENT 'Môn học / Chủ đề',
    `grade_level` TINYINT UNSIGNED DEFAULT 10 COMMENT 'Khối lớp',
    `content` LONGTEXT NOT NULL COMMENT 'Nội dung câu hỏi (Rich Text HTML, Ảnh, Code Snippet)',
    `option_a` TEXT NOT NULL COMMENT 'Đáp án A (Rich Text HTML / Code) - Bắt buộc',
    `option_b` TEXT NOT NULL COMMENT 'Đáp án B (Rich Text HTML / Code) - Bắt buộc',
    `option_c` TEXT DEFAULT NULL COMMENT 'Đáp án C (Tuỳ chọn - NULL cho câu hỏi Đúng/Sai hoặc 2-3 đáp án)',
    `option_d` TEXT DEFAULT NULL COMMENT 'Đáp án D (Tuỳ chọn - NULL cho câu hỏi 2 hoặc 3 đáp án)',
    `correct_option` ENUM('A', 'B', 'C', 'D') NOT NULL COMMENT 'Đáp án đúng gốc (A, B, C hoặc D)',
    `explanation` TEXT DEFAULT NULL COMMENT 'Giải thích chi tiết đáp án',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_questions_creator` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_questions_creator` (`creator_id`),
    INDEX `idx_questions_subject_grade` (`subject`, `grade_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ngân hàng câu hỏi chung toàn hệ thống (PUBLIC)';

-- 6. TESTS TABLE (CUMULATIVE TESTS PER CLASS)
CREATE TABLE IF NOT EXISTS `tests` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `class_id` INT UNSIGNED NOT NULL COMMENT 'Bài kiểm tra tích luỹ gán cho Lớp học',
    `teacher_id` INT UNSIGNED NOT NULL COMMENT 'Giáo viên quản lý bài kiểm tra',
    `title` VARCHAR(255) NOT NULL COMMENT 'Tên bài kiểm tra tích luỹ (VD: Tích luỹ Toán 10 - HK1)',
    `description` TEXT DEFAULT NULL COMMENT 'Mô tả bài kiểm tra',
    `status` ENUM('active', 'closed', 'archived') NOT NULL DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_tests_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_tests_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_tests_class` (`class_id`),
    INDEX `idx_tests_teacher` (`teacher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bài kiểm tra tích luỹ của Lớp (Question Pool tích luỹ)';

-- 7. TEST_QUESTIONS_POOL PIVOT TABLE
CREATE TABLE IF NOT EXISTS `test_questions_pool` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `test_id` INT UNSIGNED NOT NULL COMMENT 'Bài kiểm tra tích luỹ',
    `question_id` INT UNSIGNED NOT NULL COMMENT 'Câu hỏi đưa vào pool tích luỹ',
    `added_by_teacher_id` INT UNSIGNED NOT NULL COMMENT 'Giáo viên thêm câu hỏi vào pool',
    `added_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_tqp_test` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_tqp_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_tqp_teacher` FOREIGN KEY (`added_by_teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `uk_test_question` (`test_id`, `question_id`),
    INDEX `idx_tqp_test` (`test_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kho câu hỏi tích luỹ cho từng Bài kiểm tra';

-- 8. TEST_SESSIONS TABLE (LƯỢT THI THỰC TẾ TRÊN LỚP)
CREATE TABLE IF NOT EXISTS `test_sessions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `test_id` INT UNSIGNED NOT NULL COMMENT 'Bài kiểm tra tích luỹ gốc',
    `teacher_id` INT UNSIGNED NOT NULL COMMENT 'Giáo viên mở lượt thi',
    `session_code` VARCHAR(20) NOT NULL UNIQUE COMMENT 'Mã lượt thi (VD: SESS-10A1-001 hoặc mã PIN)',
    `session_title` VARCHAR(255) NOT NULL COMMENT 'Tiêu đề lượt thi (VD: Lượt 1 - Bài kiểm tra 15 phút)',
    `num_questions` TINYINT UNSIGNED NOT NULL DEFAULT 5 COMMENT 'Số lượng câu hỏi bốc ngẫu nhiên (3 đến 6)',
    `time_per_question_sec` SMALLINT UNSIGNED NOT NULL DEFAULT 30 COMMENT 'Thời gian cho 1 câu (30s)',
    `total_duration_sec` SMALLINT UNSIGNED AS (`num_questions` * `time_per_question_sec`) STORED COMMENT 'Tổng thời gian đếm ngược (N * 30s)',
    `status` ENUM('waiting', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'waiting' COMMENT 'Trạng thái lượt thi',
    `started_at` DATETIME DEFAULT NULL COMMENT 'Thời điểm bấm Bắt đầu lượt thi',
    `ended_at` DATETIME DEFAULT NULL COMMENT 'Thời điểm kết thúc lượt thi',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_ts_test` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ts_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_ts_test_status` (`test_id`, `status`),
    INDEX `idx_ts_code` (`session_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Quản lý lượt thi thực tế (Real-time Session Control)';

-- 9. SESSION_PARTICIPANTS TABLE
CREATE TABLE IF NOT EXISTS `session_participants` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `session_id` INT UNSIGNED NOT NULL COMMENT 'Lượt thi',
    `student_id` INT UNSIGNED NOT NULL COMMENT 'Học sinh',
    `approval_status` ENUM('pending', 'approved', 'rejected', 'absent') NOT NULL DEFAULT 'pending' COMMENT 'Duyệt thời gian thực / Đánh vắng',
    `test_status` ENUM('waiting_approval', 'ready', 'in_test', 'submitted', 'timed_out', 'absent') NOT NULL DEFAULT 'waiting_approval',
    `score_correct` TINYINT UNSIGNED DEFAULT 0 COMMENT 'Số câu trả lời đúng lượt thi này',
    `score_total` TINYINT UNSIGNED DEFAULT 0 COMMENT 'Tổng số câu lượt thi này (N)',
    `score_base10` DECIMAL(4,2) DEFAULT NULL COMMENT 'Điểm lượt thi này (thang 10)',
    `joined_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `approved_at` DATETIME DEFAULT NULL,
    `started_at` DATETIME DEFAULT NULL,
    `submitted_at` DATETIME DEFAULT NULL,
    CONSTRAINT `fk_sp_session` FOREIGN KEY (`session_id`) REFERENCES `test_sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_sp_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `uk_session_student` (`session_id`, `student_id`),
    INDEX `idx_sp_session_approval` (`session_id`, `approval_status`),
    INDEX `idx_sp_student` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Học sinh tham gia lượt thi & Duyệt Real-time / Phạt vắng';

-- 10. SESSION_QUESTION_ANSWERS TABLE (SHUFFLED OPTIONS & ANSWERS)
CREATE TABLE IF NOT EXISTS `session_question_answers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `participant_id` INT UNSIGNED NOT NULL COMMENT 'Lượt làm bài học sinh',
    `question_id` INT UNSIGNED NOT NULL COMMENT 'Câu hỏi được bốc ngẫu nhiên',
    `question_order` TINYINT UNSIGNED NOT NULL COMMENT 'Thứ tự hiển thị (1..N)',
    `option_mapping` JSON NOT NULL COMMENT 'Ánh xạ xáo trộn đáp án: {"A":"C", "B":"A", "C":"D", "D":"B"}',
    `selected_option` ENUM('A', 'B', 'C', 'D') DEFAULT NULL COMMENT 'Đáp án học sinh chọn trên UI ngẫu nhiên',
    `original_selected_option` ENUM('A', 'B', 'C', 'D') DEFAULT NULL COMMENT 'Đáp án gốc tương ứng',
    `is_correct` TINYINT(1) DEFAULT NULL COMMENT '1 = Đúng, 0 = Sai (NULL khi chưa nộp bài)',
    `answered_at` DATETIME DEFAULT NULL,
    CONSTRAINT `fk_sqa_participant` FOREIGN KEY (`participant_id`) REFERENCES `session_participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_sqa_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `uk_participant_question` (`participant_id`, `question_id`),
    INDEX `idx_sqa_participant` (`participant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Chi tiết câu hỏi xáo trộn đáp án & kết quả lượt thi';

-- 11. ACCUMULATED_SCORES TABLE (CỘNG DỒN ĐIỂM TÍCH LUỸ)
CREATE TABLE IF NOT EXISTS `accumulated_scores` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT UNSIGNED NOT NULL COMMENT 'Học sinh',
    `test_id` INT UNSIGNED NOT NULL COMMENT 'Bài kiểm tra tích luỹ của Lớp',
    `total_correct` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Tổng số câu trả lời ĐÚNG tích luỹ',
    `total_attempted` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Tổng số câu ĐÃ LÀM tích luỹ (+3 nếu bị ĐÁNH VẮNG)',
    `accumulated_gpa` DECIMAL(4,2) GENERATED ALWAYS AS (
        CASE WHEN `total_attempted` > 0 
             THEN ROUND((`total_correct` / `total_attempted`) * 10, 2)
             ELSE 0.00 
        END
    ) STORED COMMENT 'Tự động tính Điểm tích luỹ Thang 10: (total_correct / total_attempted) * 10',
    `last_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_as_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_as_test` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `uk_student_test` (`student_id`, `test_id`),
    INDEX `idx_as_test_gpa` (`test_id`, `accumulated_gpa` DESC),
    INDEX `idx_as_student` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Điểm tích luỹ cộng dồn theo từng bài kiểm tra';

-- 12. MOCK_TEST_LOGS TABLE (KIỂM TRA THỬ)
CREATE TABLE IF NOT EXISTS `mock_test_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT UNSIGNED NOT NULL COMMENT 'Học sinh luyện tập tự do',
    `test_id` INT UNSIGNED NOT NULL COMMENT 'Bài kiểm tra tích luỹ được chọn luyện tập',
    `num_questions` TINYINT UNSIGNED NOT NULL DEFAULT 5,
    `score_correct` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `total_questions` TINYINT UNSIGNED NOT NULL DEFAULT 5,
    `completed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_mtl_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_mtl_test` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_mtl_student` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Nhật ký kiểm tra thử (Không cộng dồn vào điểm tích luỹ)';
