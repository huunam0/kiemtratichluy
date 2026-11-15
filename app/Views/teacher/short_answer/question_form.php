<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= $question ? 'Chỉnh sửa Câu hỏi Trả lời ngắn' : 'Tạo Câu hỏi Trả lời ngắn Mới' ?></h1>
            <p class="text-gray-500 text-sm mt-0.5">Mỗi câu hỏi có thể cấu hình nhiều đáp án đúng, mỗi đáp án có tùy chọn phân biệt chữ hoa/thường và dấu cách.</p>
        </div>
        <div class="d-flex items-center gap-2">
            <?php if ($question): ?>
                <a href="<?= base_url('teacher/short-answer/questions/preview/' . $question['id']) ?>" class="btn btn-outline-indigo border border-indigo-600 text-indigo-600 hover:bg-indigo-50 font-semibold rounded-xl">
                    <i class="fa-solid fa-eye me-1"></i> Xem thử
                </a>
            <?php endif; ?>
            <a href="<?= base_url('teacher/short-answer/questions') ?>" class="btn btn-outline-secondary rounded-xl">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- MAIN QUESTION FORM -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="POST">
            <input type="hidden" name="action" value="update_main">
            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <label class="form-label fw-semibold text-gray-700">Tên câu hỏi (để gợi nhớ & quản lý) <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control rounded-xl" value="<?= esc($question['title'] ?? old('title')) ?>" placeholder="VD: Phép cộng ba với năm, Tên thủ đô Việt Nam..." required>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold text-gray-700">
                        <i class="fa-solid fa-layer-group text-purple-600 me-1"></i> Mã nhóm tương đương (equivalent_id)
                    </label>
                    <input type="number" name="equivalent_id" class="form-control rounded-xl" value="<?= esc($question['equivalent_id'] ?? old('equivalent_id')) ?>" placeholder="Để trống = nhận ID câu này">
                    <div class="form-text text-xs text-gray-500">Các câu cùng mã nhóm sẽ chỉ chọn ngẫu nhiên 1 câu cho mỗi học sinh.</div>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold text-gray-700">Môn học <span class="text-danger">*</span></label>
                    <select name="subject_id" id="subject_id" required class="form-select rounded-xl">
                        <option value="">-- Chọn Môn --</option>
                        <?php foreach($subjects as $subj): ?>
                            <option value="<?= $subj['id'] ?>" <?= (isset($question['subject_id']) && $question['subject_id'] == $subj['id']) ? 'selected' : '' ?>><?= esc($subj['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold text-gray-700">Khối Lớp <span class="text-danger">*</span></label>
                    <select name="grade_level" id="grade_level" required class="form-select rounded-xl">
                        <option value="">-- Chọn Khối --</option>
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i ?>" <?= isset($question['grade_level']) && $question['grade_level'] == $i ? 'selected' : '' ?>>Khối <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold text-gray-700">Chủ đề <span class="text-danger">*</span></label>
                    <select name="topic_id" id="topic_id" required class="form-select rounded-xl">
                        <option value="">-- Chọn Chủ đề --</option>
                    </select>
                </div>

                <div class="col-12 mt-3">
                    <label class="form-label fw-semibold text-gray-700">Nội dung câu hỏi (hiển thị cho học sinh) <span class="text-danger">*</span></label>
                    <textarea name="question_text" class="form-control rounded-xl" rows="4" placeholder="VD: Ba cộng Năm bằng mấy?" required><?= esc($question['question_text'] ?? old('question_text')) ?></textarea>
                </div>

                <?php if (!$question): ?>
                    <!-- KHI TẠO MỚI: DANH SÁCH ĐÁP ÁN ĐÚNG BAN ĐẦU -->
                    <div class="col-12 mt-4">
                        <div class="d-flex items-center justify-between mb-2">
                            <label class="form-label fw-semibold text-gray-800 mb-0">
                                <i class="fa-solid fa-list-check text-indigo-600 me-1"></i> Các phương án đáp án đúng <span class="text-danger">*</span>
                            </label>
                            <button type="button" class="btn btn-sm btn-outline-indigo rounded-lg" onclick="addAnswerRow()">
                                <i class="fa-solid fa-plus me-1"></i> Thêm đáp án đúng
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mb-3">Học sinh chỉ cần trả lời khớp với 1 trong các phương án đúng này sẽ được tính điểm.</p>

                        <div id="answers-container" class="space-y-3">
                            <div class="answer-row bg-gray-50 border border-gray-200 rounded-xl p-3">
                                <div class="row g-2 align-items-center">
                                    <div class="col-12 col-md-6">
                                        <input type="text" name="answers[]" class="form-control rounded-lg" placeholder="VD: 8" required>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="case_sensitives[0]" value="1" id="case_0">
                                            <label class="form-check-label text-xs font-semibold text-gray-700" for="case_0">Phân biệt Hoa/thường</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="space_sensitives[0]" value="1" id="space_0">
                                            <label class="form-check-label text-xs font-semibold text-gray-700" for="space_0">Phân biệt Dấu cách</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="answer-row bg-gray-50 border border-gray-200 rounded-xl p-3">
                                <div class="row g-2 align-items-center">
                                    <div class="col-12 col-md-6">
                                        <input type="text" name="answers[]" class="form-control rounded-lg" placeholder="VD: Tám">
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="case_sensitives[1]" value="1" id="case_1">
                                            <label class="form-check-label text-xs font-semibold text-gray-700" for="case_1">Phân biệt Hoa/thường</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 d-flex items-center justify-between">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="space_sensitives[1]" value="1" id="space_1">
                                            <label class="form-check-label text-xs font-semibold text-gray-700" for="space_1">Phân biệt Dấu cách</label>
                                        </div>
                                        <button type="button" class="btn btn-sm text-danger" onclick="removeAnswerRow(this)"><i class="fa-solid fa-xmark"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- KHI CHỈNH SỬA: DANH SÁCH ĐÁP ÁN HIỆN CÓ -->
                    <div class="col-12 mt-4">
                        <label class="form-label fw-semibold text-gray-800 mb-2">
                            <i class="fa-solid fa-list-check text-indigo-600 me-1"></i> Danh sách các đáp án đúng hiện có
                        </label>
                        <div class="space-y-3">
                            <?php foreach ($answers as $ans): ?>
                                <div class="bg-gray-50 border border-gray-200 rounded-xl p-3">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-12 col-md-5">
                                            <input type="text" name="existing_answers[<?= $ans['id'] ?>][answer_text]" class="form-control rounded-lg font-semibold" value="<?= esc($ans['answer_text']) ?>" required>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="existing_answers[<?= $ans['id'] ?>][case_sensitive]" value="1" id="exist_case_<?= $ans['id'] ?>" <?= (int)$ans['case_sensitive'] === 1 ? 'checked' : '' ?>>
                                                <label class="form-check-label text-xs text-gray-700" for="exist_case_<?= $ans['id'] ?>">Phân biệt Hoa/thường</label>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="existing_answers[<?= $ans['id'] ?>][space_sensitive]" value="1" id="exist_space_<?= $ans['id'] ?>" <?= (int)$ans['space_sensitive'] === 1 ? 'checked' : '' ?>>
                                                <label class="form-check-label text-xs text-gray-700" for="exist_space_<?= $ans['id'] ?>">Phân biệt Dấu cách</label>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-1 text-end">
                                            <button type="submit" form="del_ans_<?= $ans['id'] ?>" class="btn btn-sm btn-outline-danger rounded-lg" onclick="return confirm('Xác nhận xóa đáp án này?');" title="Xóa đáp án">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="col-12 mt-4 pt-3 border-top d-flex flex-wrap gap-2">
                    <button type="submit" class="btn bg-indigo-600 text-white hover:bg-indigo-700 font-bold rounded-xl px-5 py-2.5">
                        <i class="fa-solid fa-floppy-disk me-1"></i> <?= $question ? 'Cập nhật Câu hỏi' : 'Lưu Câu hỏi' ?>
                    </button>
                    <?php if ($question): ?>
                        <a href="<?= base_url('teacher/short-answer/questions/preview/' . $question['id']) ?>" class="btn btn-outline-indigo border border-indigo-600 text-indigo-600 hover:bg-indigo-50 font-bold rounded-xl px-4 py-2.5">
                            <i class="fa-solid fa-eye me-1"></i> Xem thử
                        </a>
                    <?php endif; ?>
                    <a href="<?= base_url('teacher/short-answer/questions') ?>" class="btn btn-light rounded-xl px-4 py-2.5">Hủy</a>
                </div>
            </div>
        </form>
    </div>

    <?php if ($question): ?>
        <!-- FORM RIÊNG ĐỂ THÊM ĐÁP ÁN ĐÚNG MỚI (KHI EDIT) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-base font-bold text-gray-900 mb-3"><i class="fa-solid fa-plus-circle text-success me-2"></i>Thêm phương án đáp án đúng mới</h3>
            <form method="POST" action="<?= base_url('teacher/short-answer/questions/edit/' . $question['id']) ?>">
                <input type="hidden" name="action" value="add_answer">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-5">
                        <label class="form-label text-xs fw-semibold text-gray-600">Nội dung đáp án</label>
                        <input type="text" name="new_answer_text" class="form-control rounded-xl" placeholder="VD: Tám, VIII, 8.0..." required>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label text-xs fw-semibold text-gray-600 d-block">Phân biệt Hoa/thường</label>
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" name="new_case_sensitive" value="1" id="new_case">
                            <label class="form-check-label text-xs text-gray-700" for="new_case">Có phân biệt</label>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label text-xs fw-semibold text-gray-600 d-block">Phân biệt Dấu cách</label>
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" name="new_space_sensitive" value="1" id="new_space">
                            <label class="form-check-label text-xs text-gray-700" for="new_space">Có phân biệt</label>
                        </div>
                    </div>
                    <div class="col-12 col-md-1 d-flex items-end">
                        <button type="submit" class="btn btn-success rounded-xl font-bold w-100 mt-md-4">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Hidden delete forms for existing answers -->
        <?php foreach ($answers as $ans): ?>
            <form id="del_ans_<?= $ans['id'] ?>" method="POST" action="<?= base_url('teacher/short-answer/questions/delete-answer/' . $ans['id']) ?>" class="d-none">
                <?= csrf_field() ?>
            </form>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
    // AJAX load topics
    const subjectSelect = document.getElementById('subject_id');
    const gradeSelect = document.getElementById('grade_level');
    const topicSelect = document.getElementById('topic_id');
    const currentTopicId = '<?= esc($question['topic_id'] ?? '') ?>';

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

    // Dynamic row addition for new question creation
    let answerIndex = 2;
    function addAnswerRow() {
        const container = document.getElementById('answers-container');
        if (!container) return;

        const idx = answerIndex++;
        const div = document.createElement('div');
        div.className = 'answer-row bg-gray-50 border border-gray-200 rounded-xl p-3';
        div.innerHTML = `
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <input type="text" name="answers[]" class="form-control rounded-lg" placeholder="Nhập đáp án đúng khác..." required>
                </div>
                <div class="col-6 col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="case_sensitives[${idx}]" value="1" id="case_${idx}">
                        <label class="form-check-label text-xs font-semibold text-gray-700" for="case_${idx}">Phân biệt Hoa/thường</label>
                    </div>
                </div>
                <div class="col-6 col-md-3 d-flex items-center justify-between">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="space_sensitives[${idx}]" value="1" id="space_${idx}">
                        <label class="form-check-label text-xs font-semibold text-gray-700" for="space_${idx}">Phân biệt Dấu cách</label>
                    </div>
                    <button type="button" class="btn btn-sm text-danger" onclick="removeAnswerRow(this)"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>
        `;
        container.appendChild(div);
    }

    function removeAnswerRow(btn) {
        const rows = document.querySelectorAll('.answer-row');
        if (rows.length <= 1) {
            alert('Cần có ít nhất 1 đáp án đúng!');
            return;
        }
        btn.closest('.answer-row').remove();
    }
</script>
<?= $this->endSection() ?>