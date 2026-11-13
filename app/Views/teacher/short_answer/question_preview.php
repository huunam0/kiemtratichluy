<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto space-y-6">
    <!-- TOP HEADER & ACTION BUTTONS -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <div class="d-flex items-center gap-2">
                <span class="badge bg-indigo-100 text-indigo-800 rounded-pill px-3 py-1 font-mono text-xs font-bold">#<?= $question['id'] ?></span>
                <?php $equivId = !empty($question['equivalent_id']) ? (int)$question['equivalent_id'] : (int)$question['id']; ?>
                <span class="badge bg-purple-100 text-purple-800 rounded-pill px-3 py-1 text-xs font-bold" title="Mã nhóm câu hỏi tương đương">
                    <i class="fa-solid fa-layer-group me-1"></i>Nhóm tương đương #<?= $equivId ?>
                </span>
                <h1 class="text-2xl font-bold text-gray-900 mb-0">Xem Thử Câu Hỏi</h1>
            </div>
            <p class="text-gray-500 text-sm mt-0.5">Xem trước giao diện câu hỏi, các đáp án đúng và thao tác nhanh.</p>
        </div>

        <!-- ACTION BUTTONS: SỬA, NHÂN BẢN, XÓA, QUAY LẠI -->
        <div class="d-flex flex-wrap items-center gap-2">
            <a href="<?= base_url('teacher/short-answer/questions/duplicate/' . $question['id']) ?>" 
               class="btn bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl px-3.5 py-2 shadow-sm"
               onclick="return confirm('Bạn có muốn nhân bản câu hỏi này thành câu hỏi mới với tên kèm đuôi \"(x2)\"?');"
               title="Tạo bản sao giống hệt câu hỏi này kèm đuôi (x2)">
                <i class="fa-solid fa-clone me-1"></i> Nhân bản câu hỏi
            </a>

            <a href="<?= base_url('teacher/short-answer/questions/edit/' . $question['id']) ?>" 
               class="btn bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl px-3.5 py-2 shadow-sm"
               title="Chỉnh sửa nội dung hoặc đáp án">
                <i class="fa-solid fa-pen-to-square me-1"></i> Sửa câu hỏi
            </a>

            <a href="<?= base_url('teacher/short-answer/questions/delete/' . $question['id']) ?>" 
               class="btn btn-outline-danger font-bold rounded-xl px-3.5 py-2"
               onclick="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn câu hỏi này?');"
               title="Xóa câu hỏi khỏi ngân hàng">
                <i class="fa-solid fa-trash me-1"></i> Xóa
            </a>

            <a href="<?= base_url('teacher/short-answer/questions') ?>" 
               class="btn btn-light rounded-xl font-semibold px-3.5 py-2 border border-gray-200">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- THÔNG TIN CHUNG CÂU HỎI -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
        <div class="d-flex flex-wrap items-center justify-between pb-3 border-bottom gap-2">
            <div>
                <span class="text-xs uppercase font-bold text-gray-400">Tiêu đề câu hỏi</span>
                <h2 class="text-xl font-bold text-gray-900 mt-0.5 mb-0"><?= esc($question['title']) ?></h2>
            </div>
            <div class="d-flex flex-wrap items-center gap-2">
                <span class="badge bg-indigo-50 text-indigo-700 border border-indigo-200 px-3 py-1.5 rounded-lg text-xs font-semibold">
                    <?= esc($question['subject_name'] ?? 'Tin học') ?>
                </span>
                <span class="badge bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-semibold">
                    Khối <?= esc($question['grade_level'] ?? 11) ?>
                </span>
                <?php if (!empty($question['topic_name'])): ?>
                    <span class="badge bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-semibold">
                        Chủ đề: <?= esc($question['topic_name']) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- PHẦN XEM THỬ GIAO DIỆN HỌC SINH -->
        <div>
            <div class="d-flex items-center justify-between mb-2">
                <label class="text-xs uppercase font-bold text-gray-400">
                    <i class="fa-solid fa-desktop text-indigo-500 me-1"></i> Giao diện hiển thị cho học sinh
                </label>
                <span class="text-xs text-gray-400 font-mono">Xem trước & Kiểm tra thử</span>
            </div>

            <div class="border-2 border-indigo-100 bg-indigo-50/20 rounded-2xl p-6 space-y-4">
                <div class="text-gray-900 font-medium text-base leading-relaxed">
                    <?= nl2br(esc($question['question_text'])) ?>
                </div>

                <div>
                    <label class="form-label text-xs fw-semibold text-gray-600 mb-1">
                        <i class="fa-solid fa-keyboard me-1 text-indigo-500"></i> Thử gõ câu trả lời vào đây:
                    </label>
                    <div class="input-group">
                        <input type="text" id="testAnswerInput" class="form-control form-control-lg rounded-start-xl font-medium" placeholder="Gõ thử câu trả lời để kiểm tra so khớp...">
                        <button type="button" class="btn bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-end-xl px-4" onclick="testCheckAnswer()">
                            <i class="fa-solid fa-vial-circle-check me-1"></i> So khớp thử
                        </button>
                    </div>
                    <div id="testAnswerResult" class="mt-2 text-sm font-semibold d-none"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- DANH SÁCH CÁC ĐÁP ÁN ĐÚNG ĐƯỢC CHẤP NHẬN -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
        <div class="d-flex items-center justify-between pb-3 border-bottom">
            <div>
                <h3 class="text-base font-bold text-gray-900 mb-0">
                    <i class="fa-solid fa-check-double text-success me-2"></i>Các Phương Án Đáp Án Đúng (<?= count($answers) ?> đáp án)
                </h3>
                <p class="text-xs text-gray-500 mt-0.5 mb-0">Học sinh trả lời khớp với bất kỳ phương án nào dưới đây đều được tính điểm.</p>
            </div>
            <a href="<?= base_url('teacher/short-answer/questions/edit/' . $question['id']) ?>" class="btn btn-sm btn-outline-indigo rounded-xl font-semibold">
                <i class="fa-solid fa-plus me-1"></i> Thêm / Sửa đáp án
            </a>
        </div>

        <div class="space-y-3">
            <?php foreach ($answers as $idx => $ans): ?>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div class="d-flex items-center gap-3">
                        <span class="badge bg-emerald-600 text-white rounded-lg px-2.5 py-1 text-xs font-bold">#<?= $idx + 1 ?></span>
                        <div>
                            <span class="text-xs text-gray-400 font-semibold uppercase block">Đáp án:</span>
                            <div class="text-base font-bold text-gray-900 font-mono"><?= esc($ans['answer_text']) ?></div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap items-center gap-2">
                        <?php if ((int)$ans['case_sensitive'] === 1): ?>
                            <span class="badge bg-purple-50 text-purple-700 border border-purple-200 px-3 py-1.5 rounded-lg text-xs font-semibold">
                                <i class="fa-solid fa-font me-1"></i> Có phân biệt Hoa/thường
                            </span>
                        <?php else: ?>
                            <span class="badge bg-gray-100 text-gray-500 px-3 py-1.5 rounded-lg text-xs">
                                <i class="fa-solid fa-font me-1 text-gray-400"></i> Bỏ qua Hoa/thường
                            </span>
                        <?php endif; ?>

                        <?php if ((int)$ans['space_sensitive'] === 1): ?>
                            <span class="badge bg-blue-50 text-blue-700 border border-blue-200 px-3 py-1.5 rounded-lg text-xs font-semibold">
                                <i class="fa-solid fa-arrows-left-right me-1"></i> Có phân biệt Dấu cách
                            </span>
                        <?php else: ?>
                            <span class="badge bg-gray-100 text-gray-500 px-3 py-1.5 rounded-lg text-xs">
                                <i class="fa-solid fa-arrows-left-right me-1 text-gray-400"></i> Bỏ qua Dấu cách thừa
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- NHÓM CÂU HỎI TƯƠNG ĐƯƠNG -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
        <div class="d-flex flex-wrap items-center justify-between pb-3 border-bottom gap-2">
            <div>
                <h3 class="text-base font-bold text-gray-900 mb-0">
                    <i class="fa-solid fa-layer-group text-purple-600 me-2"></i>Nhóm Câu Hỏi Tương Đương (Mã nhóm: #<?= $equivId ?>)
                </h3>
                <p class="text-xs text-gray-500 mt-0.5 mb-0">
                    Khi đưa nhiều câu hỏi cùng nhóm vào một đề, hệ thống sẽ tự động bốc ngẫu nhiên <strong>duy nhất 1 câu</strong> trong nhóm này cho mỗi học sinh.
                </p>
            </div>
            <a href="<?= base_url('teacher/short-answer/questions/duplicate/' . $question['id']) ?>" 
               class="btn btn-sm btn-outline-secondary rounded-xl font-semibold"
               onclick="return confirm('Bạn có muốn nhân bản câu hỏi này thành câu hỏi mới cùng nhóm tương đương?');">
                <i class="fa-solid fa-clone text-purple-600 me-1"></i> Nhân bản thêm câu cùng nhóm
            </a>
        </div>

        <?php if (empty($equivalentQuestions)): ?>
            <div class="p-3 bg-purple-50/50 border border-purple-100 rounded-xl text-xs text-purple-800 d-flex items-center justify-between">
                <div>
                    <i class="fa-solid fa-circle-info me-1"></i> Hiện tại chưa có câu hỏi nào khác cùng nhóm <strong>#<?= $equivId ?></strong>.
                </div>
                <span class="text-gray-500">Mặc định câu hỏi này hoạt động độc lập.</span>
            </div>
        <?php else: ?>
            <div class="space-y-2">
                <div class="text-xs font-semibold text-gray-600 mb-1">Các câu hỏi khác thuộc cùng nhóm #<?= $equivId ?> (<?= count($equivalentQuestions) ?> câu):</div>
                <?php foreach ($equivalentQuestions as $eqQ): ?>
                    <div class="p-3 bg-gray-50 border border-gray-200 rounded-xl d-flex flex-wrap items-center justify-between gap-3">
                        <div class="flex-grow-1">
                            <div class="font-bold text-gray-900 text-sm">
                                <span class="font-mono text-xs text-purple-700 bg-purple-100 px-2 py-0.5 rounded border border-purple-200 me-1">#<?= $eqQ['id'] ?></span>
                                <?= esc($eqQ['title']) ?>
                            </div>
                            <div class="text-xs text-gray-600 mt-1"><?= esc(mb_strimwidth($eqQ['question_text'], 0, 90, '...')) ?></div>
                        </div>
                        <div class="d-flex items-center gap-2">
                            <a href="<?= base_url('teacher/short-answer/questions/preview/' . $eqQ['id']) ?>" class="btn btn-sm btn-outline-indigo rounded-lg text-xs font-semibold px-2.5 py-1">
                                <i class="fa-solid fa-eye me-1"></i> Xem thử
                            </a>
                            <a href="<?= base_url('teacher/short-answer/questions/edit/' . $eqQ['id']) ?>" class="btn btn-sm btn-outline-secondary rounded-lg text-xs font-semibold px-2.5 py-1">
                                <i class="fa-solid fa-pen me-1"></i> Sửa
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Dữ liệu đáp án để giáo viên thử so khớp ngay trên trình duyệt
    const acceptedAnswers = <?= json_encode($answers) ?>;

    function testCheckAnswer() {
        const input = document.getElementById('testAnswerInput').value;
        const resultEl = document.getElementById('testAnswerResult');
        resultEl.classList.remove('d-none', 'text-success', 'text-danger');

        if (input.trim() === '') {
            resultEl.className = 'mt-2 text-sm font-semibold text-danger';
            resultEl.innerHTML = '<i class="fa-solid fa-circle-exclamation me-1"></i> Vui lòng nhập câu trả lời để thử nghiệm!';
            return;
        }

        let matched = false;
        let matchedWith = null;

        for (const ans of acceptedAnswers) {
            let u = input;
            let c = ans.answer_text;

            // Dấu cách
            if (parseInt(ans.space_sensitive) !== 1) {
                u = u.replace(/\s+/g, ' ').trim();
                c = c.replace(/\s+/g, ' ').trim();
            } else {
                u = u.trim();
                c = c.trim();
            }

            // Hoa/thường
            if (parseInt(ans.case_sensitive) !== 1) {
                u = u.toLowerCase();
                c = c.toLowerCase();
            }

            if (u === c) {
                matched = true;
                matchedWith = ans;
                break;
            }
        }

        if (matched) {
            resultEl.className = 'mt-2 text-sm font-semibold text-success p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl';
            resultEl.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> <strong>CHÍNH XÁC!</strong> Khớp với đáp án đúng: <code>"${matchedWith.answer_text}"</code>`;
        } else {
            resultEl.className = 'mt-2 text-sm font-semibold text-danger p-2.5 bg-red-50 border border-red-200 rounded-xl';
            resultEl.innerHTML = '<i class="fa-solid fa-circle-xmark me-1"></i> <strong>CHƯA ĐÚNG!</strong> Câu trả lời này không khớp với bất kỳ phương án đúng nào ở trên.';
        }
    }

    // Nhấn Enter trong ô input để so khớp
    document.getElementById('testAnswerInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            testCheckAnswer();
        }
    });
</script>
<?= $this->endSection() ?>