<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <!-- Add Subject Form -->
    <div class="md:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-20">
            <h3 class="font-bold text-gray-900 text-lg mb-4 flex items-center gap-2">
                <i class="fa-solid fa-square-plus text-indigo-600"></i>
                Thêm Môn Học Mới
            </h3>

            <form action="<?= base_url('admin/subjects') ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tên Môn Học</label>
                    <input type="text" name="name" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500" placeholder="VD: Toán học">
                </div>

                <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md transition">
                    Lưu Môn Học <i class="fa-solid fa-floppy-disk ms-1"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Subjects List -->
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="font-bold text-gray-900 text-lg">Danh sách Môn Học</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="table table-hover align-middle mb-0 w-full text-left">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                        <tr>
                            <th class="ps-6 py-3">ID</th>
                            <th class="py-3">Tên Môn Học</th>
                            <th class="py-3 text-right pe-6">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <?php foreach ($subjects as $s): ?>
                            <tr>
                                <td class="ps-6 font-mono text-gray-500"><?= esc($s['id']) ?></td>
                                <td class="font-semibold text-gray-900">
                                    <form action="<?= base_url('admin/subjects') ?>" method="POST" class="flex gap-2 items-center m-0">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                        <input type="text" name="name" value="<?= esc($s['name']) ?>" required class="px-2 py-1 border border-gray-200 rounded text-sm w-full max-w-[200px]">
                                        <button type="submit" class="text-indigo-600 hover:text-indigo-800 text-xs font-semibold" title="Cập nhật">Lưu</button>
                                    </form>
                                </td>
                                <td class="text-right pe-6">
                                    <form action="<?= base_url('admin/subjects') ?>" method="POST" class="inline-block m-0" onsubmit="return confirm('Bạn có chắc muốn xóa môn học này không?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                        <button type="submit" class="text-red-500 hover:text-red-700">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection() ?>
