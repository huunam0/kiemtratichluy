<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <!-- Add Test Form -->
    <div class="md:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-20">
            <h3 class="font-bold text-gray-900 text-lg mb-2 flex items-center gap-2">
                <i class="fa-solid fa-folder-plus text-indigo-600"></i>
                Tạo Bài Kiểm Tra Tích Luỹ Mới
            </h3>
            <p class="text-xs text-gray-500 mb-4">Bài kiểm tra đóng vai trò làm Kho câu hỏi tích luỹ gán cho Lớp. Muốn reset điểm HK mới, giáo viên chỉ cần tạo Bài kiểm tra mới.</p>

            <form action="<?= base_url('teacher/tests') ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Gán Cho Lớp Học <span class="text-red-500">*</span></label>
                    <select name="class_id" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="">-- Chọn Lớp Học --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?> (Khối <?= esc($c['grade_level']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tên Bài Kiểm Tra Tích Luỹ <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500" placeholder="VD: Tích luỹ Tin 10 - Học Kỳ 1">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Mô tả bài kiểm tra</label>
                    <textarea name="description" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500" placeholder="Mô tả ngắn..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Cho phép học sinh kiểm tra thử (Thi thử)?</label>
                    <div class="flex items-center gap-4 mt-2">
                        <label class="inline-flex items-center cursor-pointer text-sm font-medium text-gray-700">
                            <input type="radio" name="allow_mock" value="1" checked class="text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                            <span class="ms-2 text-emerald-700 font-semibold"><i class="fa-solid fa-circle-check me-1"></i> Có (Cho phép)</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer text-sm font-medium text-gray-700">
                            <input type="radio" name="allow_mock" value="0" class="text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                            <span class="ms-2 text-red-600 font-semibold"><i class="fa-solid fa-circle-xmark me-1"></i> Không (Khóa thi thử)</span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Khi tắt, học sinh sẽ không thể bấm "Thi Thử Ngay" từ kho câu hỏi này.</p>
                </div>

                <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md transition">
                    Tạo Bài Kiểm Tra <i class="fa-solid fa-plus ms-1"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Tests List -->
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="font-bold text-gray-900 text-lg">Danh sách Bài Kiểm Tra Tích Luỹ Theo Lớp</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                        <tr>
                            <th class="ps-6">Tên Bài Tích Luỹ</th>
                            <th>Lớp Học</th>
                            <th>Kho Câu Hỏi</th>
                            <th>Thi Thử</th>
                            <th>Giáo Viên Phụ Trách</th>
                            <th class="text-end pe-6">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <?php if (empty($tests)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-8 text-gray-400">
                                    Chưa có bài kiểm tra tích luỹ nào. Hãy tạo bài đầu tiên!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tests as $t): ?>
                                <tr>
                                    <td class="ps-6">
                                        <div class="font-bold text-gray-900 text-base"><?= esc($t['title']) ?></div>
                                        <div class="text-xs text-gray-500"><?= esc($t['description'] ?? 'Không có mô tả') ?></div>
                                    </td>
                                    <td><span class="badge bg-indigo-100 text-indigo-800 font-semibold"><?= esc($t['class_name']) ?></span></td>
                                    <td>
                                        <span class="badge bg-amber-100 text-amber-800 font-bold px-2.5 py-1">
                                            <i class="fa-solid fa-database me-1"></i> <?= $t['total_questions'] ?> câu trong Pool
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ((int)($t['allow_mock'] ?? 1) === 1): ?>
                                            <span class="badge bg-emerald-100 text-emerald-800 font-semibold px-2.5 py-1">
                                                <i class="fa-solid fa-check me-1"></i> Cho phép
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-red-100 text-red-700 font-semibold px-2.5 py-1">
                                                <i class="fa-solid fa-lock me-1"></i> Đã khóa
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-gray-700 font-medium"><?= esc($t['teacher_name'] ?? 'Giáo viên') ?></td>
                                    <td class="text-end pe-6 space-x-1">
                                        <a href="<?= base_url("teacher/tests/edit/{$t['id']}") ?>" class="btn btn-outline-secondary btn-sm rounded-lg font-semibold me-1" title="Sửa bài kiểm tra">
                                            <i class="fa-solid fa-pen-to-square"></i> Sửa
                                        </a>
                                        <a href="<?= base_url("teacher/tests/pool/{$t['id']}") ?>" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white btn-sm rounded-lg font-bold me-1">
                                            <i class="fa-solid fa-plus-minus me-1"></i> Kho Pool
                                        </a>
                                        <a href="<?= base_url("teacher/tests/accumulated-scores/{$t['id']}") ?>" class="btn btn-warning bg-amber-500 hover:bg-amber-600 text-white btn-sm rounded-lg font-bold">
                                            <i class="fa-solid fa-chart-simple me-1"></i> Điểm
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection() ?>
