<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-5 pb-24">

    <!-- ===== HEADER ===== -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Ngân hàng Câu hỏi Toàn hệ thống</h1>
            <p class="text-gray-500 text-sm mt-0.5">PUBLIC — tất cả giáo viên có thể xem &amp; dùng chung, chỉ người tạo mới được sửa/xoá.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('teacher/topics') ?>" class="btn btn-outline-indigo rounded-xl px-4 py-2 font-semibold">
                <i class="fa-solid fa-tags me-1"></i> Quản lý Chủ đề
            </a>
            <a href="<?= base_url('teacher/questions/import-aiken') ?>" class="btn btn-outline-secondary rounded-xl px-4 py-2">
                <i class="fa-solid fa-file-import me-1"></i> Import Aiken
            </a>
            <a href="<?= base_url('teacher/questions/create') ?>" class="btn bg-indigo-600 text-white hover:bg-indigo-700 font-bold rounded-xl px-4 py-2">
                <i class="fa-solid fa-plus me-1"></i> Soạn Câu Hỏi Mới
            </a>
        </div>
    </div>

    <!-- ===== FILTER BAR ===== -->
    <form method="GET" action="<?= base_url('teacher/questions') ?>" id="filterForm">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <div class="row g-3 align-items-end">

                <!-- Keyword -->
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold text-gray-700 small mb-1">
                        <i class="fa-solid fa-magnifying-glass me-1 text-indigo-400"></i>Từ khoá
                    </label>
                    <div class="input-group">
                        <input
                            type="text"
                            name="keyword"
                            id="keywordInput"
                            class="form-control rounded-start-xl"
                            placeholder="Tìm trong nội dung câu hỏi, đáp án…"
                            value="<?= esc($filter['keyword'] ?? '') ?>"
                            autocomplete="off"
                        >
                        <?php if (!empty($filter['keyword'])): ?>
                            <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('keywordInput').value=''; document.getElementById('filterForm').submit();" title="Xoá từ khoá">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Subject -->
                <div class="col-12 col-sm-4 col-md-2">
                    <label class="form-label fw-semibold text-gray-700 small mb-1">
                        <i class="fa-solid fa-book me-1 text-indigo-400"></i>Môn học
                    </label>
                    <select name="subject_id" id="filter_subject_id" class="form-select rounded-xl">
                        <option value="">-- Tất cả --</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= esc($s['id']) ?>" <?= ($filter['subject_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                                <?= esc($s['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Grade level -->
                <div class="col-12 col-sm-4 col-md-2">
                    <label class="form-label fw-semibold text-gray-700 small mb-1">
                        <i class="fa-solid fa-layer-group me-1 text-indigo-400"></i>Khối lớp
                    </label>
                    <select name="grade_level" id="filter_grade_level" class="form-select rounded-xl">
                        <option value="">-- Tất cả --</option>
                        <?php for ($g = 1; $g <= 12; $g++): ?>
                            <option value="<?= $g ?>" <?= (string)($filter['grade_level'] ?? '') === (string)$g ? 'selected' : '' ?>>
                                Khối <?= $g ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <!-- Topic -->
                <div class="col-12 col-sm-4 col-md-2">
                    <label class="form-label fw-semibold text-gray-700 small mb-1">
                        <i class="fa-solid fa-tags me-1 text-indigo-400"></i>Chủ đề
                    </label>
                    <select name="topic_id" id="filter_topic_id" class="form-select rounded-xl">
                        <option value="">-- Tất cả --</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn bg-indigo-600 text-white rounded-xl px-4 py-2 flex-grow-1 font-semibold">
                        <i class="fa-solid fa-filter me-1"></i>Lọc
                    </button>
                    <a href="<?= base_url('teacher/questions') ?>" class="btn btn-outline-secondary rounded-xl px-3 py-2" title="Xoá bộ lọc">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>

            </div>

            <!-- Active filter chips -->
            <?php
            $hasFilter = !empty($filter['subject_id']) || !empty($filter['grade_level']) || !empty($filter['keyword']);
            if ($hasFilter): ?>
                <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
                    <span class="text-xs text-gray-500 fw-semibold">Đang lọc:</span>
                    <?php if (!empty($filter['keyword'])): ?>
                        <span class="badge bg-indigo-100 text-indigo-700 rounded-pill px-3 py-1.5 text-xs">
                            <i class="fa-solid fa-magnifying-glass me-1"></i><?= esc($filter['keyword']) ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($filter['subject_id'])): 
                        $sjName = '';
                        foreach($subjects as $sj) { if($sj['id'] == $filter['subject_id']) { $sjName = $sj['name']; break; } }
                    ?>
                        <span class="badge bg-blue-100 text-blue-700 rounded-pill px-3 py-1.5 text-xs">
                            <i class="fa-solid fa-book me-1"></i><?= esc($sjName) ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($filter['topic_id'])): 
                        $topicName = '';
                        foreach($questions as $q) { if($q['topic_id'] == $filter['topic_id']) { $topicName = $q['topic_name']; break; } }
                    ?>
                        <span class="badge bg-indigo-100 text-indigo-700 rounded-pill px-3 py-1.5 text-xs">
                            <i class="fa-solid fa-tags me-1"></i><?= esc($topicName) ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($filter['grade_level'])): ?>
                        <span class="badge bg-emerald-100 text-emerald-700 rounded-pill px-3 py-1.5 text-xs">
                            <i class="fa-solid fa-layer-group me-1"></i>Khối <?= esc($filter['grade_level']) ?>
                        </span>
                    <?php endif; ?>
                    <span class="text-xs text-gray-400 ms-1">
                        — tìm thấy <strong class="text-gray-700"><?= count($questions) ?></strong> câu hỏi
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </form>

    <!-- ===== QUESTIONS TABLE ===== -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <!-- Result summary bar -->
        <div class="px-5 py-3 border-b border-gray-100 d-flex justify-content-between align-items-center">
            <span class="text-sm text-gray-500">
                <?php if ($hasFilter): ?>
                    Kết quả lọc: <strong class="text-gray-800"><?= count($questions) ?></strong> câu hỏi
                <?php else: ?>
                    Tổng cộng: <strong class="text-gray-800"><?= count($questions) ?></strong> câu hỏi
                <?php endif; ?>
            </span>
            <?php if ($hasFilter && count($questions) > 0): ?>
                <a href="<?= base_url('teacher/questions') ?>" class="text-xs text-indigo-500 hover:underline">
                    <i class="fa-solid fa-xmark me-1"></i>Bỏ bộ lọc
                </a>
            <?php endif; ?>
        </div>

        <!-- Export form wraps the table so checkboxes are included -->
        <form method="POST" action="<?= base_url('teacher/questions/export-aiken') ?>" id="exportForm">
            <?= csrf_field() ?>

            <div class="overflow-x-auto">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                        <tr>
                            <th class="ps-5" style="width: 46px;">
                                <input type="checkbox" id="selectAll" class="form-check-input" title="Chọn tất cả">
                            </th>
                            <th style="width: 50px;">ID</th>
                            <th>Nội Dung Câu Hỏi</th>
                            <th>Số Đáp Án</th>
                            <th>Đáp Án Đúng</th>
                            <th>Tác Giả</th>
                            <th class="text-end pe-6">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <?php if (empty($questions)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-12 text-gray-400">
                                    <?php if ($hasFilter): ?>
                                        <i class="fa-solid fa-magnifying-glass text-3xl mb-3 block opacity-30"></i>
                                        Không tìm thấy câu hỏi nào phù hợp với bộ lọc.
                                        <br><a href="<?= base_url('teacher/questions') ?>" class="text-indigo-500 text-sm mt-2 inline-block">Xem tất cả câu hỏi</a>
                                    <?php else: ?>
                                        <i class="fa-solid fa-database text-3xl mb-3 block opacity-30"></i>
                                        Chưa có câu hỏi nào trong Ngân hàng. Hãy tạo câu hỏi đầu tiên!
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($questions as $q): ?>
                                <?php
                                    $optCount  = 2;
                                    if (!empty($q['option_c'])) $optCount++;
                                    if (!empty($q['option_d'])) $optCount++;
                                    $isCreator = ((int)$q['creator_id'] === (int)$teacher_id);
                                    $rawContent = strip_tags($q['content'], '<img><code><pre><b><i><strong>');
                                    // Highlight keyword in content preview
                                    $kw = $filter['keyword'] ?? '';
                                    if (!empty($kw)) {
                                        $rawContent = preg_replace(
                                            '/(' . preg_quote(esc($kw), '/') . ')/iu',
                                            '<mark class="bg-yellow-200 text-gray-900 rounded px-0.5">$1</mark>',
                                            $rawContent
                                        );
                                    }
                                ?>
                                <tr>
                                    <td class="ps-5">
                                        <input
                                            type="checkbox"
                                            name="question_ids[]"
                                            value="<?= $q['id'] ?>"
                                            class="form-check-input q-checkbox"
                                            data-json="<?= esc(json_encode($q), 'attr') ?>"
                                        >
                                    </td>
                                    <td class="font-mono text-gray-400">#<?= $q['id'] ?></td>
                                    <td style="max-width: 460px;">
                                        <div class="font-medium text-gray-900 line-clamp-2"><?= $rawContent ?></div>
                                        <div class="mt-1 d-flex gap-1 flex-wrap">
                                            <span class="badge bg-gray-100 text-gray-600 text-xs font-normal"><?= esc($q['subject_name']) ?></span>
                                            <span class="badge bg-indigo-50 text-indigo-700 text-xs font-normal">Chủ đề: <?= esc($q['topic_name']) ?></span>
                                            <span class="badge bg-gray-100 text-gray-600 text-xs font-normal">Khối <?= esc($q['grade_level']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $optCount === 2 ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800' ?> font-semibold">
                                            <?= $optCount === 2 ? '2 Đáp án' : "{$optCount} Đáp án" ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-emerald-600 text-white font-bold text-sm px-2.5 py-1">
                                            <?= esc($q['correct_option']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-gray-700 <?= $isCreator ? 'fw-bold text-indigo-600' : '' ?>">
                                            <?= esc($q['creator_name'] ?? 'Giáo viên') ?>
                                            <?= $isCreator ? ' <span class="badge bg-indigo-100 text-indigo-600 text-xs">Tôi</span>' : '' ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-6">
                                        <?php if ($isCreator): ?>
                                            <a href="<?= base_url("teacher/questions/edit/{$q['id']}") ?>" class="btn btn-outline-secondary btn-sm rounded-lg me-1">
                                                <i class="fa-solid fa-pen-to-square"></i> Sửa
                                            </a>
                                            <a href="<?= base_url("teacher/questions/delete/{$q['id']}") ?>" class="btn btn-outline-danger btn-sm rounded-lg" onclick="return confirm('Bạn có chắc muốn xoá câu hỏi này?')">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-400 italic">Chỉ xem</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ===== FIXED BOTTOM ACTION BAR ===== -->
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] p-4 z-40" id="bottomActionBar">
                <div class="max-w-7xl mx-auto flex items-center justify-between">
                    <div class="text-gray-700 font-medium">
                        Đã chọn <span id="selectedCount" class="text-indigo-600 font-bold">0</span> câu hỏi
                    </div>
                    <div class="flex gap-3">
                        <button type="button" id="btnPreview" class="btn btn-outline-primary px-5 rounded-xl font-bold" disabled>
                            <i class="fa-solid fa-eye me-1"></i> Xem thử
                        </button>
                        <button type="submit" id="btnExport" class="btn bg-emerald-600 hover:bg-emerald-700 text-white px-6 rounded-xl font-bold" disabled>
                            <i class="fa-solid fa-file-export me-1"></i> Export Aiken
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

<!-- ===== PREVIEW MODAL ===== -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-b border-gray-100 bg-gray-50">
                <h5 class="modal-title font-bold text-gray-900"><i class="fa-solid fa-eye text-indigo-600 me-2"></i> Xem thử câu hỏi đã chọn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-6 bg-gray-50/50" id="previewContent">
                <!-- Injected via JS -->
            </div>
            <div class="modal-footer border-t border-gray-100 d-flex justify-content-between align-items-center">
                <div class="text-sm text-gray-500">
                    Nhấp vào đáp án để chọn, hoặc dùng nút bên phải để hiện đáp án đúng.
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-danger font-bold rounded-lg" id="btnShowAnswers">
                        <i class="fa-solid fa-lightbulb me-1"></i> Hiện đáp án
                    </button>
                    <button type="button" class="btn btn-secondary rounded-lg" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Submit form on Enter in keyword field
document.getElementById('keywordInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); this.form.submit(); }
});

document.addEventListener('DOMContentLoaded', function() {

    // ── Filter: Dynamic Topic Loading ────────────────────────────────────
    const filterSubject = document.getElementById('filter_subject_id');
    const filterGrade   = document.getElementById('filter_grade_level');
    const filterTopic   = document.getElementById('filter_topic_id');
    const currentTopicId = '<?= esc($filter['topic_id'] ?? '') ?>';

    function loadFilterTopics() {
        const subjectId  = filterSubject.value;
        const gradeLevel = filterGrade.value;
        
        filterTopic.innerHTML = '<option value="">-- Tất cả --</option>';
        if (!subjectId || !gradeLevel) return;

        fetch(`<?= base_url('teacher/get-topics-by-subject-grade') ?>?subject_id=${subjectId}&grade_level=${gradeLevel}`)
            .then(res => res.json())
            .then(data => {
                data.forEach(topic => {
                    const option = document.createElement('option');
                    option.value = topic.id;
                    option.textContent = topic.name;
                    if (currentTopicId && topic.id == currentTopicId) {
                        option.selected = true;
                    }
                    filterTopic.appendChild(option);
                });
            });
    }

    filterSubject.addEventListener('change', loadFilterTopics);
    filterGrade.addEventListener('change', loadFilterTopics);

    if (filterSubject.value && filterGrade.value) {
        loadFilterTopics();
    }

    // ── Selection Logic ──────────────────────────────────────────────────
    const checkboxes    = document.querySelectorAll('.q-checkbox');
    const selectAll     = document.getElementById('selectAll');
    const selectedCount = document.getElementById('selectedCount');
    const btnPreview    = document.getElementById('btnPreview');
    const btnExport     = document.getElementById('btnExport');

    function updateSelection() {
        const count = document.querySelectorAll('.q-checkbox:checked').length;
        selectedCount.textContent = count;
        const hasSelection = count > 0;
        btnPreview.disabled = !hasSelection;
        btnExport.disabled  = !hasSelection;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateSelection();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            updateSelection();
            if (selectAll) {
                const checkedCount = document.querySelectorAll('.q-checkbox:checked').length;
                selectAll.checked = checkedCount === checkboxes.length && checkboxes.length > 0;
                selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
            }
        });
    });

    // ── Preview Logic ────────────────────────────────────────────────────
    let previewModal;
    if (typeof bootstrap !== 'undefined') {
        previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
    }
    const previewContent  = document.getElementById('previewContent');
    const btnShowAnswers  = document.getElementById('btnShowAnswers');

    if (btnPreview) {
        btnPreview.addEventListener('click', function() {
            const checked = document.querySelectorAll('.q-checkbox:checked');
            let html = '';

            checked.forEach((cb, index) => {
                const q = JSON.parse(cb.dataset.json);

                html += `
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-4 preview-q-card">
                    <div class="font-bold text-indigo-700 mb-3 border-b border-gray-100 pb-2">
                        Câu ${index + 1} <span class="text-sm font-normal text-gray-400 ms-2">ID: #${q.id}</span>
                    </div>
                    <div class="text-gray-800 mb-4 content-render">
                        ${q.content}
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 options-container">
                `;

                const options = { 'A': q.option_a, 'B': q.option_b, 'C': q.option_c, 'D': q.option_d };
                for (const [key, val] of Object.entries(options)) {
                    if (val && val.trim() !== '') {
                        const isCorrect = (key === q.correct_option) ? 'true' : 'false';
                        html += `
                        <div class="p-3 border border-gray-200 rounded-lg cursor-pointer option-item transition-colors duration-200 hover:bg-gray-50 flex items-start gap-3" data-correct="${isCorrect}">
                            <div class="font-bold option-letter w-6 shrink-0">${key}.</div>
                            <div class="flex-grow content-render">${val}</div>
                        </div>`;
                    }
                }

                html += `
                    </div>
                </div>`;
            });

            previewContent.innerHTML = html;

            // Re-render MathJax if available
            if (typeof MathJax !== 'undefined' && MathJax.typesetPromise) {
                MathJax.typesetPromise([previewContent]);
            }

            // Click to select answer
            const optionItems = previewContent.querySelectorAll('.option-item');
            optionItems.forEach(item => {
                item.addEventListener('click', function() {
                    const siblings = this.closest('.options-container').querySelectorAll('.option-item');
                    siblings.forEach(sib => {
                        sib.classList.remove('bg-yellow-200', 'bg-gray-800', 'text-white', 'border-gray-800', 'border-yellow-300');
                        sib.classList.add('border-gray-200', 'hover:bg-gray-50');
                    });

                    const isCorrect = this.getAttribute('data-correct') === 'true';
                    if (isCorrect) {
                        this.classList.remove('border-gray-200', 'hover:bg-gray-50');
                        this.classList.add('bg-yellow-200', 'border-yellow-300');
                    } else {
                        this.classList.remove('border-gray-200', 'hover:bg-gray-50');
                        this.classList.add('bg-gray-800', 'text-white', 'border-gray-800');
                    }
                });
            });

            // Reset show-answers button
            btnShowAnswers.innerHTML = '<i class="fa-solid fa-lightbulb me-1"></i> Hiện đáp án';
            btnShowAnswers.classList.remove('btn-danger');
            btnShowAnswers.classList.add('btn-outline-danger');

            if (previewModal) previewModal.show();
        });
    }

    // Show all correct answers
    if (btnShowAnswers) {
        btnShowAnswers.addEventListener('click', function() {
            this.innerHTML = '<i class="fa-solid fa-check-double me-1"></i> Đã hiện đáp án';
            this.classList.remove('btn-outline-danger');
            this.classList.add('btn-danger');

            const optionItems = previewContent.querySelectorAll('.option-item');
            optionItems.forEach(item => {
                const isCorrect = item.getAttribute('data-correct') === 'true';
                if (isCorrect) {
                    // Remove wrong-answer styling first
                    item.classList.remove('bg-gray-800', 'text-white', 'border-gray-800', 'hover:bg-gray-50');
                    item.classList.add('bg-yellow-200', 'border-yellow-300');

                    const letter = item.querySelector('.option-letter');
                    const text   = item.querySelector('.content-render');
                    if (letter) letter.classList.add('text-emerald-700');
                    if (text)   text.classList.add('text-emerald-700', 'font-bold');
                }
            });
        });
    }
});
</script>
<?= $this->endSection() ?>
