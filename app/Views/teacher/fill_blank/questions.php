<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Ngân hàng Câu hỏi Điền vào chỗ trống</h1>
            <p class="text-gray-500 text-sm mt-0.5">Quản lý các dạng bài trắc nghiệm điền từ/mã nguồn và các phiên bản biến thể ngẫu nhiên.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('teacher/topics') ?>" class="btn btn-outline-indigo rounded-xl font-semibold">
                <i class="fa-solid fa-tags me-1"></i> Quản lý Chủ đề
            </a>
            <a href="<?= base_url('teacher/fill-blank/quizzes') ?>" class="btn btn-outline-secondary rounded-xl font-semibold">
                <i class="fa-solid fa-list-check me-1"></i> Quản lý Đề kiểm tra
            </a>
            <a href="<?= base_url('teacher/fill-blank/questions/create') ?>" class="btn bg-indigo-600 text-white hover:bg-indigo-700 font-bold rounded-xl px-4 py-2">
                <i class="fa-solid fa-plus me-1"></i> Tạo Dạng bài mới
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-gray-50 text-gray-700 text-xs uppercase">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Tên dạng bài</th>
                        <th>Môn học</th>
                        <th>Khối</th>
                        <th>Số phiên bản (Variants)</th>
                        <th>Ngày tạo</th>
                        <th class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($questions)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-gray-400">Chưa có câu hỏi điền chỗ trống nào. Hãy tạo mới hoặc chạy seeder.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($questions as $q): ?>
                            <tr>
                                <td class="ps-4 font-mono text-sm text-gray-500">#<?= $q['id'] ?></td>
                                <td class="fw-bold text-gray-900"><?= esc($q['title']) ?></td>
                                <td>
                                    <span class="badge bg-indigo-50 text-indigo-700 border border-indigo-200"><?= esc($q['subject_name'] ?? 'Tin học') ?></span>
                                    <span class="badge bg-gray-100 text-gray-700 ms-1"><?= esc($q['topic_name'] ?? '') ?></span>
                                </td>
                                <td>Khối <?= esc($q['grade_level'] ?? 11) ?></td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                        <i class="fa-solid fa-clone me-1"></i> <?= $q['variant_count'] ?> phiên bản
                                    </span>
                                </td>
                                <td class="text-gray-500 text-sm"><?= esc($q['created_at'] ?? '') ?></td>
                                <td class="text-end pe-4">
                                    <a href="<?= base_url('teacher/fill-blank/questions/edit/' . $q['id']) ?>" class="btn btn-sm btn-outline-primary rounded-lg me-1">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Quản lý biến thể
                                    </a>
                                    <a href="<?= base_url('teacher/fill-blank/questions/delete/' . $q['id']) ?>" class="btn btn-sm btn-outline-danger rounded-lg" onclick="return confirm('Bạn có chắc chắn muốn xóa câu hỏi này?');">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
