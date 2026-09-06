<?php

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db   = 'tichluy_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Disable foreign key checks for clean seeding
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("TRUNCATE TABLE schools;");
    $pdo->exec("TRUNCATE TABLE users;");
    $pdo->exec("TRUNCATE TABLE classes;");
    $pdo->exec("TRUNCATE TABLE class_students;");
    $pdo->exec("TRUNCATE TABLE questions;");
    $pdo->exec("TRUNCATE TABLE tests;");
    $pdo->exec("TRUNCATE TABLE test_questions_pool;");
    $pdo->exec("TRUNCATE TABLE test_sessions;");
    $pdo->exec("TRUNCATE TABLE session_participants;");
    $pdo->exec("TRUNCATE TABLE session_question_answers;");
    $pdo->exec("TRUNCATE TABLE accumulated_scores;");
    $pdo->exec("TRUNCATE TABLE mock_test_logs;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    // 1. Seed Schools
    $pdo->exec("INSERT INTO schools (id, name, code, address, status) VALUES 
        (1, 'THPT Lê Hồng Phong', 'LHP', 'TP. Hồ Chí Minh', 'active'),
        (2, 'THPT Chuyên Quốc Học', 'QUOCHOC', 'TP. Huế', 'active');");

    // 2. Seed Users
    $passwordHash = password_hash('123456', PASSWORD_BCRYPT);

    $pdo->exec("INSERT INTO users (id, school_id, username, email, password_hash, full_name, role, status) VALUES 
        (1, NULL, 'admin', 'admin@tichluy.edu.vn', '{$passwordHash}', 'Quản Trị Viên Hệ Thống', 'admin', 'approved'),
        (2, 1, 'teacher1', 'thaydung@lhp.edu.vn', '{$passwordHash}', 'Thầy Nguyễn Văn Dũng', 'teacher', 'approved'),
        (3, 1, 'teacher2', 'coha@lhp.edu.vn', '{$passwordHash}', 'Cô Trần Thị Thu Hà', 'teacher', 'pending'),
        (4, 1, 'student1', 'an.nguyen@lhp.edu.vn', '{$passwordHash}', 'Nguyễn Văn An', 'student', 'approved'),
        (5, 1, 'student2', 'binh.le@lhp.edu.vn', '{$passwordHash}', 'Lê Thi Bình', 'student', 'approved'),
        (6, 1, 'student3', 'cuong.pham@lhp.edu.vn', '{$passwordHash}', 'Phạm Quốc Cường', 'student', 'approved');");

    // 3. Seed Classes
    $pdo->exec("INSERT INTO classes (id, school_id, creator_teacher_id, name, grade_level, academic_year, status) VALUES 
        (1, 1, 2, '10A1', 10, '2025-2026', 'active'),
        (2, 1, 2, '11B2', 11, '2025-2026', 'active');");

    // 4. Seed Class Students
    $pdo->exec("INSERT INTO class_students (class_id, student_id) VALUES 
        (1, 4), (1, 5), (2, 6);");

    // 5. Seed Global Questions (Standard MC, True/False, Code Snippets)
    $q1Content = "<p>Trong ngôn ngữ lập trình Python, lệnh nào dùng để in dữ liệu ra màn hình?</p><pre><code>print(\"Hello World\")</code></pre>";
    $q2Content = "<p>Bộ nhớ RAM trong máy tính là loại bộ nhớ lưu trữ dữ liệu tạm thời và sẽ mất đi khi mất điện?</p>";
    $q3Content = "<p>Cho đoạn mã HTML sau: <code>&lt;a href=\"...\"&gt;</code>. Thẻ này dùng để tạo gì?</p>";
    $q4Content = "<p>Trong thuật toán tìm kiếm nhị phân (Binary Search), độ phức tạp thời gian trung bình là bao nhiêu?</p>";

    $stmt = $pdo->prepare("INSERT INTO questions (id, creator_id, subject, grade_level, content, option_a, option_b, option_c, option_d, correct_option, explanation) VALUES 
        (1, 2, 'Tin Học', 10, ?, 'Hàm echo()', 'Hàm print()', 'Hàm System.out.println()', 'Hàm console.log()', 'B', 'Hàm print() là cú pháp chuẩn của Python.'),
        (2, 2, 'Tin Học', 10, ?, 'Đúng', 'Sai', NULL, NULL, 'A', 'RAM là Volatile Memory, dữ liệu mất đi khi ngắt nguồn điện.'),
        (3, 2, 'Tin Học', 10, ?, 'Hình ảnh', 'Liên kết (Hyperlink)', 'Bảng dữ liệu', 'Đoạn văn bản', 'B', 'Thẻ a (anchor) định nghĩa liên kết trong HTML.'),
        (4, 2, 'Tin Học', 11, ?, 'O(1)', 'O(n)', 'O(log n)', 'O(n^2)', 'C', 'Tìm kiếm nhị phân có độ phức tạp thời gian O(log n).');");
    
    $stmt->execute([$q1Content, $q2Content, $q3Content, $q4Content]);

    // 6. Seed Cumulative Test
    $pdo->exec("INSERT INTO tests (id, class_id, teacher_id, title, description, status) VALUES 
        (1, 1, 2, 'Tích Luỹ Tin Học 10 - Học Kỳ 1', 'Bài kiểm tra tích luỹ cộng dồn cho Lớp 10A1', 'active');");

    // 7. Seed Question Pool
    $pdo->exec("INSERT INTO test_questions_pool (test_id, question_id, added_by_teacher_id) VALUES 
        (1, 1, 2), (1, 2, 2), (1, 3, 2);");

    echo "Database successfully seeded with demo data!\n";
} catch (PDOException $e) {
    echo "Seeding Error: " . $e->getMessage() . "\n";
}
