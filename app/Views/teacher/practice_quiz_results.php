<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Kết Quả Luyện Tập: <?= esc($quiz['title']) ?></h1>
            <p class="text-gray-500 text-sm">Danh sách học sinh đã hoàn thành bài luyện tập tự do này.</p>
        </div>
        <a href="<?= base_url('teacher/practice-quizzes') ?>" class="btn btn-outline-secondary rounded-xl px-4 py-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <!-- Stats summary -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="text-xs text-gray-500 font-semibold uppercase">Tổng Lượt Làm Bài</div>
            <div class="text-3xl font-black text-indigo-600 mt-1"><?= count($results) ?></div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="text-xs text-gray-500 font-semibold uppercase">Điểm Trung Bình (Tỷ Lệ Đúng)</div>
            <div class="text-3xl font-black text-emerald-600 mt-1">
                <?php
                if (!empty($results)) {
                    $sum = 0;
                    foreach ($results as $r) {
                        $sum += $r['total_questions'] > 0 ? ($r['score_correct'] / $r['total_questions']) * 100 : 0;
                    }
                    echo round($sum / count($results), 1) . '%';
                } else {
                    echo '0%';
                }
                ?>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="text-xs text-gray-500 font-semibold uppercase">Mã Đề / Link Chia Sẻ</div>
            <div class="text-sm font-mono text-gray-800 mt-1 truncate">
                <a href="<?= base_url("practice/{$quiz['slug']}") ?>" target="_blank" class="text-indigo-600 hover:underline">
                    <?= base_url("practice/{$quiz['slug']}") ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table table-hover align-middle mb-0 text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                    <tr>
                        <th class="ps-6">STT</th>
                        <th>Họ Và Tên Học Sinh</th>
                        <th>Số Câu Đúng</th>
                        <th>Tổng Số Câu</th>
                        <th>Tỷ Lệ Hoàn Thành</th>
                        <th class="text-end pe-6">Thời Gian Nộp Bài</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($results)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-400">
                                Chưa có lượt làm bài nào cho đề luyện tập này.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($results as $index => $res): ?>
                            <?php 
                                $percent = $res['total_questions'] > 0 ? round(($res['score_correct'] / $res['total_questions']) * 100) : 0;
                            ?>
                            <tr>
                                <td class="ps-6 font-mono text-gray-400">#<?= $index + 1 ?></td>
                                <td class="font-bold text-gray-900"><?= esc($res['student_name']) ?></td>
                                <td><span class="badge bg-emerald-100 text-emerald-800 font-bold"><?= $res['score_correct'] ?> câu</span></td>
                                <td><?= $res['total_questions'] ?> câu</td>
                                <td>
                                    <span class="badge <?= $percent >= 80 ? 'bg-emerald-600' : ($percent >= 50 ? 'bg-amber-500' : 'bg-rose-500') ?> text-white font-bold px-2.5 py-1">
                                        <?= $percent ?>%
                                    </span>
                                </td>
                                <td class="text-end pe-6 text-gray-500 text-xs"><?= date('H:i:s d/m/Y', strtotime($res['completed_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
