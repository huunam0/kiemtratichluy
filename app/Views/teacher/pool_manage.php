<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Quản Lý Kho Câu Hỏi Tích Luỹ (Pool)</h1>
            <p class="text-gray-500 text-sm">
                Bài kiểm tra: <span class="font-bold text-indigo-600"><?= esc($test['title']) ?></span> | 
                Hiện có: <span class="badge bg-amber-600 text-white font-bold me-1"><?= count($pool_questions) ?> câu</span>
            </p>
        </div>
        <a href="<?= base_url('teacher/tests') ?>" class="btn btn-outline-secondary btn-sm rounded-lg">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại Bài kiểm tra
        </a>
    </div>

    <!-- Active Pool Questions -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-indigo-600"></i>
                Các câu hỏi ĐÃ CÓ trong Kho bài kiểm tra này (<?= count($pool_questions) ?>)
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                    <tr>
                        <th class="ps-6" style="width: 50px;">ID</th>
                        <th>Nội Dung Câu Hỏi</th>
                        <th>Số Đáp Án</th>
                        <th>Đáp Án Đúng Gốc</th>
                        <th>Ngày Thêm</th>
                        <th class="text-end pe-6">Hành Động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php if (empty($pool_questions)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-400">
                                Kho câu hỏi của bài kiểm tra này đang trống. Hãy chọn thêm câu hỏi từ Ngân hàng chung bên dưới!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pool_questions as $q): ?>
                            <?php 
                                $optCount = 2;
                                if (!empty($q['option_c'])) $optCount++;
                                if (!empty($q['option_d'])) $optCount++;
                            ?>
                            <tr>
                                <td class="ps-6 font-mono text-gray-400">#<?= $q['id'] ?></td>
                                <td>
                                    <div class="font-medium text-gray-900 line-clamp-2 max-w-xl">
                                        <?= strip_tags($q['content'], '<img><code><pre><b><i><strong>') ?>
                                    </div>
                                </td>
                                <td><span class="badge bg-gray-100 text-gray-800 font-medium"><?= $optCount ?> đáp án</span></td>
                                <td><span class="badge bg-emerald-600 text-white font-bold"><?= esc($q['correct_option']) ?></span></td>
                                <td class="text-gray-500 text-xs">
                                    <?= !empty($q['added_at']) ? date('d/m/Y H:i', strtotime($q['added_at'])) : '-' ?>
                                </td>
                                <td class="text-end pe-6">
                                    <a href="<?= base_url("teacher/tests/pool/remove/{$test['id']}/{$q['id']}") ?>" class="btn btn-outline-danger btn-sm rounded-lg">
                                        <i class="fa-solid fa-minus me-1"></i> Gỡ khỏi Pool
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Thêm câu hỏi mới -->
    <div class="mt-6 flex justify-center">
        <a href="<?= base_url("teacher/tests/pool/select-questions/{$test['id']}") ?>" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-bold shadow-sm flex items-center gap-2">
            <i class="fa-solid fa-plus-circle text-lg"></i> Tìm và thêm câu hỏi vào Pool
        </a>
    </div>
</div>
<?= $this->endSection() ?>
