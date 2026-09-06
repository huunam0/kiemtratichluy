<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">

    <!-- Header Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="badge bg-indigo-100 text-indigo-800 font-bold">Lớp <?= esc($class['name'] ?? '') ?></span>
                <span class="badge bg-gray-100 text-gray-700">Học Sinh: <?= esc($student['full_name']) ?> (@<?= esc($student['username']) ?>)</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-0">Lịch Sử & Chi Tiết Điểm Tích Luỹ Từng Lượt Thi</h1>
            <p class="text-gray-500 text-sm mt-1 mb-0">
                Bài kiểm tra: <span class="font-semibold text-indigo-600"><?= esc($test['title']) ?></span>
            </p>
        </div>

        <a href="<?= base_url("teacher/tests/accumulated-scores/{$test['id']}") ?>" class="btn btn-outline-secondary btn-sm rounded-xl">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại Bảng Điểm Tích Luỹ
        </a>
    </div>

    <!-- Student Overall Accumulated Summary -->
    <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-purple-900 rounded-3xl p-6 text-white shadow-lg grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white/10 border border-white/10 text-center">
            <div class="text-xs text-indigo-200 uppercase font-semibold">Tổng Câu Trả Lời Đúng</div>
            <div class="text-3xl font-black text-emerald-400 mt-1"><?= $accumulated ? $accumulated['total_correct'] : 0 ?> câu</div>
        </div>
        <div class="p-4 rounded-2xl bg-white/10 border border-white/10 text-center">
            <div class="text-xs text-indigo-200 uppercase font-semibold">Tổng Câu Đã Làm (Bao gồm Phạt)</div>
            <div class="text-3xl font-black text-amber-300 mt-1"><?= $accumulated ? $accumulated['total_attempted'] : 0 ?> câu</div>
        </div>
        <div class="p-4 rounded-2xl bg-white/10 border border-white/10 text-center">
            <div class="text-xs text-indigo-200 uppercase font-semibold">ĐIỂM TÍCH LUỸ (HỆ 10)</div>
            <div class="text-4xl font-black text-yellow-300 mt-1">
                <?= $accumulated ? number_format($accumulated['accumulated_gpa'], 2) : '0.00' ?>
            </div>
        </div>
    </div>

    <!-- Detailed History Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                <i class="fa-solid fa-list-check text-indigo-600"></i>
                Nhật Ký Từng Lượt Thi Thực Tế & Đánh Vắng
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                    <tr>
                        <th class="ps-6">Thời Gian</th>
                        <th>Tên Lượt Thi</th>
                        <th>Trạng Thái</th>
                        <th class="text-center">Kết Quả Lượt Thi</th>
                        <th class="text-center">Cộng Phạt Tích Luỹ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php if (empty($history)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-400">
                                Học sinh này chưa tham gia lượt thi nào thuộc Bài tích luỹ này.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($history as $h): ?>
                            <tr>
                                <td class="ps-6 text-gray-500 text-xs font-mono">
                                    <?= date('H:i d/m/Y', strtotime($h['joined_at'] ?? $h['session_date'])) ?>
                                </td>
                                <td>
                                    <div class="font-bold text-gray-900"><?= esc($h['session_title']) ?></div>
                                    <code class="bg-gray-100 px-2 py-0.5 rounded text-indigo-600 font-bold text-xs"><?= esc($h['session_code']) ?></code>
                                </td>
                                <td>
                                    <?php if ($h['approval_status'] === 'absent'): ?>
                                        <span class="badge bg-red-600 text-white font-bold px-3 py-1 text-xs">
                                            <i class="fa-solid fa-user-xmark me-1"></i> ĐÁNH VẮNG
                                        </span>
                                    <?php elseif ($h['test_status'] === 'submitted'): ?>
                                        <span class="badge bg-emerald-100 text-emerald-800 font-bold px-3 py-1 text-xs">
                                            <i class="fa-solid fa-check me-1"></i> Nộp bài
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-amber-100 text-amber-800 font-semibold px-3 py-1 text-xs">
                                            Chưa nộp bài
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center font-bold">
                                    <?php if ($h['approval_status'] === 'absent'): ?>
                                        <span class="text-red-600 text-xs">0 câu đúng (Vắng)</span>
                                    <?php elseif ($h['score_total'] > 0 && $h['test_status'] === 'submitted'): ?>
                                        <span class="text-emerald-700 font-bold"><?= $h['score_correct'] ?> / <?= $h['score_total'] ?> câu đúng</span>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center font-bold">
                                    <?php if ($h['approval_status'] === 'absent'): ?>
                                        <span class="text-red-600 bg-red-50 px-2.5 py-1 rounded-lg border border-red-100 text-xs">
                                            +0 Đúng / +3 Tổng
                                        </span>
                                    <?php elseif ($h['score_total'] > 0): ?>
                                        <span class="text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-100 text-xs">
                                            +<?= $h['score_correct'] ?> Đúng / +<?= $h['score_total'] ?> Tổng
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs">-</span>
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
