<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $isEdit = isset($quiz); ?>
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= $isEdit ? 'Chỉnh Sửa Đề Trắc Nghiệm Luyện Tập' : 'Tạo Đề Trắc Nghiệm Luyện Tập (Markdown)' ?></h1>
            <p class="text-gray-500 text-sm">Soạn đề bằng cú pháp mã nguồn Markdown (tương thích với bộ chuyển đổi detracnghiem)</p>
        </div>
        <a href="<?= base_url('teacher/practice-quizzes') ?>" class="btn btn-outline-secondary rounded-xl px-4 py-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <form method="POST" action="<?= $isEdit ? base_url("teacher/practice-quizzes/edit/{$quiz['id']}") : base_url('teacher/practice-quizzes/create') ?>" class="space-y-6">
        <?= csrf_field() ?>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
            <!-- Title -->
            <div>
                <label class="form-label font-semibold text-gray-700">Tiêu đề bài trắc nghiệm <span class="text-danger">*</span></label>
                <input type="text" name="title" required value="<?= esc(old('title', $quiz['title'] ?? '')) ?>" class="form-control rounded-xl py-2.5" placeholder="Ví dụ: Luyện tập Trắc nghiệm Tin học 12 Giữa kì 1">
            </div>

            <!-- Subject & Grade -->
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label font-semibold text-gray-700">Môn học</label>
                    <select name="subject" class="form-select rounded-xl">
                        <?php
                        $subjects = ['Toán', 'Ngữ Văn', 'Vật Lý', 'Hóa Học', 'Sinh Học', 'Lịch Sử', 'Địa Lý', 'GDCD', 'Tiếng Anh', 'Tin Học', 'Công Nghệ', 'Chung'];
                        $curSubject = old('subject', $quiz['subject'] ?? 'Tin Học');
                        foreach ($subjects as $s): ?>
                            <option value="<?= esc($s) ?>" <?= $curSubject === $s ? 'selected' : '' ?>><?= esc($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label font-semibold text-gray-700">Khối lớp</label>
                    <select name="grade_level" class="form-select rounded-xl">
                        <?php
                        $curGrade = (int)old('grade_level', $quiz['grade_level'] ?? 10);
                        for ($g = 1; $g <= 12; $g++): ?>
                            <option value="<?= $g ?>" <?= $curGrade === $g ? 'selected' : '' ?>>Khối <?= $g ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <!-- Editor Header / Tool Button -->
            <div class="d-flex justify-content-between align-items-center pt-2">
                <label class="form-label font-semibold text-gray-700 mb-0">Nội dung Markdown bài trắc nghiệm <span class="text-danger">*</span></label>
                <a href="<?= base_url('cauhoi.html') ?>" target="_blank" class="btn btn-sm btn-outline-indigo border-indigo-400 text-indigo-600 rounded-lg">
                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Mở Tiện Ích cauhoi.html (Sinh Mã Câu Hỏi)
                </a>
            </div>

            <!-- Markdown Textarea -->
            <div>
                <textarea name="content_markdown" rows="16" required class="form-control font-mono text-sm rounded-xl p-3" style="line-height: 1.6;" placeholder="Dán nội dung cấu trúc Markdown vào đây..."><?= esc(old('content_markdown', $quiz['content_markdown'] ?? '')) ?></textarea>
            </div>

            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 text-xs text-indigo-900 space-y-1">
                <p class="font-bold mb-1"><i class="fa-solid fa-lightbulb me-1 text-amber-500"></i> Hướng dẫn cấu trúc Markdown cho 3 loại câu hỏi:</p>
                <p><strong>1. Trắc nghiệm 1 lựa chọn:</strong> <code>«chon1 Đáp án 1¸Đáp án 2¸Đáp án 3¦2¦10¦0¦0»</code> (Số 2 là vị trí đáp án đúng)</p>
                <p><strong>2. Trắc nghiệm nhiều lựa chọn (Đúng/Sai):</strong> <code>«chon1 Đúng¸Sai¦1¦00¦0¦0»</code></p>
                <p><strong>3. Trả lời ngắn / Điền vào chỗ trống:</strong> <code>«trong 13¦00¦0¦0»</code> (Số 13 là đáp án đúng)</p>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl px-5 py-2.5 shadow-md">
                    <i class="fa-solid fa-save me-1"></i> <?= $isEdit ? 'Cập Nhật Đề Trắc Nghiệm' : 'Lưu & Phát Hành Đề Luyện Tập' ?>
                </button>
            </div>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
