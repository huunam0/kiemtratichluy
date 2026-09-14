<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-5">

    <!-- ===== HEADER ===== -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Ngân hàng Câu hỏi Toàn hệ thống</h1>
            <p class="text-gray-500 text-sm mt-0.5">PUBLIC — tất cả giáo viên có thể xem &amp; dùng chung, chỉ người tạo mới được sửa/xoá.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= base_url('teacher/questions/import-aiken') ?>" class="btn btn-outline-secondary rounded-xl px-4 py-2 font-semibold">
                <i class="fa-solid fa-file-import me-1 text-indigo-500"></i> Import Aiken
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
                <div class="col-12 col-md-5">
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
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label fw-semibold text-gray-700 small mb-1">
                        <i class="fa-solid fa-book me-1 text-indigo-400"></i>Môn học
                    </label>
                    <select name="subject" class="form-select rounded-xl" onchange="this.form.submit()">
                        <option value="">-- Tất cả môn --</option>
                        <?php
                        // Merge DB subjects with predefined list for completeness
                        $predefined = ['Toán','Ngữ Văn','Vật Lý','Hóa Học','Sinh Học','Lịch Sử','Địa Lý','GDCD','Tiếng Anh','Tin Học','Thể Dục','Công Nghệ','Âm Nhạc','Mỹ Thuật','Chung'];
                        $allSubjects = array_unique(array_merge($subjects ?? [], $predefined));
                        sort($allSubjects);
                        foreach ($allSubjects as $s): ?>
                            <option value="<?= esc($s) ?>" <?= ($filter['subject'] ?? '') === $s ? 'selected' : '' ?>>
                                <?= esc($s) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Grade level -->
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label fw-semibold text-gray-700 small mb-1">
                        <i class="fa-solid fa-layer-group me-1 text-indigo-400"></i>Khối lớp
                    </label>
                    <select name="grade_level" class="form-select rounded-xl" onchange="this.form.submit()">
                        <option value="">-- Tất cả --</option>
                        <?php for ($g = 1; $g <= 12; $g++): ?>
                            <option value="<?= $g ?>" <?= (string)($filter['grade_level'] ?? '') === (string)$g ? 'selected' : '' ?>>
                                Khối <?= $g ?>
                            </option>
                        <?php endfor; ?>
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
            $hasFilter = !empty($filter['subject']) || !empty($filter['grade_level']) || !empty($filter['keyword']);
            if ($hasFilter): ?>
                <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
                    <span class="text-xs text-gray-500 fw-semibold">Đang lọc:</span>
                    <?php if (!empty($filter['keyword'])): ?>
                        <span class="badge bg-indigo-100 text-indigo-700 rounded-pill px-3 py-1.5 text-xs">
                            <i class="fa-solid fa-magnifying-glass me-1"></i><?= esc($filter['keyword']) ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($filter['subject'])): ?>
                        <span class="badge bg-blue-100 text-blue-700 rounded-pill px-3 py-1.5 text-xs">
                            <i class="fa-solid fa-book me-1"></i><?= esc($filter['subject']) ?>
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

        <div class="overflow-x-auto">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                    <tr>
                        <th class="ps-6" style="width: 50px;">ID</th>
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
                            <td colspan="6" class="text-center py-12 text-gray-400">
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
                                <td class="ps-6 font-mono text-gray-400">#<?= $q['id'] ?></td>
                                <td style="max-width: 480px;">
                                    <div class="font-medium text-gray-900 line-clamp-2"><?= $rawContent ?></div>
                                    <div class="mt-1 d-flex gap-1 flex-wrap">
                                        <span class="badge bg-gray-100 text-gray-600 text-xs font-normal"><?= esc($q['subject']) ?></span>
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
    </div>
</div>

<script>
// Submit form on Enter in keyword field
document.getElementById('keywordInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); this.form.submit(); }
});
</script>
<?= $this->endSection() ?>
