<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= $quiz ? 'Chỉnh sửa Đề Kiểm Tra Trả Lời Ngắn' : 'Tạo Đề Kiểm Tra Trả Lời Ngắn Mới' ?></h1>
            <p class="text-gray-500 text-sm mt-0.5">Cấu hình thời gian làm bài, hệ số phạt nộp chậm, gán điểm riêng cho từng câu hỏi.</p>
        </div>
        <a href="<?= base_url('teacher/short-answer/quizzes') ?>" class="btn btn-outline-secondary rounded-xl">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <!-- MAIN SETTINGS FORM -->
    <form method="POST" class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-base font-bold text-gray-900 mb-4 pb-2 border-bottom">
                <i class="fa-solid fa-gear text-indigo-600 me-2"></i>Thông tin chung & Cấu hình bài kiểm tra
            </h3>
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-gray-700">Tên bài kiểm tra <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control rounded-xl" value="<?= esc($quiz['title'] ?? old('title')) ?>" placeholder="VD: Kiểm tra 15 phút - Trả lời ngắn Chương 2" required>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold text-gray-700">Gán cho Lớp học <span class="text-danger">*</span></label>
                    <select name="class_id" class="form-select rounded-xl" required>
                        <option value="">-- Chọn lớp --</option>
                        <?php foreach ($classes as $cls): ?>
                            <option value="<?= $cls['id'] ?>" <?= (isset($quiz['class_id']) && $quiz['class_id'] == $cls['id']) ? 'selected' : '' ?>>
                                <?= esc($cls['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label fw-semibold text-gray-700">Thời gian làm bài (Phút)</label>
                    <input type="number" name="time_limit" class="form-control rounded-xl" value="<?= esc($quiz['time_limit'] ?? 15) ?>" min="1" max="180" required>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-gray-700">Đảo ngẫu nhiên các câu hỏi trong đề?</label>
                    <div class="d-flex items-center gap-4 mt-2">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="shuffle_questions" value="1" <?= (int)old('shuffle_questions', $quiz['shuffle_questions'] ?? 1) === 1 ? 'checked' : '' ?> class="text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                            <span class="ms-2 text-sm font-semibold text-gray-800"><i class="fa-solid fa-shuffle text-success me-1"></i> Có (Mỗi em một thứ tự)</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="shuffle_questions" value="0" <?= (int)old('shuffle_questions', $quiz['shuffle_questions'] ?? 1) === 0 ? 'checked' : '' ?> class="text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                            <span class="ms-2 text-sm font-semibold text-gray-500"><i class="fa-solid fa-arrow-down-1-9 text-gray-400 me-1"></i> Không đảo</span>
                        </label>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-gray-700">
                        Hệ số phạt nộp chậm
                        <i class="fa-regular fa-circle-question text-gray-400 ms-1" title="Điểm phạt = Hệ số × số bạn làm cùng điểm và nộp bài sớm hơn. Mặc định là 0 (không phạt)."></i>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text rounded-start-xl bg-gray-50 text-gray-500">Trừ</span>
                        <input type="number" step="0.01" min="0" max="10" name="penalty_coefficient" class="form-control" value="<?= esc($quiz['penalty_coefficient'] ?? '0.00') ?>" placeholder="0.10">
                        <span class="input-group-text rounded-end-xl bg-gray-50 text-gray-500">điểm / bạn nộp sớm hơn</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 mb-0">VD: Đặt <code>0.1</code>. Học sinh cùng 10đ nhưng nộp sau 2 bạn sẽ bị trừ <code>0.1 × 2 = 0.2đ</code> (còn 9.8đ).</p>
                </div>

                <!-- Cho phép thi thử -->
                <div class="col-12">
                    <div class="p-3 bg-gray-50 border border-gray-100 rounded-xl">
                        <label class="form-label fw-semibold text-gray-800 mb-1">
                            <i class="fa-solid fa-gamepad text-teal-600 me-1"></i> Cho phép học sinh làm bài thử (Thi thử)?
                        </label>
                        <div class="d-flex items-center gap-6 mt-1">
                            <label class="inline-flex items-center cursor-pointer text-sm font-medium text-gray-700">
                                <input type="radio" name="allow_mock" value="1" <?= (int)old('allow_mock', $quiz['allow_mock'] ?? 1) === 1 ? 'checked' : '' ?> class="text-teal-600 focus:ring-teal-500 w-4 h-4">
                                <span class="ms-2 text-emerald-700 font-semibold"><i class="fa-solid fa-circle-check me-1"></i> Có (Cho phép thi thử)</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer text-sm font-medium text-gray-700">
                                <input type="radio" name="allow_mock" value="0" <?= (int)old('allow_mock', $quiz['allow_mock'] ?? 1) === 0 ? 'checked' : '' ?> class="text-teal-600 focus:ring-teal-500 w-4 h-4">
                                <span class="ms-2 text-red-600 font-semibold"><i class="fa-solid fa-circle-xmark me-1"></i> Không (Khóa thi thử)</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!$quiz): ?>
                <!-- KHI TẠO MỚI: CHỌN CÂU HỎI VÀ ĐIỂM -->
                <div class="mt-4 pt-4 border-top">
                    <div class="d-flex items-center justify-between mb-3">
                        <h4 class="text-base font-bold text-gray-900 mb-0">
                            <i class="fa-solid fa-list-ol text-indigo-600 me-2"></i>Chọn câu hỏi đưa vào đề & Gán điểm từng câu
                        </h4>
                        <span class="text-xs text-gray-500">Tích chọn các câu hỏi và nhập số điểm cho mỗi câu</span>
                    </div>

                    <?php if (empty($questions)): ?>
                        <div class="alert alert-warning rounded-xl">
                            <i class="fa-solid fa-circle-exclamation me-1"></i> Chưa có câu hỏi nào trong ngân hàng. Vui lòng tạo câu hỏi trước khi tạo đề!
                        </div>
                    <?php else: ?>
                        <div class="space-y-2">
                            <?php foreach ($questions as $q): ?>
                                <div class="p-3 border border-gray-200 rounded-xl hover:bg-indigo-50/30 transition">
                                    <div class="d-flex flex-wrap items-center justify-between gap-2">
                                        <div class="form-check d-flex items-start gap-2 flex-grow-1">
                                            <input class="form-check-input mt-1" type="checkbox" name="selected_questions[]" value="<?= $q['id'] ?>" id="q_<?= $q['id'] ?>">
                                            <label class="form-check-label text-sm text-gray-800" for="q_<?= $q['id'] ?>">
                                                <div class="font-bold text-gray-900"><?= esc($q['title']) ?></div>
                                                <div class="text-gray-600 text-xs mt-0.5"><?= esc(mb_strimwidth($q['question_text'], 0, 90, '...')) ?></div>
                                                <div class="mt-1">
                                                    <span class="badge bg-gray-100 text-gray-600 text-xs"><?= esc($q['subject_name'] ?? 'Tin học') ?> - <?= esc($q['topic_name'] ?? '') ?></span>
                                                    <span class="badge bg-indigo-50 text-indigo-700 text-xs"><?= $q['answer_count'] ?> đáp án đúng</span>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="d-flex items-center gap-1">
                                            <label class="text-xs fw-semibold text-gray-600">Điểm:</label>
                                            <input type="number" step="any" min="0" name="question_points[<?= $q['id'] ?>]" class="form-control form-control-sm rounded-lg text-center font-bold" value="1.00" style="width: 80px;">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="pt-4 border-top d-flex gap-2">
                <button type="submit" class="btn bg-indigo-600 text-white hover:bg-indigo-700 font-bold rounded-xl px-5 py-2.5">
                    <i class="fa-solid fa-floppy-disk me-1"></i> <?= $quiz ? 'Lưu Cấu Hình Đề' : 'Tạo Đề Kiểm Tra' ?>
                </button>
                <a href="<?= base_url('teacher/short-answer/quizzes') ?>" class="btn btn-light rounded-xl px-4 py-2.5">Quay lại</a>
            </div>
        </div>
    </form>

    <?php if ($quiz): ?>
        <!-- KHI CHỈNH SỬA: QUẢN LÝ DANH SÁCH CÂU HỎI TRONG ĐỀ -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
            <div class="d-flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">
                        <i class="fa-solid fa-list-check text-indigo-600 me-2"></i>Các Câu Hỏi Đang Có Trong Đề (<?= count($quizQuestions) ?> câu)
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">Bạn có thể chỉnh sửa điểm riêng cho từng câu hoặc xóa bớt câu khỏi đề.</p>
                </div>
                <div class="text-end">
                    <?php 
                        $totalPts = array_sum(array_column($quizQuestions, 'points'));
                    ?>
                    <span class="badge bg-indigo-100 text-indigo-800 text-sm px-3 py-1.5 font-bold">
                        Tổng điểm đề: <?= number_format($totalPts, 2) ?>đ
                    </span>
                </div>
            </div>

            <?php if (empty($quizQuestions)): ?>
                <div class="alert alert-warning rounded-xl">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Đề chưa có câu hỏi nào. Hãy thêm câu hỏi từ danh sách bên dưới!
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-gray-50 text-gray-700 text-xs">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Câu hỏi</th>
                                <th>Chủ đề</th>
                                <th style="width: 140px;" class="text-center">Điểm câu này</th>
                                <th style="width: 90px;" class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quizQuestions as $idx => $qq): ?>
                                <tr>
                                    <td class="font-mono text-xs text-gray-500"><?= $idx + 1 ?></td>
                                    <td>
                                        <div class="font-bold text-gray-900"><?= esc($qq['title']) ?></div>
                                        <div class="text-xs text-gray-600 mt-0.5"><?= esc(mb_strimwidth($qq['question_text'], 0, 80, '...')) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-gray-100 text-gray-700 text-xs"><?= esc($qq['topic_name'] ?? '') ?></span>
                                    </td>
                                    <td class="text-center">
                                        <form method="POST" action="<?= base_url("teacher/short-answer/quizzes/update-points/{$quiz['id']}/{$qq['id']}") ?>" class="d-flex items-center justify-center gap-1">
                                            <?= csrf_field() ?>
                                            <input type="number" step="any" min="0" name="points" value="<?= (float)$qq['points'] ?>" class="form-control form-control-sm text-center font-bold rounded-lg" style="width: 75px;">
                                            <button type="submit" class="btn btn-sm btn-outline-primary rounded-lg" title="Lưu điểm mới">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-end">
                                        <form method="POST" action="<?= base_url("teacher/short-answer/quizzes/remove-question/{$quiz['id']}/{$qq['id']}") ?>" onsubmit="return confirm('Xác nhận xóa câu hỏi này khỏi đề?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-lg" title="Xóa khỏi đề">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- THÊM CÂU HỎI MỚI VÀO ĐỀ HIỆN TẠI -->
            <div class="pt-4 border-top">
                <h4 class="text-sm font-bold text-gray-800 mb-3"><i class="fa-solid fa-plus-circle text-success me-1"></i>Thêm câu hỏi từ Ngân hàng vào đề này</h4>
                <form method="POST" action="<?= base_url("teacher/short-answer/quizzes/add-question/{$quiz['id']}") ?>" class="row g-2 align-items-center">
                    <?= csrf_field() ?>
                    <div class="col-12 col-md-8">
                        <select name="question_id" class="form-select rounded-xl" required>
                            <option value="">-- Chọn câu hỏi từ ngân hàng --</option>
                            <?php 
                                $existingQIds = array_column($quizQuestions, 'question_id');
                                foreach ($questions as $q):
                                    if (in_array($q['id'], $existingQIds)) continue;
                            ?>
                                <option value="<?= $q['id'] ?>">
                                    [#<?= $q['id'] ?>] <?= esc($q['title']) ?> (<?= esc($q['topic_name'] ?? 'Tin') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="input-group">
                            <input type="number" step="any" min="0" name="points" value="1.00" class="form-control rounded-xl font-bold text-center" placeholder="Điểm" required>
                            <span class="input-group-text rounded-end-xl bg-gray-50 text-xs">đ</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <button type="submit" class="btn btn-success rounded-xl font-bold w-100">
                            <i class="fa-solid fa-plus me-1"></i> Thêm vào đề
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>