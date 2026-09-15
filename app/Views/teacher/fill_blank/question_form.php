<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= $question ? 'Chỉnh sửa Câu hỏi & Quản lý Biến thể' : 'Tạo Dạng bài Điền chỗ trống Mới' ?></h1>
            <p class="text-gray-500 text-sm mt-0.5">Dữ liệu mã nguồn & câu hỏi trắc nghiệm điền vào chỗ trống.</p>
        </div>
        <a href="<?= base_url('teacher/fill-blank/questions') ?>" class="btn btn-outline-secondary rounded-xl">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <!-- MAIN FORM -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="POST">
            <input type="hidden" name="action" value="update_main">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold text-gray-700">Tên dạng bài / Tiêu đề câu hỏi <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control rounded-xl" value="<?= esc($question['title'] ?? old('title')) ?>" placeholder="VD: Tìm UCLN của 2 số nguyên" required>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label fw-semibold text-gray-700">Môn học</label>
                    <input type="text" name="subject" class="form-control rounded-xl" value="<?= esc($question['subject'] ?? 'Tin học') ?>">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label fw-semibold text-gray-700">Khối lớp</label>
                    <select name="grade_level" class="form-select rounded-xl">
                        <option value="10" <?= (isset($question['grade_level']) && $question['grade_level'] == 10) ? 'selected' : '' ?>>Khối 10</option>
                        <option value="11" <?= (!isset($question['grade_level']) || $question['grade_level'] == 11) ? 'selected' : '' ?>>Khối 11</option>
                        <option value="12" <?= (isset($question['grade_level']) && $question['grade_level'] == 12) ? 'selected' : '' ?>>Khối 12</option>
                    </select>
                </div>

                <?php if (!$question): ?>
                    <!-- Initial Variant Content when creating -->
                    <div class="col-12 mt-4">
                        <label class="form-label fw-semibold text-gray-700">Tên phiên bản đầu tiên (Variant Name)</label>
                        <input type="text" name="variant_name" class="form-control rounded-xl mb-2" value="var_1" placeholder="VD: ucln_1">

                        <label class="form-label fw-semibold text-gray-700">Nội dung mã HTML / BBCode mẫu <span class="text-danger">*</span></label>
                        <textarea name="variant_content" class="form-control font-mono rounded-xl" rows="8" placeholder="Nhập nội dung mã nguồn..." required><?= esc(old('variant_content')) ?></textarea>
                    </div>
                <?php endif; ?>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn bg-indigo-600 text-white hover:bg-indigo-700 font-bold rounded-xl px-5 py-2.5">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Lưu Dạng Bài
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- SYNTAX GUIDE HELPER -->
    <div class="bg-indigo-50/70 border border-indigo-100 rounded-2xl p-5 text-sm text-gray-700">
        <h6 class="fw-bold text-indigo-900 mb-2"><i class="fa-solid fa-lightbulb me-1 text-amber-500"></i> Hướng dẫn Cú pháp Thẻ & Sinh Biến Ngẫu Nhiên</h6>
        <ul class="list-disc ps-5 space-y-1">
            <li><strong>Chỗ trống cần điền:</strong> Bao quanh đáp án bằng thẻ <code>[trong]đáp án đúng[/trong]</code>.</li>
            <li><strong>Sinh biến ngẫu nhiên:</strong> <code>[bien1=a,b,c]</code> chọn ngẫu nhiên một trong các giá trị <code>a, b, c</code> cho biến 1, sau đó hiển thị bằng <code>[bien1]</code>.</li>
            <li><strong>Biểu thức tính toán:</strong> <code>[bthuc [bien1]%[bien2]]</code> tính toán giá trị biểu thức.</li>
            <li><strong>Chọn ngẫu nhiên:</strong> <code>[chon a,b,c]</code> hoặc chọn số <code>[chonso 1-10,15,20]</code>.</li>
        </ul>
    </div>

    <!-- EXISTING VARIANTS LIST (If editing) -->
    <?php if ($question): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
            <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-clone me-2 text-indigo-500"></i>Danh sách Các Phiên bản Biến thể (Variants)</h3>

            <?php if (!empty($variants)): ?>
                <div class="space-y-4">
                    <?php foreach ($variants as $v): ?>
                        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
                            <div class="flex items-center justify-between mb-2">
                                <span class="badge bg-indigo-600 text-white font-mono rounded-lg px-3 py-1"><?= esc($v['variant_name']) ?></span>
                                <form method="POST" onsubmit="return confirm('Bạn có chắc xoá phiên bản này?');" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_variant">
                                    <input type="hidden" name="variant_id" value="<?= $v['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-lg"><i class="fa-solid fa-trash me-1"></i>Xoá phiên bản</button>
                                </form>
                            </div>
                            <pre class="bg-white p-3 border rounded-lg text-xs font-mono overflow-auto max-h-48 text-gray-800 mb-0"><?= esc($v['content_raw']) ?></pre>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-400 text-center py-3">Chưa có phiên bản biến thể nào.</p>
            <?php endif; ?>

            <!-- ADD NEW VARIANT -->
            <hr class="my-4">
            <h4 class="font-bold text-gray-800 text-base mb-3"><i class="fa-solid fa-plus-circle me-1 text-emerald-600"></i>Thêm Phiên bản Biến thể Mới</h4>
            <form method="POST">
                <input type="hidden" name="action" value="add_variant">
                <div class="mb-3">
                    <label class="form-label fw-semibold text-gray-700">Tên phiên bản (VD: ucln_4, max_3...)</label>
                    <input type="text" name="new_variant_name" class="form-control rounded-xl" placeholder="Tự động sinh nếu để trống">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-gray-700">Nội dung mã HTML / BBCode của phiên bản này <span class="text-danger">*</span></label>
                    <textarea name="new_variant_content" class="form-control font-mono rounded-xl" rows="6" placeholder="Nhập mã..." required></textarea>
                </div>
                <button type="submit" class="btn bg-emerald-600 text-white hover:bg-emerald-700 font-bold rounded-xl px-4 py-2">
                    <i class="fa-solid fa-plus me-1"></i> Thêm Biến Thể
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
