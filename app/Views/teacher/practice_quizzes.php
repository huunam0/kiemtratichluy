<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Quản Lý Đề Trắc Nghiệm Luyện Tập (Markdown)</h1>
            <p class="text-gray-500 text-sm">Tạo bài luyện tập gán cho từng lớp, chỉ học sinh của lớp đó mới thấy và làm được bài.</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= base_url('cauhoi.html') ?>" target="_blank" class="btn btn-outline-indigo border-indigo-500 text-indigo-600 hover:bg-indigo-50 font-semibold rounded-xl px-4 py-2">
                <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Mở Tool Soạn Câu Hỏi
            </a>
            <a href="<?= base_url('teacher/practice-quizzes/create') ?>" class="btn bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl px-4 py-2">
                <i class="fa-solid fa-plus me-1"></i> Tạo Đề Mới
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table table-hover align-middle mb-0 text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                    <tr>
                        <th class="ps-6">Tiêu Đề Bài Luyện Tập</th>
                        <th>Môn Học / Khối</th>
                        <th>Lớp Học</th>
                        <th>Thi Thử</th>
                        <th>Đường Dẫn Chia Sẻ (Link)</th>
                        <th>Ngày Tạo</th>
                        <th class="text-end pe-6">Hành Động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($quizzes)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-400">
                                Chưa có đề trắc nghiệm luyện tập nào. Bấm <strong>Tạo Đề Mới</strong> để tạo ngay!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($quizzes as $q): ?>
                            <?php $shareUrl = base_url("practice/{$q['slug']}"); ?>
                            <tr>
                                <td class="ps-6 font-bold text-gray-900">
                                    <?= esc($q['title']) ?>
                                </td>
                                <td>
                                    <span class="badge bg-indigo-100 text-indigo-800 font-semibold">
                                        <?= esc($q['subject']) ?> - Khối <?= esc($q['grade_level']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($q['class_name'])): ?>
                                        <span class="badge bg-emerald-100 text-emerald-800 font-semibold">
                                            <i class="fa-solid fa-users me-1"></i><?= esc($q['class_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-gray-100 text-gray-500 text-xs">Chưa gán lớp</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ((int)($q['allow_mock'] ?? 1) === 1): ?>
                                        <span class="badge bg-emerald-100 text-emerald-800 font-semibold px-2.5 py-1">
                                            <i class="fa-solid fa-check me-1"></i> Cho phép
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-red-100 text-red-700 font-semibold px-2.5 py-1">
                                            <i class="fa-solid fa-lock me-1"></i> Đã khóa
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm max-w-xs">
                                        <input type="text" readonly class="form-control font-mono text-xs bg-gray-50" value="<?= $shareUrl ?>">
                                        <button class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText('<?= $shareUrl ?>'); alert('Đã chép link!');" title="Sao chép link">
                                            <i class="fa-solid fa-copy"></i>
                                        </button>
                                        <a href="<?= $shareUrl ?>" target="_blank" class="btn btn-outline-indigo" title="Mở làm thử">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>
                                    </div>
                                </td>
                                <td class="text-gray-500 text-xs"><?= date('H:i d/m/Y', strtotime($q['created_at'])) ?></td>
                                <td class="text-end pe-6 space-x-2">
                                    <a href="<?= base_url("teacher/practice-quizzes/results/{$q['id']}") ?>" class="btn btn-outline-info btn-sm rounded-lg font-semibold">
                                        <i class="fa-solid fa-chart-line me-1"></i> Xem Kết Quả
                                    </a>
                                    <a href="<?= base_url("teacher/practice-quizzes/edit/{$q['id']}") ?>" class="btn btn-outline-secondary btn-sm rounded-lg">
                                        <i class="fa-solid fa-pen-to-square"></i> Sửa
                                    </a>
                                    <a href="<?= base_url("teacher/practice-quizzes/delete/{$q['id']}") ?>" class="btn btn-outline-danger btn-sm rounded-lg" onclick="return confirm('Bạn có chắc muốn xoá đề luyện tập này?')">
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

