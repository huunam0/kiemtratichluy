<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <!-- Add School Form -->
    <div class="md:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-20">
            <h3 class="font-bold text-gray-900 text-lg mb-4 flex items-center gap-2">
                <i class="fa-solid fa-square-plus text-indigo-600"></i>
                Thêm Trường Học Mới
            </h3>

            <form action="<?= base_url('admin/schools') ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tên Trường Học</label>
                    <input type="text" name="name" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500" placeholder="VD: THPT Chuyên Quốc Học">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Mã Trường (Duy nhất)</label>
                    <input type="text" name="code" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 uppercase" placeholder="VD: QUOCHOC">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Địa chỉ</label>
                    <input type="text" name="address" class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500" placeholder="VD: Huế, Thừa Thiên Huế">
                </div>

                <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md transition">
                    Lưu Trường Học <i class="fa-solid fa-floppy-disk ms-1"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Schools List -->
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="font-bold text-gray-900 text-lg">Danh sách Trường Học</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                        <tr>
                            <th class="ps-6">Mã Trường</th>
                            <th>Tên Trường</th>
                            <th>Địa Chỉ</th>
                            <th>Trạng Thái</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <?php foreach ($schools as $s): ?>
                            <tr>
                                <td class="ps-6 font-mono font-bold text-indigo-600"><?= esc($s['code']) ?></td>
                                <td class="font-semibold text-gray-900"><?= esc($s['name']) ?></td>
                                <td class="text-gray-500"><?= esc($s['address'] ?? '-') ?></td>
                                <td><span class="badge bg-emerald-100 text-emerald-800 font-medium">Hoạt động</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection() ?>
