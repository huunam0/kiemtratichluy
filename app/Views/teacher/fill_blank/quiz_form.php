<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= $quiz ? 'Chỉnh sửa Đề kiểm tra Điền vào chỗ trống' : 'Tạo Đề kiểm tra Điền vào chỗ trống Mới' ?></h1>
            <p class="text-gray-500 text-sm mt-0.5">Chọn danh sách dạng bài tập và gán cho Lớp học.</p>
        </div>
        <a href="<?= base_url('teacher/fill-blank/quizzes') ?>" class="btn btn-outline-secondary rounded-xl">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <form method="POST" class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-gray-700">Tên bài kiểm tra <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control rounded-xl" value="<?= esc($quiz['title'] ?? old('title')) ?>" placeholder="VD: Kiểm tra Điền chỗ trống - Chương Xâu Ký Tự" required>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label fw-semibold text-gray-700">Gán cho Lớp học <span class="text-danger">*</span></label>
                    <select name="class_id" class="form-select rounded-xl" required>
                        <option value="">-- Chọn lớp --</option>
                        <?php foreach ($classes as $cls): ?>
                            <option value="<?= $cls['id'] ?>" <?= (isset($quiz['class_id']) && $quiz['class_id'] == $cls['id']) ? 'selected' : '' ?>>
                                <?= esc($cls['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label fw-semibold text-gray-700">Thời gian làm bài (Phút)</label>
                    <input type="number" name="time_limit" class="form-control rounded-xl" value="<?= esc($quiz['time_limit'] ?? 15) ?>" min="1" max="180">
                </div>
            </div>

            <!-- Allow Mock / Practice Option -->
            <div class="mt-4 p-4 bg-gray-50 border border-gray-100 rounded-xl space-y-2">
                <label class="form-label fw-semibold text-gray-800 mb-1">
                    <i class="fa-solid fa-gamepad text-teal-600 me-1"></i> Cho phép học sinh kiểm tra thử (Thi thử)?
                </label>
                <div class="flex items-center gap-6 mt-1">
                    <label class="inline-flex items-center cursor-pointer text-sm font-medium text-gray-700">
                        <input type="radio" name="allow_mock" value="1" <?= (int)old('allow_mock', $quiz['allow_mock'] ?? 1) === 1 ? 'checked' : '' ?> class="text-teal-600 focus:ring-teal-500 w-4 h-4">
                        <span class="ms-2 text-emerald-700 font-semibold"><i class="fa-solid fa-circle-check me-1"></i> Có (Cho phép thi thử)</span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer text-sm font-medium text-gray-700">
                        <input type="radio" name="allow_mock" value="0" <?= (int)old('allow_mock', $quiz['allow_mock'] ?? 1) === 0 ? 'checked' : '' ?> class="text-teal-600 focus:ring-teal-500 w-4 h-4">
                        <span class="ms-2 text-red-600 font-semibold"><i class="fa-solid fa-circle-xmark me-1"></i> Không (Khóa thi thử)</span>
                    </label>
                </div>
                <p class="text-xs text-gray-500 mb-0">Khi chọn "Không", học sinh sẽ không thể bấm "Làm Bài Thử" cho đề điền chỗ trống này.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-square-check me-2 text-indigo-600"></i>Chọn các Dạng bài tập đưa vào Đề</h3>
                <span class="text-sm text-gray-500">Mỗi dạng bài khi học sinh làm sẽ tự động bốc 1 phiên bản ngẫu nhiên.</span>
            </div>

            <?php
            $selectedIds = json_decode($quiz['selected_question_ids'] ?? '[]', true) ?: [];
            ?>

            <?php if (empty($questions)): ?>
                <p class="text-gray-400 text-center py-4">Chưa có câu hỏi nào trong ngân hàng. Hãy tạo câu hỏi trước!</p>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <?php foreach ($questions as $q): ?>
                        <label class="border border-gray-200 rounded-xl p-3 flex items-start space-x-3 cursor-pointer hover:bg-indigo-50/50 transition">
                            <input type="checkbox" name="selected_questions[]" value="<?= $q['id'] ?>" class="mt-1 form-check-input" <?= in_array($q['id'], $selectedIds) ? 'checked' : '' ?>>
                            <div class="flex-1">
                                <div class="font-bold text-gray-900"><?= esc($q['title']) ?></div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <span class="badge bg-gray-100 text-gray-700 me-1"><?= esc($q['subject_name'] ?? 'Tin học') ?> - <?= esc($q['topic_name'] ?? '') ?></span>
                                    <span class="badge bg-indigo-50 text-indigo-700"><?= $q['variant_count'] ?> phiên bản</span>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="pt-4">
                <button type="submit" class="btn bg-indigo-600 text-white hover:bg-indigo-700 font-bold rounded-xl px-5 py-2.5">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Lưu Đề Kiểm Tra
                </button>
            </div>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
