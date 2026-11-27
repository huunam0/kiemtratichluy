<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">

    <!-- Header Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="badge bg-indigo-100 text-indigo-800 font-bold">Lớp <?= esc($class['name'] ?? '') ?></span>
                <span class="badge bg-gray-100 text-gray-700">Khối <?= esc($class['grade_level'] ?? '') ?></span>
                <span class="badge bg-amber-100 text-amber-800 font-semibold">
                    <i class="fa-solid fa-circle-check me-1"></i>Số câu tối thiểu: <?= (int)($test['min_questions'] ?? 20) ?> câu
                </span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-0">Bảng Điểm Tích Luỹ Cộng Dồn Học Sinh</h1>
            <p class="text-gray-500 text-sm mt-1 mb-0">
                Bài kiểm tra: <span class="font-semibold text-indigo-600"><?= esc($test['title']) ?></span>
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- Export Excel Button -->
            <a href="<?= base_url("teacher/tests/export-excel/{$test['id']}") ?>" class="btn btn-success btn-sm rounded-xl font-bold shadow-sm">
                <i class="fa-solid fa-file-excel me-1"></i> Xuất Excel
            </a>

            <!-- Question Pool Button -->
            <a href="<?= base_url("teacher/tests/pool/{$test['id']}") ?>" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white btn-sm rounded-xl font-bold shadow-sm">
                <i class="fa-solid fa-layer-group me-1"></i> Kho Câu Hỏi
            </a>

            <!-- Create Session Button -->
            <button type="button" class="btn btn-warning btn-sm rounded-xl font-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#createSessionModal">
                <i class="fa-solid fa-play-circle me-1"></i> Tạo Lượt Thi
            </button>

            <a href="<?= base_url('teacher/tests') ?>" class="btn btn-outline-secondary btn-sm rounded-xl">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại Danh sách Bài Tích Luỹ
            </a>
        </div>
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
                        <th class="text-center">Số Lượt Thi</th>
                        <th class="text-center">Tổng Câu Đúng</th>
                        <th class="text-center">Tổng Câu Đã Làm</th>
                        <th class="text-center">ĐIỂM TÍCH LUỸ (HỆ 10)</th>
                        <th class="text-end pe-6">Chi Tiết Từng Lượt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php if (empty($scores_list)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-8 text-gray-400">
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
                                $minQ = $min_questions ?? 20;
                                $lacking = $minQ - $st['total_attempted'];
                            ?>
                            <tr>
                                <td class="ps-6 font-bold text-center"><?= $rankBadge ?></td>
                                <td>
                                    <div class="font-bold text-gray-900 text-base"><?= esc($st['full_name']) ?></div>
                                    <div class="text-xs text-gray-500"><?= esc($st['email']) ?></div>
                                </td>
                                <td><code class="bg-gray-100 px-2 py-0.5 rounded text-gray-800 font-mono">@<?= esc($st['username']) ?></code></td>
                                <td class="text-center font-semibold text-gray-700"><?= $st['real_count'] ?? 0 ?> lượt</td>
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


<!-- ============================================================ -->
<!-- CREATE SESSION MODAL -->
<!-- ============================================================ -->
<div class="modal fade" id="createSessionModal" tabindex="-1" aria-labelledby="createSessionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3xl border-0 shadow-xl overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header bg-gradient-to-r from-amber-500 to-orange-500 text-white border-0 px-6 py-4">
                <h5 class="modal-title font-bold text-lg flex items-center gap-2" id="createSessionModalLabel">
                    <i class="fa-solid fa-play-circle text-white text-xl"></i>
                    Tạo Lượt Thi Mới
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body px-6 py-5">
                <p class="text-gray-600 text-sm mb-4">
                    <i class="fa-solid fa-circle-info text-amber-500 me-1"></i>
                    Tạo lượt thi trực tiếp cho bài kiểm tra <strong><?= esc($test['title']) ?></strong> (Lớp <?= esc($class['name'] ?? '') ?>).
                </p>

                <form action="<?= base_url('teacher/sessions') ?>" method="POST" class="space-y-4">
                    <?= csrf_field() ?>
                    <input type="hidden" name="test_id" value="<?= $test['id'] ?>">

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Tên Lượt Thi <span class="text-red-500">*</span></label>
                        <input type="text" name="session_title" required
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                               placeholder="VD: Lượt 1 - Kiểm tra bài 5 (15 phút)">
                    </div>

                    <div class="p-3 bg-amber-50 border border-amber-100 rounded-xl text-xs text-amber-900">
                        <i class="fa-solid fa-circle-info me-1 text-amber-600"></i> Học sinh sẽ tự lựa chọn số câu làm (từ 3 đến 6 câu) khi bắt đầu lượt thi của mình.
                    </div>

                    <button type="submit" class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl shadow-md transition">
                        Khởi Tạo Lượt Thi <i class="fa-solid fa-arrow-right ms-1"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
