<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <!-- Add Topic Form -->
    <div class="md:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-20">
            <h3 class="font-bold text-gray-900 text-lg mb-4 flex items-center gap-2">
                <i class="fa-solid fa-folder-plus text-indigo-600"></i>
                Thêm Chủ Đề Mới
            </h3>

            <form action="<?= base_url('teacher/topics') ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create">
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Môn Học</label>
                    <select name="subject_id" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Chọn Môn Học --</option>
                        <?php foreach ($subjects as $subj): ?>
                            <option value="<?= $subj['id'] ?>"><?= esc($subj['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Khối Lớp</label>
                    <select name="grade_level" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Chọn Khối Lớp --</option>
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i ?>">Lớp <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tên Chủ Đề</label>
                    <input type="text" name="name" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500" placeholder="VD: Hàm số lượng giác">
                </div>

                <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md transition">
                    Lưu Chủ Đề <i class="fa-solid fa-floppy-disk ms-1"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Topics List -->
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h3 class="font-bold text-gray-900 text-lg">Danh sách Chủ Đề</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="table table-hover align-middle mb-0 w-full text-left">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                        <tr>
                            <th class="ps-6 py-3">Môn - Lớp</th>
                            <th class="py-3">Tên Chủ Đề</th>
                            <th class="py-3 text-right pe-6">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <?php foreach ($topics as $t): ?>
                            <tr>
                                <td class="ps-6 font-semibold text-indigo-600">
                                    <?= esc($t['subject_name']) ?> <?= $t['grade_level'] ?>
                                </td>
                                <td class="font-semibold text-gray-900">
                                    <form action="<?= base_url('teacher/topics') ?>" method="POST" class="flex gap-2 items-center m-0">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                        <input type="text" name="name" value="<?= esc($t['name']) ?>" required class="px-2 py-1 border border-gray-200 rounded text-sm w-full max-w-[200px]">
                                        <button type="submit" class="text-indigo-600 hover:text-indigo-800 text-xs font-semibold" title="Cập nhật">Lưu</button>
                                    </form>
                                </td>
                                <td class="text-right pe-6">
                                    <form action="<?= base_url('teacher/topics') ?>" method="POST" class="inline-block m-0" onsubmit="return confirm('Bạn có chắc muốn xóa chủ đề này không? Các câu hỏi thuộc chủ đề này có thể bị mất liên kết!');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                        <button type="submit" class="text-red-500 hover:text-red-700">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if(empty($topics)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-4 text-gray-500">Chưa có chủ đề nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection() ?>
