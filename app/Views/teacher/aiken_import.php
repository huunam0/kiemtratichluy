<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                <i class="fa-solid fa-file-import me-2 text-indigo-600"></i>Import Câu Hỏi (Định dạng Aiken)
            </h1>
            <p class="text-gray-500 text-sm mt-1">
                Dán nhiều câu hỏi trắc nghiệm cùng lúc — tất cả sẽ thuộc cùng một <strong>môn học</strong> và <strong>khối lớp</strong>.
            </p>
        </div>
        <a href="<?= base_url('teacher/questions') ?>" class="btn btn-outline-secondary rounded-xl px-4 py-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại Ngân hàng
        </a>
    </div>

    <div class="row g-4">

        <!-- ===== IMPORT FORM ===== -->
        <div class="col-lg-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fa-solid fa-paste me-2 text-indigo-500"></i>Nội dung Aiken
                </h2>

                <form method="POST" action="<?= base_url('teacher/questions/import-aiken') ?>">
                    <?= csrf_field() ?>

                    <!-- Subject, Grade, Topic -->
                    <div class="row g-3 mb-4">
                        <div class="col-sm-4">
                            <label class="form-label fw-semibold text-gray-700">
                                <i class="fa-solid fa-book me-1 text-indigo-400"></i>Môn học <span class="text-danger">*</span>
                            </label>
                            <select name="subject_id" id="subject_id" class="form-select rounded-xl" required>
                                <option value="">-- Chọn môn --</option>
                                <?php foreach ($subjects as $s): ?>
                                    <option value="<?= esc($s['id']) ?>" <?= old('subject_id') == $s['id'] ? 'selected' : '' ?>>
                                        <?= esc($s['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label fw-semibold text-gray-700">
                                <i class="fa-solid fa-layer-group me-1 text-indigo-400"></i>Khối lớp <span class="text-danger">*</span>
                            </label>
                            <select name="grade_level" id="grade_level" class="form-select rounded-xl" required>
                                <option value="">-- Chọn khối --</option>
                                <?php
                                $oldGrade = (int)old('grade_level', 0);
                                for ($g = 1; $g <= 12; $g++): ?>
                                    <option value="<?= $g ?>" <?= $oldGrade === $g ? 'selected' : '' ?>>
                                        Khối <?= $g ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label fw-semibold text-gray-700">
                                <i class="fa-solid fa-tags me-1 text-indigo-400"></i>Chủ đề <span class="text-danger">*</span>
                            </label>
                            <select name="topic_id" id="topic_id" class="form-select rounded-xl" required>
                                <option value="">-- Chọn Chủ đề --</option>
                            </select>
                        </div>
                    </div>

                    <!-- Aiken textarea -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-gray-700">
                            <i class="fa-solid fa-align-left me-1 text-indigo-400"></i>Dán văn bản định dạng Aiken <span class="text-danger">*</span>
                        </label>
                        <textarea
                            name="aiken_text"
                            id="aikenText"
                            rows="18"
                            class="form-control font-monospace rounded-xl"
                            style="font-size: 0.875rem; line-height: 1.6; resize: vertical;"
                            placeholder="Dán nội dung định dạng Aiken vào đây..."
                            required
                        ><?= old('aiken_text') ?></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">Mỗi câu hỏi cách nhau ít nhất <strong>1 dòng trống</strong>.</small>
                            <small id="lineCount" class="text-muted"></small>
                        </div>
                    </div>

                    <!-- Live preview counter -->
                    <div id="previewBar" class="alert alert-light border rounded-xl py-2 px-3 mb-4 d-flex align-items-center gap-3 small text-gray-700" style="display:none;">
                        <i class="fa-solid fa-magnifying-glass text-indigo-400"></i>
                        <span>Phát hiện sơ bộ: <strong id="previewCount">0</strong> câu hỏi hợp lệ</span>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl px-5 py-2">
                            <i class="fa-solid fa-upload me-2"></i>Import Ngay
                        </button>
                        <button type="button" onclick="document.getElementById('aikenText').value=''; updatePreview();" class="btn btn-outline-secondary rounded-xl px-4 py-2">
                            <i class="fa-solid fa-eraser me-1"></i>Xoá trắng
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ===== FORMAT GUIDE ===== -->
        <div class="col-lg-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sticky-top" style="top: 80px;">
                <h2 class="text-base font-semibold text-gray-800 mb-3">
                    <i class="fa-solid fa-circle-info me-2 text-indigo-500"></i>Hướng dẫn định dạng Aiken
                </h2>

                <div class="bg-indigo-50 border border-indigo-200 rounded-xl mb-4 p-3">
                    <p class="fw-semibold text-indigo-700 mb-2 small">Cấu trúc 1 câu hỏi:</p>
                    <pre class="mb-0 bg-transparent p-0 small" style="color: #374151;">Nội dung câu hỏi?
A. Đáp án A
B. Đáp án B
C. Đáp án C
D. Đáp án D
ANSWER: C</pre>
                </div>

                <ul class="list-unstyled small mb-0 text-gray-600 space-y-2">
                    <li class="d-flex gap-2 mb-2">
                        <i class="fa-solid fa-check-circle text-success mt-1 flex-shrink-0"></i>
                        <span>Dòng đầu tiên: nội dung câu hỏi (không có tiền tố).</span>
                    </li>
                    <li class="d-flex gap-2 mb-2">
                        <i class="fa-solid fa-check-circle text-success mt-1 flex-shrink-0"></i>
                        <span>Đáp án bắt đầu bằng <code>A.</code> <code>B.</code> <code>C.</code> <code>D.</code> — C, D là tuỳ chọn.</span>
                    </li>
                    <li class="d-flex gap-2 mb-2">
                        <i class="fa-solid fa-check-circle text-success mt-1 flex-shrink-0"></i>
                        <span>Dòng cuối bắt buộc: <code>ANSWER: X</code></span>
                    </li>
                    <li class="d-flex gap-2 mb-2">
                        <i class="fa-solid fa-check-circle text-success mt-1 flex-shrink-0"></i>
                        <span>Mỗi câu cách nhau ít nhất <strong>1 dòng trống</strong>.</span>
                    </li>
                    <li class="d-flex gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-warning mt-1 flex-shrink-0"></i>
                        <span>Câu trùng nội dung (cùng môn + khối) sẽ <strong>bị bỏ qua</strong>.</span>
                    </li>
                </ul>

                <hr class="my-4">

                <p class="text-xs fw-semibold text-muted mb-2">Ví dụ đầy đủ:</p>
                <pre class="bg-gray-50 rounded-xl p-3 border border-gray-200 small" style="color: #374151; white-space: pre-wrap; font-size: 0.75rem;">Nước sôi ở bao nhiêu độ C?
A. 90
B. 100
C. 110
D. 120
ANSWER: B

Thủ đô của Việt Nam là?
A. Hồ Chí Minh
B. Đà Nẵng
C. Hà Nội
D. Huế
ANSWER: C</pre>
            </div>
        </div>

    </div>
</div>

<script>
function updatePreview() {
    var textarea = document.getElementById('aikenText');
    var previewBar = document.getElementById('previewBar');
    var previewCount = document.getElementById('previewCount');
    var lineCount = document.getElementById('lineCount');

    var val = textarea.value;
    var lines = val.split('\n').length;
    lineCount.textContent = lines + ' dòng';

    var matches = (val.match(/^ANSWER\s*:\s*[A-Da-d]\s*$/gim) || []).length;
    if (matches > 0) {
        previewBar.style.display = '';
        previewCount.textContent = matches;
    } else {
        previewBar.style.display = 'none';
    }
}

document.getElementById('aikenText').addEventListener('input', updatePreview);

document.addEventListener('DOMContentLoaded', function() {
    // Initial load check
    updatePreview();

    // AJAX Load Topics
    const subjectSelect = document.getElementById('subject_id');
    const gradeSelect = document.getElementById('grade_level');
    const topicSelect = document.getElementById('topic_id');
    const currentTopicId = '<?= esc(old('topic_id') ?? '') ?>';

    function loadTopics() {
        const subjectId = subjectSelect.value;
        const gradeLevel = gradeSelect.value;
        
        topicSelect.innerHTML = '<option value="">-- Chọn Chủ đề --</option>';
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
                    topicSelect.appendChild(option);
                });
            });
    }

    subjectSelect.addEventListener('change', loadTopics);
    gradeSelect.addEventListener('change', loadTopics);

    if (subjectSelect.value && gradeSelect.value) {
        loadTopics();
    }
});
</script>
<?= $this->endSection() ?>
