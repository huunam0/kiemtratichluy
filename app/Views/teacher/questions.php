<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Ngân hàng Câu hỏi Toàn hệ thống (Global Question Bank)</h1>
            <p class="text-gray-500 text-sm">PUBLIC toàn hệ thống — Tất cả giáo viên có thể xem & dùng chung, chỉ người tạo mới được sửa/xoá.</p>
        </div>
        <a href="<?= base_url('teacher/questions/create') ?>" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl px-4 py-2">
            <i class="fa-solid fa-plus me-1"></i> Soạn Câu Hỏi Mới
        </a>
    </div>

    <!-- Questions Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                    <tr>
                        <th class="ps-6" style="width: 50px;">ID</th>
                        <th>Nội Dung Câu Hỏi</th>
                        <th>Số Đáp Án</th>
                        <th>Đáp Án Đúng Gốc</th>
                        <th>Tác Giả</th>
                        <th class="text-end pe-6">Hành Động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php if (empty($questions)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-400">
                                Chưa có câu hỏi nào trong Ngân hàng chung. Hãy tạo câu hỏi đầu tiên!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($questions as $q): ?>
                            <?php 
                                $optCount = 2;
                                if (!empty($q['option_c'])) $optCount++;
                                if (!empty($q['option_d'])) $optCount++;
                                $isCreator = ((int)$q['creator_id'] === (int)$teacher_id);
                            ?>
                            <tr>
                                <td class="ps-6 font-mono text-gray-400">#<?= $q['id'] ?></td>
                                <td>
                                    <div class="font-medium text-gray-900 line-clamp-2 max-w-xl">
                                        <?= strip_tags($q['content'], '<img><code><pre><b><i><strong>') ?>
                                    </div>
                                    <span class="badge bg-gray-100 text-gray-600 text-xs font-normal mt-1"><?= esc($q['subject']) ?> - Khối <?= esc($q['grade_level']) ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= $optCount === 2 ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800' ?> font-semibold">
                                        <?= $optCount === 2 ? '2 Đáp án (Đúng/Sai)' : "{$optCount} Đáp án" ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-emerald-600 text-white font-bold text-sm px-2.5 py-1">
                                        <?= esc($q['correct_option']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-gray-700 <?= $isCreator ? 'font-bold text-indigo-600' : '' ?>">
                                        <?= esc($q['creator_name'] ?? 'Giáo viên') ?>
                                        <?= $isCreator ? ' (Tôi)' : '' ?>
                                    </span>
                                </td>
                                <td class="text-end pe-6 space-x-2">
                                    <?php if ($isCreator): ?>
                                        <a href="<?= base_url("teacher/questions/edit/{$q['id']}") ?>" class="btn btn-outline-indigo btn-sm rounded-lg">
                                            <i class="fa-solid fa-pen-to-square"></i> Sửa
                                        </a>
                                        <a href="<?= base_url("teacher/questions/delete/{$q['id']}") ?>" class="btn btn-outline-danger btn-sm rounded-lg" onclick="return confirm('Bạn có chắc muốn xoá câu hỏi này?')">
                                            <i class="fa-solid fa-trash"></i> Xoá
                                        </a>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400 italic">Chỉ xem (Chỉ creator được sửa)</span>
                                    <?php endif; ?>
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
