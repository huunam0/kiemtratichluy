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
                        <th class="text-end pe-6">Hành Động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php if (empty($pool_questions)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-400">
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

    <!-- Available Questions from Global Bank -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                <i class="fa-solid fa-globe text-emerald-600"></i>
                Thêm câu hỏi từ Ngân hàng chung (Global Bank) vào Pool sau mỗi bài học
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                    <tr>
                        <th class="ps-6" style="width: 50px;">ID</th>
                        <th>Nội Dung Câu Hỏi</th>
                        <th>Môn / Khối</th>
                        <th>Tác Giả</th>
                        <th class="text-end pe-6">Trạng Thái Pool</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php foreach ($global_questions as $gq): ?>
                        <?php $inPool = in_array($gq['id'], $pool_ids); ?>
                        <tr class="<?= $inPool ? 'bg-gray-50/50' : '' ?>">
                            <td class="ps-6 font-mono text-gray-400">#<?= $gq['id'] ?></td>
                            <td>
                                <div class="font-medium text-gray-900 line-clamp-2 max-w-xl">
                                    <?= strip_tags($gq['content'], '<img><code><pre><b><i><strong>') ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-gray-100 text-gray-600"><?= esc($gq['subject_name'] ?? '') ?></span>
                                <span class="badge bg-indigo-50 text-indigo-700 ms-1"><?= esc($gq['topic_name'] ?? '') ?></span>
                                - K<?= esc($gq['grade_level'] ?? '') ?>
                            </td>
                            <td class="text-gray-600 text-xs"><?= esc($gq['creator_name'] ?? 'Giáo viên') ?></td>
                            <td class="text-end pe-6">
                                <?php if ($inPool): ?>
                                    <span class="badge bg-emerald-100 text-emerald-800 font-semibold px-3 py-1.5">
                                        <i class="fa-solid fa-check me-1"></i> Đã trong Pool
                                    </span>
                                <?php else: ?>
                                    <a href="<?= base_url("teacher/tests/pool/add/{$test['id']}/{$gq['id']}") ?>" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white btn-sm rounded-lg font-bold">
                                        <i class="fa-solid fa-plus me-1"></i> Thêm Vào Pool
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
