<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto space-y-6 pb-12">
    <!-- TOP HEADER & ACTION BUTTONS -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <div class="d-flex items-center gap-2 flex-wrap">
                <span class="badge bg-indigo-100 text-indigo-800 rounded-pill px-3 py-1 font-mono text-xs font-bold">#<?= $question['id'] ?></span>
                <?php $equivId = !empty($question['equivalent_id']) ? (int)$question['equivalent_id'] : (int)$question['id']; ?>
                <span class="badge bg-purple-100 text-purple-800 rounded-pill px-3 py-1 text-xs font-bold" title="Mã nhóm câu hỏi tương đương">
                    <i class="fa-solid fa-layer-group me-1"></i>Nhóm tương đương #<?= $equivId ?>
                </span>
                <h1 class="text-2xl font-bold text-gray-900 mb-0">Chi Tiết Câu Hỏi Trắc Nghiệm</h1>
            </div>
            <p class="text-gray-500 text-sm mt-0.5">Xem trước giao diện câu hỏi, các phương án lựa chọn và nhóm tương đương.</p>
        </div>

        <!-- ACTION BUTTONS: NHÂN BẢN, SỬA, XÓA, QUAY LẠI -->
        <div class="d-flex flex-wrap items-center gap-2">
            <a href="<?= base_url('teacher/questions/duplicate/' . $question['id']) ?>" 
               class="btn bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl px-3.5 py-2 shadow-sm"
               onclick="return confirm('Bạn có muốn nhân bản câu hỏi này thành câu hỏi mới cùng nhóm tương đương #<?= $equivId ?>?');"
               title="Tạo bản sao giống hệt câu hỏi này cùng nhóm tương đương">
                <i class="fa-solid fa-clone me-1"></i> Nhân bản câu hỏi
            </a>

            <?php if (!empty($isCreator)): ?>
                <a href="<?= base_url('teacher/questions/edit/' . $question['id']) ?>" 
                   class="btn bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl px-3.5 py-2 shadow-sm"
                   title="Chỉnh sửa nội dung hoặc đáp án">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Sửa câu hỏi
                </a>

                <a href="<?= base_url('teacher/questions/delete/' . $question['id']) ?>" 
                   class="btn btn-outline-danger font-bold rounded-xl px-3.5 py-2"
                   onclick="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn câu hỏi này?');"
                   title="Xóa câu hỏi khỏi ngân hàng">
                    <i class="fa-solid fa-trash me-1"></i> Xóa
                </a>
            <?php endif; ?>

            <a href="<?= base_url('teacher/questions') ?>" 
               class="btn btn-light rounded-xl font-semibold px-3.5 py-2 border border-gray-200">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- THÔNG TIN CHUNG CÂU HỎI -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
        <div class="d-flex flex-wrap items-center justify-between pb-3 border-bottom gap-2">
            <div>
                <span class="text-xs uppercase font-bold text-gray-400">Người tạo:</span>
                <span class="text-sm font-semibold text-gray-800 ms-1"><?= esc($question['creator_name'] ?? 'Giáo viên') ?></span>
                <?php if (!empty($isCreator)): ?>
                    <span class="badge bg-indigo-50 text-indigo-700 text-xs ms-1">Tôi</span>
                <?php endif; ?>
            </div>
            <div class="d-flex flex-wrap items-center gap-2">
                <span class="badge bg-indigo-50 text-indigo-700 border border-indigo-200 px-3 py-1.5 rounded-lg text-xs font-semibold">
                    <?= esc($question['subject_name'] ?? 'Chưa phân môn') ?>
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

        <!-- NỘI DUNG CÂU HỎI -->
        <div>
            <label class="text-xs uppercase font-bold text-gray-400 mb-2 block">
                <i class="fa-solid fa-circle-question text-indigo-500 me-1"></i> Nội dung câu hỏi
            </label>
            <div class="border-2 border-indigo-100 bg-indigo-50/20 rounded-2xl p-6 text-gray-900 font-medium text-base leading-relaxed content-render">
                <?= format_question_content($question['content']) ?>
            </div>
        </div>

        <!-- CÁC PHƯƠNG ÁN LỰA CHỌN -->
        <div class="space-y-3 pt-2">
            <label class="text-xs uppercase font-bold text-gray-400 mb-2 block">
                <i class="fa-solid fa-list-check text-indigo-500 me-1"></i> Các phương án lựa chọn
            </label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <?php
                    $options = [
                        'A' => $question['option_a'],
                        'B' => $question['option_b'],
                        'C' => $question['option_c'],
                        'D' => $question['option_d'],
                    ];
                    foreach ($options as $optKey => $optVal):
                        if (empty($optVal) && $optVal !== '0') continue;
                        $isCorrect = (strtoupper($question['correct_option']) === $optKey);
                ?>
                    <div class="p-4 rounded-xl border-2 <?= $isCorrect ? 'border-emerald-500 bg-emerald-50/40 text-emerald-900 shadow-sm' : 'border-gray-200 bg-white text-gray-800' ?> flex items-start gap-3">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm shrink-0 <?= $isCorrect ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600' ?>">
                            <?= $optKey ?>
                        </span>
                        <div class="flex-grow pt-1 text-sm font-medium">
                            <?= format_option_content($optVal) ?>
                        </div>
                        <?php if ($isCorrect): ?>
                            <span class="badge bg-emerald-600 text-white text-xs px-2.5 py-1 rounded-md shrink-0">
                                <i class="fa-solid fa-check me-1"></i> Đúng
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- LỜI GIẢI / GIẢI THÍCH CHI TIẾT -->
        <?php if (!empty(trim((string)$question['explanation']))): ?>
            <div class="pt-3 border-t border-gray-100">
                <label class="text-xs uppercase font-bold text-gray-400 mb-1.5 block">
                    <i class="fa-solid fa-lightbulb text-amber-500 me-1"></i> Lời giải / Giải thích chi tiết
                </label>
                <div class="p-4 bg-amber-50/60 border border-amber-200 rounded-xl text-sm text-gray-800 leading-relaxed">
                    <?= nl2br(esc($question['explanation'])) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- NHÓM CÂU HỎI TƯƠNG ĐƯƠNG -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
        <div class="d-flex flex-wrap items-center justify-between pb-3 border-bottom gap-2">
            <div>
                <h3 class="text-base font-bold text-gray-900 mb-0">
                    <i class="fa-solid fa-layer-group text-purple-600 me-2"></i>Nhóm Câu Hỏi Tương Đương (Mã nhóm: #<?= $equivId ?>)
                </h3>
                <p class="text-xs text-gray-500 mt-0.5 mb-0">
                    Khi đưa nhiều câu hỏi cùng nhóm vào đề thi, hệ thống sẽ tự động bốc ngẫu nhiên <strong>duy nhất 1 câu</strong> trong nhóm này cho mỗi lượt thi.
                </p>
            </div>
            <a href="<?= base_url('teacher/questions/duplicate/' . $question['id']) ?>" 
               class="btn btn-sm btn-outline-secondary rounded-xl font-semibold"
               onclick="return confirm('Bạn có muốn nhân bản câu hỏi này thành câu hỏi mới cùng nhóm tương đương #<?= $equivId ?>?');">
                <i class="fa-solid fa-clone text-purple-600 me-1"></i> Nhân bản thêm câu cùng nhóm
            </a>
        </div>

        <?php if (empty($equivalentQuestions)): ?>
            <div class="p-4 bg-purple-50/50 border border-purple-100 rounded-xl text-xs text-purple-800 d-flex items-center justify-between flex-wrap gap-2">
                <div>
                    <i class="fa-solid fa-circle-info me-1"></i> Hiện tại chưa có câu hỏi nào khác cùng nhóm <strong>#<?= $equivId ?></strong>.
                </div>
                <span class="text-gray-500">Mặc định câu hỏi này hoạt động độc lập. Bấm nút "Nhân bản" phía trên để tạo câu hỏi tương đương.</span>
            </div>
        <?php else: ?>
            <div class="space-y-2">
                <div class="text-xs font-semibold text-gray-600 mb-1">Các câu hỏi khác thuộc cùng nhóm #<?= $equivId ?> (<?= count($equivalentQuestions) ?> câu):</div>
                <?php foreach ($equivalentQuestions as $eqQ): ?>
                    <?php 
                        $isEqCreator = ((int)$eqQ['creator_id'] === (int)$teacher_id);
                        $eqOptCount = 2;
                        if (!empty($eqQ['option_c'])) $eqOptCount++;
                        if (!empty($eqQ['option_d'])) $eqOptCount++;
                    ?>
                    <div class="p-3 bg-gray-50 border border-gray-200 rounded-xl d-flex flex-wrap items-center justify-between gap-3">
                        <div class="flex-grow-1" style="max-width: 600px;">
                            <div class="font-bold text-gray-900 text-sm d-flex items-center gap-1.5">
                                <span class="font-mono text-xs text-purple-700 bg-purple-100 px-2 py-0.5 rounded border border-purple-200">#<?= $eqQ['id'] ?></span>
                                <span class="badge bg-emerald-100 text-emerald-800 text-xs">Đ/A: <?= esc($eqQ['correct_option']) ?></span>
                                <span class="text-xs text-gray-400 font-normal">(<?= $eqOptCount ?> đáp án)</span>
                                <span class="text-xs text-gray-500 font-normal ms-auto">Tạo bởi: <?= esc($eqQ['creator_name'] ?? 'GV') ?> <?= $isEqCreator ? '<span class="text-indigo-600 font-semibold">(Tôi)</span>' : '' ?></span>
                            </div>
                            <div class="text-xs text-gray-600 mt-1.5 line-clamp-2">
                                <?= format_preview_content($eqQ['content']) ?>
                            </div>
                        </div>
                        <div class="d-flex items-center gap-2">
                            <a href="<?= base_url('teacher/questions/preview/' . $eqQ['id']) ?>" class="btn btn-sm btn-outline-indigo rounded-lg text-xs font-semibold px-2.5 py-1">
                                <i class="fa-solid fa-eye me-1"></i> Xem
                            </a>
                            <?php if ($isEqCreator): ?>
                                <a href="<?= base_url('teacher/questions/edit/' . $eqQ['id']) ?>" class="btn btn-sm btn-outline-secondary rounded-lg text-xs font-semibold px-2.5 py-1">
                                    <i class="fa-solid fa-pen me-1"></i> Sửa
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
