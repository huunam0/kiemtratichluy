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
                        <th>Tỉ Lệ Chọn Đúng</th>
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
                                $eqId = !empty($q['equivalent_id']) ? $q['equivalent_id'] : $q['id'];
                            ?>
                            <tr>
                                <td class="ps-6 font-mono text-gray-400">
                                    <div>#<?= $q['id'] ?></div>
                                    <div class="mt-0.5">
                                        <span class="badge bg-purple-50 text-purple-700 border border-purple-200 text-xs font-normal" title="Mã nhóm câu hỏi tương đương #<?= $eqId ?>">
                                            <i class="fa-solid fa-layer-group me-0.5"></i>#<?= $eqId ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="font-medium text-gray-900 line-clamp-2 max-w-xl">
                                        <?= format_preview_content($q['content']) ?>
                                    </div>
                                    <div class="mt-1">
                                        <span class="badge bg-purple-50 text-purple-700 border border-purple-100 text-xs font-normal">
                                            <i class="fa-solid fa-layer-group me-1"></i>Nhóm #<?= $eqId ?>
                                        </span>
                                    </div>
                                </td>
                                <td><span class="badge bg-gray-100 text-gray-800 font-medium"><?= $optCount ?> đáp án</span></td>
                                <td>
                                    <?php
                                        $testPicked  = $q['test_picked'] ?? 0;
                                        $testCorrect = $q['test_correct'] ?? 0;
                                        $allPicked   = $q['picked'] ?? 0;
                                        $allCorrect  = $q['correct'] ?? 0;

                                        $rate = $testPicked > 0 ? round(($testCorrect / $testPicked) * 100) : 0;
                                        $badgeClass = 'bg-gray-100 text-gray-400 border border-gray-200';
                                        if ($testPicked > 0) {
                                            if ($rate >= 70) $badgeClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                                            elseif ($rate >= 40) $badgeClass = 'bg-blue-50 text-blue-700 border border-blue-200';
                                            else $badgeClass = 'bg-amber-50 text-amber-700 border border-amber-200';
                                        }
                                    ?>
                                    <div>
                                        <span class="badge <?= $badgeClass ?> font-bold text-sm px-2.5 py-1" title="<?= $testPicked > 0 ? "Trong bài kiểm tra này: {$testCorrect}/{$testPicked} ({$rate}%)" : "Chưa có lượt thi trong bài này" ?> | Đáp án gốc: <?= esc($q['correct_option']) ?>">
                                            <?= "{$testCorrect}/{$testPicked}" ?>
                                        </span>
                                        <?php if ($allPicked > 0 && $allPicked !== $testPicked): ?>
                                            <div class="text-[11px] text-gray-400 mt-0.5" title="Thống kê toàn hệ thống: <?= $allCorrect ?>/<?= $allPicked ?>">
                                                Chung: <?= "{$allCorrect}/{$allPicked}" ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-gray-500 text-xs">
                                    <?= !empty($q['added_at']) ? date('d/m/Y H:i', strtotime($q['added_at'])) : '-' ?>
                                </td>
                                <td class="text-end pe-6 text-nowrap">
                                    <button type="button" 
                                            class="btn btn-outline-indigo btn-sm rounded-lg me-1 btn-view-question" 
                                            data-question="<?= esc(json_encode($q), 'attr') ?>"
                                            title="Xem chi tiết câu hỏi và tỉ lệ chọn từng phương án">
                                        <i class="fa-solid fa-eye me-1"></i> Xem
                                    </button>
                                    <a href="<?= base_url("teacher/tests/pool/remove/{$test['id']}/{$q['id']}") ?>" 
                                       class="btn btn-outline-danger btn-sm rounded-lg"
                                       onclick="return confirm('Bạn có chắc muốn gỡ câu hỏi này khỏi kho?');">
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

<!-- Modal Xem Chi Tiết Câu Hỏi & Thống Kê Phương Án -->
<div class="modal fade" id="modalQuestionDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-2xl shadow-xl border-0 overflow-hidden">
            <div class="modal-header bg-gray-50 border-b border-gray-100 py-3.5 px-6">
                <div class="flex items-center gap-2">
                    <span class="badge bg-indigo-100 text-indigo-700 font-mono text-xs px-2.5 py-1 rounded" id="modalQuestionId">#0</span>
                    <span class="badge bg-purple-50 text-purple-700 border border-purple-200 text-xs px-2 py-0.5 rounded" id="modalEquivGroup">
                        <i class="fa-solid fa-layer-group me-1"></i>Nhóm #0
                    </span>
                    <h5 class="modal-title font-bold text-gray-900 text-base mb-0">Chi Tiết Câu Hỏi & Tỉ Lệ Chọn Phương Án</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-6 space-y-4">
                <!-- Nội dung câu hỏi -->
                <div>
                    <label class="text-xs uppercase font-bold text-gray-400 mb-1.5 block">
                        <i class="fa-solid fa-circle-question text-indigo-500 me-1"></i> Nội dung câu hỏi
                    </label>
                    <div class="p-4 bg-indigo-50/20 border-2 border-indigo-100 rounded-xl text-gray-900 font-medium leading-relaxed text-sm content-render" id="modalQuestionContent">
                        <!-- Rendered content -->
                    </div>
                </div>

                <!-- Thống kê tổng quan -->
                <div class="d-flex items-center justify-between flex-wrap gap-2 p-3 bg-indigo-50/60 border border-indigo-100 rounded-xl text-xs">
                    <div class="text-gray-700">
                        <i class="fa-solid fa-chart-pie text-indigo-600 me-1.5"></i>
                        <span id="modalStatsOverview">Thống kê trong bài kiểm tra này</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Tỉ lệ đúng:</span>
                        <span class="badge bg-emerald-600 text-white font-bold ms-1 text-xs px-2 py-0.5" id="modalAccuracyRate">0/0 (0%)</span>
                    </div>
                </div>

                <!-- Danh sách các phương án -->
                <div>
                    <label class="text-xs uppercase font-bold text-gray-400 mb-2 block">
                        <i class="fa-solid fa-list-check text-indigo-500 me-1"></i> Các phương án lựa chọn & Số lần học sinh chọn
                    </label>
                    <div class="space-y-3" id="modalOptionsList">
                        <!-- Options injected via JS -->
                    </div>
                </div>

                <!-- Lời giải / Giải thích -->
                <div id="modalExplanationWrapper" class="pt-2 border-t border-gray-100 d-none">
                    <label class="text-xs uppercase font-bold text-gray-400 mb-1 block">
                        <i class="fa-solid fa-lightbulb text-amber-500 me-1"></i> Lời giải / Giải thích chi tiết
                    </label>
                    <div class="p-3 bg-amber-50/70 border border-amber-200 rounded-xl text-xs text-gray-800 leading-relaxed" id="modalExplanationContent">
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-gray-50 border-t border-gray-100 py-3 px-6 flex justify-between items-center">
                <a href="#" id="modalFullPreviewLink" class="btn btn-outline-indigo btn-sm rounded-lg font-semibold">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở trang chi tiết đầy đủ
                </a>
                <button type="button" class="btn btn-secondary btn-sm rounded-lg px-4" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('modalQuestionDetail');
    if (!modalEl) return;
    const bsModal = new bootstrap.Modal(modalEl);

    document.querySelectorAll('.btn-view-question').forEach(btn => {
        btn.addEventListener('click', function() {
            const rawJson = this.getAttribute('data-question');
            if (!rawJson) return;

            let q;
            try {
                q = JSON.parse(rawJson);
            } catch (e) {
                console.error('Error parsing question data:', e);
                return;
            }

            const eqId = q.equivalent_id || q.id;
            document.getElementById('modalQuestionId').textContent = '#' + q.id;
            document.getElementById('modalEquivGroup').innerHTML = '<i class="fa-solid fa-layer-group me-1"></i>Nhóm #' + eqId;

            // Render question content
            const contentEl = document.getElementById('modalQuestionContent');
            if (typeof window.formatQuestionContent === 'function') {
                contentEl.innerHTML = window.formatQuestionContent(q.content);
            } else {
                contentEl.innerHTML = q.content || '';
            }

            // Stats
            const optStats = q.option_stats || {};
            const testTotal = optStats.test_total !== undefined ? optStats.test_total : (q.test_picked || 0);
            const globalTotal = optStats.total !== undefined ? optStats.total : (q.picked || 0);
            const testCorrect = q.test_correct || 0;
            const rate = testTotal > 0 ? Math.round((testCorrect / testTotal) * 100) : 0;

            const overviewEl = document.getElementById('modalStatsOverview');
            let overviewText = `Trong bài kiểm tra này: <strong>${testTotal}</strong> lượt câu hỏi được chọn vào đề`;
            if (globalTotal > testTotal) {
                overviewText += ` <span class="text-gray-400 ms-1">(Toàn hệ thống: ${globalTotal} lượt)</span>`;
            }
            overviewEl.innerHTML = overviewText;

            document.getElementById('modalAccuracyRate').textContent = `${testCorrect}/${testTotal} (${rate}%)`;

            // Render options
            const optionsContainer = document.getElementById('modalOptionsList');
            optionsContainer.innerHTML = '';

            const optionKeys = ['A', 'B', 'C', 'D'];
            const correctOpt = (q.correct_option || '').toUpperCase();

            optionKeys.forEach(optKey => {
                const optVal = q['option_' + optKey.toLowerCase()];
                if (optVal === null || optVal === undefined || optVal === '') return;

                const isCorrect = (correctOpt === optKey);
                const optData = (optStats.options && optStats.options[optKey]) ? optStats.options[optKey] : { total: 0, test: 0 };
                const optCount = optData.test !== undefined ? optData.test : 0;
                const globalOptCount = optData.total !== undefined ? optData.total : 0;
                const percent = testTotal > 0 ? Math.round((optCount / testTotal) * 100) : 0;

                let formattedOpt = optVal;
                if (typeof window.formatOptionContent === 'function') {
                    formattedOpt = window.formatOptionContent(optVal);
                }

                const optCard = document.createElement('div');
                optCard.className = `p-3.5 rounded-xl border-2 ${isCorrect ? 'border-emerald-500 bg-emerald-50/40' : 'border-gray-200 bg-white'}`;

                let globalText = '';
                if (globalTotal > testTotal) {
                    globalText = `<span class="text-gray-400 ms-1 text-[11px]" title="Toàn hệ thống: ${globalOptCount}/${globalTotal}">[Chung: ${globalOptCount}/${globalTotal}]</span>`;
                }

                optCard.innerHTML = `
                    <div class="flex items-start gap-3">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs shrink-0 ${isCorrect ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700'}">
                            ${optKey}
                        </span>
                        <div class="flex-grow pt-0.5 text-sm font-medium text-gray-800">
                            ${formattedOpt}
                        </div>
                        ${isCorrect ? '<span class="badge bg-emerald-600 text-white text-xs px-2 py-0.5 rounded shrink-0"><i class="fa-solid fa-check me-1"></i>Đúng</span>' : ''}
                    </div>
                    <div class="mt-2.5 pt-2 border-t ${isCorrect ? 'border-emerald-200/60' : 'border-gray-100'}">
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-gray-600 font-medium">
                                <i class="fa-solid fa-user-check me-1 ${isCorrect ? 'text-emerald-600' : 'text-indigo-500'}"></i>
                                Học sinh chọn: <strong class="${isCorrect ? 'text-emerald-700' : 'text-indigo-700'}">${optCount}/${testTotal}</strong>
                                <span class="text-gray-500 font-semibold">(${percent}%)</span>
                                ${globalText}
                            </span>
                        </div>
                        <div class="w-full bg-gray-200/80 rounded-full h-2 overflow-hidden">
                            <div class="${isCorrect ? 'bg-emerald-500' : 'bg-indigo-500'} h-2 rounded-full transition-all duration-300" style="width: ${percent}%"></div>
                        </div>
                    </div>
                `;
                optionsContainer.appendChild(optCard);
            });

            // Unanswered info if any
            const unanswered = optStats.unanswered ? (optStats.unanswered.test || 0) : 0;
            if (unanswered > 0) {
                const unDiv = document.createElement('div');
                unDiv.className = 'text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-2.5';
                unDiv.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-1"></i> Có <strong>${unanswered}/${testTotal}</strong> lượt làm bài không chọn đáp án (bỏ trống hoặc hết giờ).`;
                optionsContainer.appendChild(unDiv);
            }

            // Explanation
            const expWrapper = document.getElementById('modalExplanationWrapper');
            const expContent = document.getElementById('modalExplanationContent');
            if (q.explanation && q.explanation.trim() !== '') {
                expContent.textContent = q.explanation.trim();
                expWrapper.classList.remove('d-none');
            } else {
                expWrapper.classList.add('d-none');
            }

            // Full preview link
            const fullLink = document.getElementById('modalFullPreviewLink');
            fullLink.href = `<?= base_url('teacher/questions/preview/') ?>/${q.id}?from_test=<?= $test['id'] ?>`;

            bsModal.show();
        });
    });
});
</script>
<?= $this->endSection() ?>
