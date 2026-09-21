<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Chỉnh Sửa Bài Kiểm Tra Tích Luỹ</h1>
            <p class="text-gray-500 text-sm">Cập nhật thông tin bài kiểm tra và tùy chọn cho phép học sinh kiểm tra thử</p>
        </div>
        <a href="<?= base_url('teacher/tests') ?>" class="btn btn-outline-secondary rounded-xl px-4 py-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form action="<?= base_url("teacher/tests/edit/{$test['id']}") ?>" method="POST" class="space-y-5">
            <?= csrf_field() ?>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Gán Cho Lớp Học <span class="text-red-500">*</span></label>
                <select name="class_id" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 bg-white">
                    <option value="">-- Chọn Lớp Học --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= (int)$test['class_id'] === (int)$c['id'] ? 'selected' : '' ?>>
                            <?= esc($c['name']) ?> (Khối <?= esc($c['grade_level']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Tên Bài Kiểm Tra Tích Luỹ <span class="text-red-500">*</span></label>
                <input type="text" name="title" required value="<?= esc(old('title', $test['title'])) ?>" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Mô tả bài kiểm tra</label>
                <textarea name="description" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500"><?= esc(old('description', $test['description'] ?? '')) ?></textarea>
            </div>

            <div class="p-4 bg-gray-50 border border-gray-100 rounded-xl space-y-2">
                <label class="block text-sm font-semibold text-gray-800">
                    <i class="fa-solid fa-gamepad text-indigo-600 me-1"></i> Cho phép học sinh kiểm tra thử (Thi thử)?
                </label>
                <div class="flex items-center gap-6 mt-2">
                    <label class="inline-flex items-center cursor-pointer text-sm font-medium text-gray-700">
                        <input type="radio" name="allow_mock" value="1" <?= (int)($test['allow_mock'] ?? 1) === 1 ? 'checked' : '' ?> class="text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <span class="ms-2 text-emerald-700 font-semibold"><i class="fa-solid fa-circle-check me-1"></i> Có (Cho phép thi thử)</span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer text-sm font-medium text-gray-700">
                        <input type="radio" name="allow_mock" value="0" <?= (int)($test['allow_mock'] ?? 1) === 0 ? 'checked' : '' ?> class="text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <span class="ms-2 text-red-600 font-semibold"><i class="fa-solid fa-circle-xmark me-1"></i> Không (Khóa thi thử)</span>
                    </label>
                </div>
                <p class="text-xs text-gray-500 mb-0">
                    Khi chọn "Không", học sinh trong lớp sẽ không thấy nút thi thử và không thể vào làm bài kiểm tra thử từ kho câu hỏi này.
                </p>
            </div>

            <div class="pt-2 flex gap-3">
                <button type="submit" class="btn bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl px-5 py-2.5 shadow-md">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Lưu Thay Đổi
                </button>
                <a href="<?= base_url('teacher/tests') ?>" class="btn btn-outline-secondary rounded-xl px-4 py-2.5">
                    Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
