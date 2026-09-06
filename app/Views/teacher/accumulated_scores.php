<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">

    <!-- Header Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="badge bg-indigo-100 text-indigo-800 font-bold">Lớp <?= esc($class['name'] ?? '') ?></span>
                <span class="badge bg-gray-100 text-gray-700">Khối <?= esc($class['grade_level'] ?? '') ?></span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-0">Bảng Điểm Tích Luỹ Cộng Dồn Học Sinh</h1>
            <p class="text-gray-500 text-sm mt-1 mb-0">
                Bài kiểm tra: <span class="font-semibold text-indigo-600"><?= esc($test['title']) ?></span>
            </p>
        </div>

        <a href="<?= base_url('teacher/tests') ?>" class="btn btn-outline-secondary btn-sm rounded-xl">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại Danh sách Bài Tích Luỹ
        </a>
    </div>

    <!-- Leaderboard Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                <i class="fa-solid fa-ranking-star text-amber-500"></i>
                Bảng Xếp Hạng Điểm Tích Luỹ Thang 10 (Sắp xếp từ Cao xuống Thấp)
            </h3>
            <span class="text-xs text-gray-500 font-medium">Tổng số: <?= count($scores_list) ?> học sinh</span>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                    <tr>
                        <th class="ps-6" style="width: 70px;">Hạng</th>
                        <th>Họ Và Tên Học Sinh</th>
                        <th>Tên Đăng Nhập</th>
                        <th class="text-center">Tổng Câu Đúng</th>
                        <th class="text-center">Tổng Câu Đã Làm</th>
                        <th class="text-center">ĐIỂM TÍCH LUỸ (HỆ 10)</th>
                        <th class="text-end pe-6">Chi Tiết Từng Lượt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php if (empty($scores_list)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-400">
                                Lớp học này chưa có học sinh nào đăng ký.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($scores_list as $index => $st): ?>
                            <?php 
                                $rank = $index + 1;
                                $rankBadge = "<span class='text-gray-400 font-bold'>#{$rank}</span>";
                                if ($rank === 1 && $st['has_score']) {
                                    $rankBadge = '<span class="w-8 h-8 rounded-full bg-yellow-400 text-yellow-950 font-black inline-flex items-center justify-center shadow-sm">1</span>';
                                } elseif ($rank === 2 && $st['has_score']) {
                                    $rankBadge = '<span class="w-8 h-8 rounded-full bg-slate-300 text-slate-900 font-black inline-flex items-center justify-center shadow-sm">2</span>';
                                } elseif ($rank === 3 && $st['has_score']) {
                                    $rankBadge = '<span class="w-8 h-8 rounded-full bg-amber-600 text-white font-black inline-flex items-center justify-center shadow-sm">3</span>';
                                }
                            ?>
                            <tr>
                                <td class="ps-6 font-bold text-center"><?= $rankBadge ?></td>
                                <td>
                                    <div class="font-bold text-gray-900 text-base"><?= esc($st['full_name']) ?></div>
                                    <div class="text-xs text-gray-500"><?= esc($st['email']) ?></div>
                                </td>
                                <td><code class="bg-gray-100 px-2 py-0.5 rounded text-gray-800 font-mono">@<?= esc($st['username']) ?></code></td>
                                <td class="text-center font-bold text-emerald-600"><?= $st['total_correct'] ?> câu</td>
                                <td class="text-center font-semibold text-gray-700"><?= $st['total_attempted'] ?> câu</td>
                                <td class="text-center">
                                    <?php if ($st['has_score'] && $st['total_attempted'] > 0): ?>
                                        <span class="text-xl font-black text-indigo-700 bg-indigo-50 px-3 py-1 rounded-xl border border-indigo-100">
                                            <?= number_format($st['accumulated_gpa'], 2) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs italic">Chưa tham gia lượt nào</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-6">
                                    <a href="<?= base_url("teacher/tests/student-breakdown/{$test['id']}/{$st['id']}") ?>" class="btn btn-outline-indigo btn-sm rounded-xl font-bold">
                                        <i class="fa-solid fa-list-ul me-1"></i> Xem Chi Tiết Lượt Thi
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
