<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6 pb-24">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tìm & Thêm Câu Hỏi</h1>
            <p class="text-gray-500 text-sm">
                Đang chọn câu hỏi cho: <span class="font-bold text-indigo-600"><?= esc($test['title']) ?></span>
            </p>
        </div>
        <a href="<?= base_url("teacher/tests/pool/{$test['id']}") ?>" class="btn btn-outline-secondary btn-sm rounded-lg">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <form method="get" action="<?= current_url() ?>" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Môn học</label>
                <select name="subject_id" class="form-select text-sm rounded-lg" id="subjectFilter">
                    <option value="">-- Tất cả môn --</option>
                    <?php foreach ($subjects as $sub): ?>
                        <option value="<?= $sub['id'] ?>" <?= ($filters['subject_id'] ?? '') == $sub['id'] ? 'selected' : '' ?>>
                            <?= esc($sub['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Khối lớp</label>
                <select name="grade_level" class="form-select text-sm rounded-lg" id="gradeFilter">
                    <option value="">-- Tất cả khối --</option>
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>" <?= ($filters['grade_level'] ?? '') == $i ? 'selected' : '' ?>>
                            Khối <?= $i ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Chủ đề</label>
                <select name="topic_id" class="form-select text-sm rounded-lg" id="topicFilter">
                    <option value="">-- Tất cả chủ đề --</option>
                    <?php if (!empty($topics)): ?>
                        <?php foreach ($topics as $top): ?>
                            <option value="<?= $top['id'] ?>" <?= ($filters['topic_id'] ?? '') == $top['id'] ? 'selected' : '' ?>>
                                <?= esc($top['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Từ đề kiểm tra</label>
                <select name="from_test_id" class="form-select text-sm rounded-lg">
                    <option value="">-- Tất cả câu hỏi --</option>
                    <?php foreach ($otherTests as $ot): ?>
                        <?php if($ot['id'] != $test['id']): ?>
                        <option value="<?= $ot['id'] ?>" <?= ($filters['from_test_id'] ?? '') == $ot['id'] ? 'selected' : '' ?>>
                            <?= esc($ot['title']) ?>
                        </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Từ khoá</label>
                <input type="text" name="keyword" value="<?= esc($filters['keyword'] ?? '') ?>" class="form-control text-sm rounded-lg" placeholder="Nội dung, đáp án...">
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white w-full rounded-lg font-bold">
                    <i class="fa-solid fa-filter me-1"></i> Lọc
                </button>
            </div>
        </form>
    </div>

    <!-- Questions Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <form method="post" action="<?= base_url("teacher/tests/pool/bulk-add/{$test['id']}") ?>" id="bulkAddForm">
            <div class="overflow-x-auto">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                        <tr>
                            <th class="ps-6" style="width: 50px;">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th style="width: 70px;">ID</th>
                            <th>Nội Dung Câu Hỏi</th>
                            <th>Môn / Khối</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <?php if (empty($questions)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-8 text-gray-400">
                                    Không tìm thấy câu hỏi nào phù hợp.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($questions as $q): ?>
                                <tr>
                                    <td class="ps-6">
                                        <input type="checkbox" name="question_ids[]" value="<?= $q['id'] ?>" class="form-check-input q-checkbox" data-json="<?= esc(json_encode($q), 'attr') ?>">
                                    </td>
                                    <td class="font-mono text-gray-400">#<?= $q['id'] ?></td>
                                    <td>
                                        <div class="font-medium text-gray-900 line-clamp-2 max-w-xl">
                                            <?= strip_tags($q['content'], '<img><code><pre><b><i><strong>') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-gray-100 text-gray-600"><?= esc($q['subject_name'] ?? '') ?></span>
                                        <span class="badge bg-indigo-50 text-indigo-700 ms-1"><?= esc($q['topic_name'] ?? '') ?></span>
                                        - K<?= esc($q['grade_level'] ?? '') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Fixed Bottom Action Bar -->
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] p-4 z-40 transition-transform transform translate-y-0" id="bottomActionBar">
                <div class="max-w-7xl mx-auto flex items-center justify-between">
                    <div class="text-gray-700 font-medium">
                        Đã chọn <span id="selectedCount" class="text-indigo-600 font-bold">0</span> câu hỏi
                    </div>
                    <div class="flex gap-3">
                        <button type="button" id="btnPreview" class="btn btn-outline-primary px-6 rounded-xl font-bold" disabled>
                            <i class="fa-solid fa-eye me-1"></i> Xem thử
                        </button>
                        <button type="submit" id="btnAdd" class="btn btn-emerald bg-emerald-600 hover:bg-emerald-700 text-white px-8 rounded-xl font-bold" disabled>
                            <i class="fa-solid fa-check me-1"></i> Thêm vào Pool
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Preview Modal -->
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
            <div class="modal-footer border-t border-gray-100">
                <button type="button" class="btn btn-outline-danger font-bold rounded-lg" id="btnShowAnswers">
                    <i class="fa-solid fa-lightbulb me-1"></i> Hiện đáp án
                </button>
                <button type="button" class="btn btn-secondary rounded-lg" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.q-checkbox');
    const selectAll = document.getElementById('selectAll');
    const selectedCount = document.getElementById('selectedCount');
    const btnPreview = document.getElementById('btnPreview');
    const btnAdd = document.getElementById('btnAdd');
    let previewModal;
    if (typeof bootstrap !== 'undefined') {
        previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
    }
    const previewContent = document.getElementById('previewContent');
    const btnShowAnswers = document.getElementById('btnShowAnswers');

    // Update bottom bar state
    function updateSelection() {
        const checked = document.querySelectorAll('.q-checkbox:checked');
        const count = checked.length;
        selectedCount.textContent = count;
        
        if (count > 0) {
            btnPreview.disabled = false;
            btnAdd.disabled = false;
        } else {
            btnPreview.disabled = true;
            btnAdd.disabled = true;
        }
    }

    // Handle Select All
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateSelection();
        });
    }

    // Handle individual checkboxes
    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            updateSelection();
            if (selectAll) {
                selectAll.checked = document.querySelectorAll('.q-checkbox:checked').length === checkboxes.length && checkboxes.length > 0;
            }
        });
    });

    // Dynamic Topic Loading
    const subjectFilter = document.getElementById('subjectFilter');
    const gradeFilter = document.getElementById('gradeFilter');
    const topicFilter = document.getElementById('topicFilter');

    function loadTopics() {
        const sid = subjectFilter.value;
        const gl = gradeFilter.value;
        if (sid && gl) {
            fetch(`<?= base_url('teacher/get-topics-by-subject-grade') ?>?subject_id=${sid}&grade_level=${gl}`)
                .then(res => res.json())
                .then(data => {
                    topicFilter.innerHTML = '<option value="">-- Tất cả chủ đề --</option>';
                    data.forEach(t => {
                        topicFilter.innerHTML += `<option value="${t.id}">${t.name}</option>`;
                    });
                });
        } else {
            topicFilter.innerHTML = '<option value="">-- Tất cả chủ đề --</option>';
        }
    }

    if (subjectFilter) subjectFilter.addEventListener('change', loadTopics);
    if (gradeFilter) gradeFilter.addEventListener('change', loadTopics);

    // Preview Logic
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
            
            // Render mathjax if present (assuming MathJax is included in layouts/main)
            if (typeof MathJax !== 'undefined' && MathJax.typesetPromise) {
                MathJax.typesetPromise([previewContent]);
            }

            // Add click events to options
            const optionItems = previewContent.querySelectorAll('.option-item');
            optionItems.forEach(item => {
                item.addEventListener('click', function() {
                    // Reset colors in the same question
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

            // Reset show answers button state
            btnShowAnswers.innerHTML = '<i class="fa-solid fa-lightbulb me-1"></i> Hiện đáp án';
            btnShowAnswers.classList.remove('btn-danger');
            btnShowAnswers.classList.add('btn-outline-danger');
            
            if (previewModal) previewModal.show();
        });
    }

    // Show answers logic
    if (btnShowAnswers) {
        btnShowAnswers.addEventListener('click', function() {
            this.innerHTML = '<i class="fa-solid fa-check-double me-1"></i> Đã hiện đáp án';
            this.classList.remove('btn-outline-danger');
            this.classList.add('btn-danger');

            const optionItems = previewContent.querySelectorAll('.option-item');
            optionItems.forEach(item => {
                const isCorrect = item.getAttribute('data-correct') === 'true';
                if (isCorrect) {
                    // Change text color to red
                    const letter = item.querySelector('.option-letter');
                    const text = item.querySelector('.content-render');
                    if (letter) letter.classList.add('text-red-600');
                    if (text) text.classList.add('text-red-600', 'font-bold');
                    
                    // If it was already highlighted with background black, we ensure the text stands out
                    if (item.classList.contains('text-white')) {
                        item.classList.remove('text-white');
                    }
                }
            });
        });
    }
});
</script>
<?= $this->endSection() ?>
