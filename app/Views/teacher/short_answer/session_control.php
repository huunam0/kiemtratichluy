<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <div class="d-flex items-center gap-2">
                <h1 class="text-2xl font-bold text-gray-900"><?= esc($quiz['title']) ?></h1>
                <?php if ((int)$quiz['is_open'] === 1): ?>
                    <span class="badge bg-success text-white px-3 py-1.5 rounded-pill text-xs font-bold animate-pulse">
                        <i class="fa-solid fa-door-open me-1"></i> ĐANG MỞ
                    </span>
                <?php else: ?>
                    <span class="badge bg-secondary text-white px-3 py-1.5 rounded-pill text-xs font-bold">
                        <i class="fa-solid fa-lock me-1"></i> ĐANG ĐÓNG
                    </span>
                <?php endif; ?>
            </div>
            <p class="text-gray-500 text-sm mt-0.5">
                Lớp: <strong class="text-gray-700"><?= esc($class['name'] ?? 'Chưa gán') ?></strong> |
                Thời gian: <strong class="text-gray-700"><?= (int)$quiz['time_limit'] ?> phút</strong> |
                Tổng điểm đề: <strong class="text-gray-700"><?= number_format($totalPoints, 2) ?>đ</strong> |
                Hệ số phạt nộp chậm: <strong class="text-amber-700"><?= (float)$quiz['penalty_coefficient'] > 0 ? (float)$quiz['penalty_coefficient'] . ' đ/bạn nộp sớm hơn' : 'Không phạt' ?></strong>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('teacher/short-answer/quizzes/edit/' . $quiz['id']) ?>" class="btn btn-outline-secondary rounded-xl font-semibold">
                <i class="fa-solid fa-gear me-1"></i> Cấu hình đề
            </a>
            <a href="<?= base_url('teacher/short-answer/quizzes') ?>" class="btn btn-light rounded-xl font-semibold">
                <i class="fa-solid fa-arrow-left me-1"></i> Danh sách đề
            </a>
        </div>
    </div>

    <!-- ACTION CARDS: OPEN / CLOSE BUTTONS -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="d-flex flex-wrap items-center justify-between gap-4">
            <div>
                <?php if ((int)$quiz['is_open'] === 1): ?>
                    <h3 class="text-lg font-bold text-emerald-800 mb-1">
                        <i class="fa-solid fa-circle-check text-success me-2"></i>Bài kiểm tra đang mở cho học sinh làm bài
                    </h3>
                    <p class="text-sm text-gray-600 mb-0">Học sinh thuộc lớp có thể vào làm bài ngay trên giao diện của mình. Khi hết giờ hoặc cả lớp đã nộp xong, hãy bấm nút Đóng bên cạnh để chốt bài và tự động tính điểm phạt nộp chậm.</p>
                <?php else: ?>
                    <h3 class="text-lg font-bold text-gray-800 mb-1">
                        <i class="fa-solid fa-lock text-gray-500 me-2"></i>Bài kiểm tra hiện đang đóng
                    </h3>
                    <p class="text-sm text-gray-500 mb-0">Học sinh không thể vào làm bài lúc này. Bấm nút Mở để bắt đầu một lượt kiểm tra mới cho cả lớp.</p>
                <?php endif; ?>
            </div>

            <div class="d-flex items-center gap-2">
                <?php if ((int)$quiz['is_open'] === 1): ?>
                    <form method="POST" action="<?= base_url('teacher/short-answer/quizzes/close/' . $quiz['id']) ?>" onsubmit="return confirm('Bạn có chắc chắn muốn ĐÓNG bài kiểm tra? Hệ thống sẽ tự động tính điểm phạt nộp chậm cho tất cả học sinh!');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger btn-lg rounded-xl font-bold px-4 shadow-sm">
                            <i class="fa-solid fa-lock me-1"></i> Đóng bài kiểm tra & Tính điểm
                        </button>
                    </form>
                <?php else: ?>
                    <form method="POST" action="<?= base_url('teacher/short-answer/quizzes/open/' . $quiz['id']) ?>" onsubmit="return confirm('Bạn có chắc chắn muốn MỞ bài kiểm tra cho học sinh vào làm?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-success btn-lg rounded-xl font-bold px-4 shadow-sm">
                            <i class="fa-solid fa-door-open me-1"></i> Mở bài kiểm tra ngay
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SESSIONS HISTORY & RECALCULATE -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
        <div class="d-flex flex-wrap items-center justify-between gap-3 pb-3 border-bottom">
            <div class="d-flex items-center gap-3">
                <label class="fw-bold text-gray-700 text-sm mb-0">Lượt thi:</label>
                <form method="GET" class="d-flex items-center gap-2 mb-0">
                    <select name="session_id" class="form-select form-select-sm rounded-xl font-semibold text-gray-800" onchange="this.form.submit()">
                        <?php if (empty($sessions)): ?>
                            <option value="">Chưa có lượt thi nào</option>
                        <?php else: ?>
                            <?php foreach ($sessions as $idx => $sess): ?>
                                <option value="<?= $sess['id'] ?>" <?= ($currentSession && $currentSession['id'] == $sess['id']) ? 'selected' : '' ?>>
                                    Lượt #<?= $sess['id'] ?> (<?= esc($sess['opened_at'] ?? $sess['created_at']) ?>) - [<?= $sess['status'] === 'open' ? 'ĐANG MỞ' : 'ĐÃ ĐÓNG' ?>]
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </form>
            </div>

            <?php if ($currentSession): ?>
                <form method="POST" action="<?= base_url('teacher/short-answer/quizzes/recalculate/' . $quiz['id']) ?>" class="d-inline mb-0">
                    <?= csrf_field() ?>
                    <input type="hidden" name="session_id" value="<?= $currentSession['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-warning rounded-xl font-semibold text-amber-800" title="Tính lại điểm phạt nộp chậm dựa theo thời gian nộp bài">
                        <i class="fa-solid fa-calculator me-1"></i> Tính lại điểm phạt lượt này
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- BẢNG DANH SÁCH BÀI LÀM CỦA HỌC SINH -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-gray-50 text-gray-700 text-xs uppercase">
                    <tr>
                        <th style="width: 50px;" class="ps-4">STT</th>
                        <th>Học sinh</th>
                        <th>Thời gian nộp</th>
                        <th class="text-center">Điểm ban đầu</th>
                        <th class="text-center">Số bạn nộp sớm hơn (cùng điểm)</th>
                        <th class="text-center">Trừ phạt nộp chậm</th>
                        <th class="text-center pe-4">Điểm tổng kết</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($submissions)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-gray-400">
                                <?php if ($currentSession && $currentSession['status'] === 'open'): ?>
                                    <div class="spinner-border spinner-border-sm text-primary me-1" role="status"></div>
                                    Đang đợi học sinh nộp bài... Hãy bấm F5 để cập nhật danh sách.
                                <?php else: ?>
                                    Chưa có học sinh nào nộp bài trong lượt thi này.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                            // Calculate rank on display to show exactly how it works
                            $coeff = (float)$quiz['penalty_coefficient'];
                            foreach ($submissions as $idx => $sub): 
                                $raw = (float)$sub['raw_score'];
                                // Count how many submitted earlier with same raw score
                                $rank = 0;
                                foreach ($submissions as $other) {
                                    if ($other['id'] == $sub['id']) continue;
                                    if (abs((float)$other['raw_score'] - $raw) < 0.001) {
                                        if (strtotime($other['submitted_at']) < strtotime($sub['submitted_at'])) {
                                            $rank++;
                                        }
                                    }
                                }
                        ?>
                            <tr>
                                <td class="ps-4 font-mono text-sm text-gray-500"><?= $idx + 1 ?></td>
                                <td>
                                    <div class="fw-bold text-gray-900"><?= esc($sub['student_name']) ?></div>
                                    <div class="text-xs text-gray-500 font-mono">@<?= esc($sub['username']) ?></div>
                                </td>
                                <td>
                                    <span class="text-sm text-gray-700 font-mono">
                                        <i class="fa-regular fa-clock me-1 text-gray-400"></i>
                                        <?= date('H:i:s d/m/Y', strtotime($sub['submitted_at'])) ?>
                                    </span>
                                </td>
                                <td class="text-center font-bold text-gray-900">
                                    <span class="badge bg-indigo-50 text-indigo-700 text-sm px-2.5 py-1">
                                        <?= number_format((float)$sub['raw_score'], 2) ?>đ
                                    </span>
                                </td>
                                <td class="text-center font-semibold">
                                    <?php if ($rank > 0): ?>
                                        <span class="badge bg-warning-subtle text-amber-800 px-2.5 py-1 rounded-pill">
                                            <?= $rank ?> bạn nộp sớm hơn
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success px-2.5 py-1 rounded-pill">
                                            <i class="fa-solid fa-trophy text-amber-500 me-1"></i> Nộp sớm nhất nhóm điểm
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center font-mono">
                                    <?php if ((float)$sub['penalty_deducted'] > 0): ?>
                                        <span class="text-danger fw-bold">-<?= number_format((float)$sub['penalty_deducted'], 2) ?>đ</span>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs">0đ</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center pe-4 font-mono">
                                    <span class="text-lg font-bold text-emerald-700">
                                        <?= number_format((float)$sub['final_score'], 2) ?>đ
                                    </span>
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