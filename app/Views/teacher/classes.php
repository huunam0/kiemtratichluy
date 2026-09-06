<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <!-- Add Class Form -->
    <div class="md:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-20">
            <h3 class="font-bold text-gray-900 text-lg mb-4 flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-indigo-600"></i>
                Tạo Lớp Học Mới
            </h3>

            <form action="<?= base_url('teacher/classes') ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tên Lớp Hoặc Danh Sách Lớp <span class="text-red-500">*</span></label>
                    <textarea name="name" required rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500" placeholder="VD: 10A1 hoặc 10A1, 10A2, 10A3 (ngăn cách bằng phẩy hoặc xuống dòng)"></textarea>
                    <p class="text-xs text-gray-500 mt-1"><i class="fa-solid fa-lightbulb text-amber-500 me-1"></i> Có thể tạo nhiều lớp của cùng 1 khối bằng cách gõ: <code class="bg-gray-100 px-1 py-0.5 rounded text-indigo-700">10A1, 10A2, 10A3</code></p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Khối Lớp</label>
                    <select name="grade_level" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="10">Khối 10</option>
                        <option value="11">Khối 11</option>
                        <option value="12">Khối 12</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Năm Học</label>
                    <input type="text" name="academic_year" value="2025-2026" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500">
                </div>

                <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md transition">
                    Khởi Tạo Lớp Học <i class="fa-solid fa-check ms-1"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- School Shared Classes List -->
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-gray-900 text-lg">Danh sách Lớp học trong Trường</h3>
                    <p class="text-xs text-gray-500 mb-0">Giáo viên trong cùng trường có thể xem và sử dụng chung danh sách này.</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                        <tr>
                            <th class="ps-6">Tên Lớp</th>
                            <th>Khối</th>
                            <th>Năm Học</th>
                            <th>Giáo Viên Tạo</th>
                            <th>Ngày Tạo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <?php foreach ($classes as $cls): ?>
                            <tr>
                                <td class="ps-6 font-bold text-indigo-700 text-base"><?= esc($cls['name']) ?></td>
                                <td><span class="badge bg-gray-100 text-gray-800 font-medium">Khối <?= esc($cls['grade_level']) ?></span></td>
                                <td><?= esc($cls['academic_year']) ?></td>
                                <td class="text-gray-700 font-medium"><?= esc($cls['creator_name'] ?? 'Giáo viên') ?></td>
                                <td class="text-gray-500 text-xs"><?= date('d/m/Y', strtotime($cls['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection() ?>
