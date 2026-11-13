<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-3xl mx-auto space-y-6">
    <!-- RESULT SUMMARY CARD -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 text-center space-y-4">
        <div>
            <?php if ((int)$submission['is_mock'] === 1): ?>
                <span class="badge bg-teal-100 text-teal-800 rounded-pill px-3 py-1 font-semibold text-xs">
                    <i class="fa-solid fa-gamepad me-1"></i> KẾT QUẢ THI THỬ
                </span>
            <?php else: ?>
                <span class="badge bg-indigo-100 text-indigo-800 rounded-pill px-3 py-1 font-semibold text-xs">
                    <i class="fa-solid fa-award me-1"></i> KẾT QUẢ KIỂM TRA CHÍNH THỨC
                </span>
            <?php endif; ?>
        </div>

        <div>
            <h1 class="text-2xl font-extrabold text-gray-900"><?= esc($quiz['title']) ?></h1>
            <p class="text-xs text-gray-400 mt-1">
                Thời gian nộp bài: <span class="font-mono text-gray-600"><?= date('H:i:s d/m/Y', strtotime($submission['submitted_at'])) ?></span>
            </p>
        </div>

        <!-- SCORE DISPLAY -->
        <div class="py-4">
            <div class="inline-block bg-gradient-to-br from-indigo-50 to-blue-50 border border-indigo-100 rounded-3xl p-6 shadow-inner">
                <div class="text-xs uppercase font-bold text-gray-500 tracking-wider mb-1">
                    <?= (int)$submission['is_mock'] === 1 ? 'Điểm của bạn' : 'Điểm tổng kết' ?>
                </div>
                <div class="text-5xl font-black text-indigo-600 font-mono tracking-tight">
                    <?= number_format((float)$submission['final_score'], 2) ?>
                    <span class="text-xl font-normal text-gray-400">/ <?= number_format($totalPoints, 2) ?>đ</span>
                </div>

                <?php if ((int)$submission['is_mock'] === 0): ?>
                    <div class="mt-3 pt-3 border-t border-indigo-100 flex items-center justify-center gap-6 text-xs text-gray-600">
                        <div>
                            Điểm làm đúng: <strong class="text-gray-900"><?= number_format((float)$submission['raw_score'], 2) ?>đ</strong>
                        </div>
                        <div>
                            Phạt nộp chậm: 
                            <?php if ((float)$submission['penalty_deducted'] > 0): ?>
                                <strong class="text-danger">-<?= number_format((float)$submission['penalty_deducted'], 2) ?>đ</strong>
                            <?php else: ?>
                                <strong class="text-success">0đ</strong>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ((int)$submission['is_mock'] === 0 && (int)$quiz['is_open'] === 1): ?>
            <div class="alert alert-info rounded-2xl text-xs text-start mb-0">
                <i class="fa-solid fa-circle-info me-1"></i>
                <strong>Lưu ý:</strong> Bài kiểm tra hiện vẫn đang mở cho các bạn trong lớp làm bài. Điểm phạt nộp chậm sẽ được hệ thống chốt chính xác khi giáo viên đóng bài kiểm tra.
            </div>
        <?php endif; ?>
    </div>

    <!-- DETAILED BREAKDOWN PER QUESTION -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 space-y-4">
        <h3 class="text-base font-bold text-gray-900 pb-3 border-bottom">
            <i class="fa-solid fa-list-check text-indigo-600 me-2"></i>Chi Tiết Bài Làm Từng Câu
        </h3>

        <div class="space-y-4">
            <?php 
                $qIdx = 1;
                foreach ($questionResults as $qId => $res): 
                    $info = $questionInfoMap[$qId] ?? null;
                    $isCorrect = !empty($res['correct']);
            ?>
                <div class="p-4 rounded-2xl border <?= $isCorrect ? 'border-emerald-200 bg-emerald-50/20' : 'border-red-200 bg-red-50/20' ?>">
                    <div class="d-flex items-center justify-between mb-2">
                        <div class="d-flex items-center gap-2">
                            <span class="badge <?= $isCorrect ? 'bg-success' : 'bg-danger' ?> rounded-lg px-2.5 py-1 text-xs font-bold">
                                Câu <?= $qIdx++ ?>
                            </span>
                            <?php if ($info): ?>
                                <span class="font-bold text-gray-900 text-sm"><?= esc($info['title']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <?php if ($isCorrect): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 text-xs font-bold rounded-pill">
                                    <i class="fa-solid fa-circle-check me-1"></i> ĐÚNG (+<?= (float)$res['points'] ?>đ)
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 text-xs font-bold rounded-pill">
                                    <i class="fa-solid fa-circle-xmark me-1"></i> CHƯA ĐÚNG (0/<?= (float)$res['max_points'] ?>đ)
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($info): ?>
                        <div class="text-gray-700 text-sm mb-3">
                            <?= nl2br(esc($info['question_text'])) ?>
                        </div>
                    <?php endif; ?>

                    <div class="bg-white rounded-xl p-3 border border-gray-100 text-sm">
                        <span class="text-gray-500 text-xs fw-semibold">Câu trả lời của bạn:</span>
                        <div class="font-mono font-semibold <?= $isCorrect ? 'text-emerald-700' : 'text-danger' ?> mt-0.5">
                            <?= ($res['user_answer'] !== '') ? esc($res['user_answer']) : '<em class="text-gray-400 font-normal">Chưa nhập câu trả lời</em>' ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="pt-4 border-top text-center d-flex justify-center gap-3">
            <a href="<?= base_url('student/dashboard') ?>" class="btn btn-outline-secondary rounded-xl px-5 font-semibold">
                <i class="fa-solid fa-house me-1"></i> Về Bảng điều khiển
            </a>
            <?php if ((int)$submission['is_mock'] === 1): ?>
                <a href="<?= base_url('student/short-answer/mock/' . $quiz['id']) ?>" class="btn btn-teal rounded-xl px-5 font-semibold text-white bg-teal-600 hover:bg-teal-700">
                    <i class="fa-solid fa-rotate-right me-1"></i> Làm lại bài thử
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>