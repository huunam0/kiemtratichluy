<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <!-- SESSION HEADER -->
    <div class="bg-gradient-to-r from-indigo-800 to-purple-800 rounded-2xl p-6 text-white shadow-lg flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="text-xs uppercase tracking-wider font-semibold text-indigo-200 mb-1">PHÒNG ĐIỀU KHIỂN PHIÊN THI THẬT (ĐIỀN VÀO CHỖ TRỐNG)</div>
            <h1 class="text-2xl font-extrabold"><?= esc($quiz['title']) ?></h1>
            <p class="text-indigo-100 text-sm mt-1 mb-0">
                <i class="fa-solid fa-key me-1"></i> Mã phiên thi: <span class="font-mono bg-white/20 px-2 py-0.5 rounded font-bold"><?= esc($session['session_code']) ?></span>
                <span class="mx-2">•</span>
                <i class="fa-solid fa-clock me-1"></i> Thời gian: <?= $quiz['time_limit'] ?> phút
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('teacher/fill-blank/quizzes') ?>" class="btn btn-light rounded-xl font-bold">
                <i class="fa-solid fa-arrow-left me-1"></i> Trở về Quản lý Đề
            </a>
            <button onclick="location.reload();" class="btn btn-outline-light rounded-xl font-semibold">
                <i class="fa-solid fa-rotate me-1"></i> Tải lại danh sách
            </button>
        </div>
    </div>

    <!-- PARTICIPANTS LIST -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-user-check me-2 text-indigo-600"></i>Danh sách Học sinh Đăng ký & Phê duyệt</h3>
            <span class="badge bg-indigo-100 text-indigo-800 px-3 py-1.5 rounded-full text-sm font-semibold">Tổng số: <?= count($participants) ?> học sinh</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-gray-50 text-gray-700 text-xs uppercase">
                    <tr>
                        <th class="ps-4">Họ và tên Học sinh</th>
                        <th>Tên tài khoản</th>
                        <th>Thời gian tham gia</th>
                        <th>Trạng thái Phê duyệt</th>
                        <th>Trạng thái Làm bài</th>
                        <th>Điểm số</th>
                        <th class="text-end pe-4">Phê duyệt / Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($participants)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-gray-400">
                                <i class="fa-solid fa-spinner fa-spin text-2xl text-indigo-400 mb-2 d-block"></i>
                                Đang chờ học sinh đăng ký vào phòng thi... (Mã phiên: <strong><?= esc($session['session_code']) ?></strong>)
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($participants as $p): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-gray-900"><?= esc($p['student_name']) ?></td>
                                <td class="font-mono text-sm text-gray-500"><?= esc($p['username']) ?></td>
                                <td class="text-gray-500 text-sm"><?= esc($p['joined_at'] ?? '') ?></td>
                                <td>
                                    <?php if ($p['approval_status'] === 'approved'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1"><i class="fa-solid fa-check me-1"></i> Đã duyệt</span>
                                    <?php elseif ($p['approval_status'] === 'rejected'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1"><i class="fa-solid fa-xmark me-1"></i> Từ chối</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1"><i class="fa-solid fa-clock me-1"></i> Đang chờ duyệt</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($p['test_status'] === 'submitted'): ?>
                                        <span class="badge bg-indigo-100 text-indigo-800">Đã nộp bài</span>
                                    <?php elseif ($p['test_status'] === 'in_test'): ?>
                                        <span class="badge bg-info-subtle text-info">Đang làm bài</span>
                                    <?php else: ?>
                                        <span class="badge bg-gray-100 text-gray-600">Phòng chờ</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold font-mono">
                                    <?php if ($p['test_status'] === 'submitted'): ?>
                                        <span class="text-emerald-600 text-base"><?= $p['score_base10'] ?> / 10</span>
                                        <span class="text-xs text-gray-400 block font-normal">(Đúng <?= $p['score_correct'] ?>/<?= $p['score_total'] ?> ô)</span>
                                    <?php else: ?>
                                        <span class="text-gray-300">--</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4 space-x-1">
                                    <?php if ($p['approval_status'] !== 'approved'): ?>
                                        <form method="POST" action="<?= base_url('teacher/fill-blank/sessions/approve/' . $p['id']) ?>" style="display:inline;">
                                            <button type="submit" class="btn btn-sm bg-emerald-600 text-white hover:bg-emerald-700 font-bold rounded-lg px-3">
                                                <i class="fa-solid fa-check me-1"></i> Duyệt
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($p['approval_status'] !== 'rejected'): ?>
                                        <form method="POST" action="<?= base_url('teacher/fill-blank/sessions/reject/' . $p['id']) ?>" style="display:inline;">
                                            <button type="submit" class="btn btn-sm btn-outline-danger font-semibold rounded-lg px-3">
                                                <i class="fa-solid fa-xmark me-1"></i> Từ chối
                                            </button>
                                        </form>
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

<script>
// Auto reload every 5 seconds to update waiting room participants
setInterval(function() {
    location.reload();
}, 5000);
</script>
<?= $this->endSection() ?>
