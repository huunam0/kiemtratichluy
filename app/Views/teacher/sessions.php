<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <!-- Start New Session Form -->
    <div class="md:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-20">
            <h3 class="font-bold text-gray-900 text-lg mb-2 flex items-center gap-2">
                <i class="fa-solid fa-play-circle text-amber-500"></i>
                Mở Lượt Thi Thực Tế Trên Lớp
            </h3>
            <p class="text-xs text-gray-500 mb-4">Mở lượt thi trực tiếp để học sinh chọn và tham gia làm bài trên smartphone.</p>

            <form action="<?= base_url('teacher/sessions') ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Chọn Bài Kiểm Tra Tích Luỹ <span class="text-red-500">*</span></label>
                    <select name="test_id" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="">-- Chọn Bài Tích Luỹ --</option>
                        <?php foreach ($tests as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= esc($t['title']) ?> (Lớp <?= esc($t['class_name']) ?> - Pool <?= $t['pool_count'] ?> câu)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tên Lượt Thi <span class="text-red-500">*</span></label>
                    <input type="text" name="session_title" required class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500" placeholder="VD: Lượt 1 - Kiểm tra bài 5 (15 phút)">
                </div>

                <div class="p-3 bg-indigo-50 border border-indigo-100 rounded-xl text-xs text-indigo-900">
                    <i class="fa-solid fa-circle-info me-1 text-indigo-600"></i> Học sinh sẽ tự lựa chọn số câu làm (từ 3 đến 6 câu) khi bắt đầu lượt thi của mình.
                </div>

                <button type="submit" class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl shadow-md transition">
                    Khởi Tạo Lượt Thi <i class="fa-solid fa-arrow-right ms-1"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Sessions List -->
    <div class="md:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="font-bold text-gray-900 text-lg">Danh Sách Lượt Thi Đã Tạo</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                        <tr>
                            <th class="ps-6">Tên Lượt Thi / Mã PIN</th>
                            <th>Bài Tích Luỹ (Lớp)</th>
                            <th>Số Câu / Thời Gian</th>
                            <th>Trạng Thái</th>
                            <th class="text-end pe-6">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <?php if (empty($sessions)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-8 text-gray-400">
                                    Chưa có lượt thi nào được mở. Hãy tạo lượt thi đầu tiên!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sessions as $s): ?>
                                <tr>
                                    <td class="ps-6">
                                        <div class="font-bold text-gray-900"><?= esc($s['session_title']) ?></div>
                                        <code class="bg-gray-100 px-2 py-0.5 rounded text-indigo-600 font-bold text-xs"><?= esc($s['session_code']) ?></code>
                                    </td>
                                    <td>
                                        <div class="font-medium text-gray-800"><?= esc($s['test_title']) ?></div>
                                        <span class="badge bg-indigo-50 text-indigo-700 text-xs">Lớp <?= esc($s['class_name']) ?></span>
                                    </td>
                                    <td><?= $s['num_questions'] ?> câu (<?= round($s['total_duration_sec']/60, 1) ?> phút)</td>
                                    <td>
                                        <?php if ($s['status'] === 'waiting'): ?>
                                            <span class="badge bg-amber-100 text-amber-800 font-semibold animate-pulse">Chờ học sinh vào</span>
                                        <?php elseif ($s['status'] === 'in_progress'): ?>
                                            <span class="badge bg-emerald-600 text-white font-bold">Đang làm bài</span>
                                        <?php else: ?>
                                            <span class="badge bg-gray-200 text-gray-700 font-medium">Đã kết thúc</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-6 space-x-1">
                                        <a href="<?= base_url("teacher/sessions/control/{$s['id']}") ?>" class="btn btn-warning btn-sm font-bold rounded-lg px-2.5 py-1 me-1">
                                            <i class="fa-solid fa-gears me-1"></i> Điều Khiển
                                        </a>
                                        <?php if ($s['status'] !== 'completed'): ?>
                                            <button onclick="closeSession(<?= $s['id'] ?>)" class="btn btn-outline-danger btn-sm font-bold rounded-lg px-2.5 py-1 me-1">
                                                <i class="fa-solid fa-lock me-1"></i> Đóng
                                            </button>
                                        <?php endif; ?>
                                        <a href="<?= base_url("teacher/sessions/leaderboard/{$s['id']}") ?>" target="_blank" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white btn-sm rounded-lg font-bold px-2.5 py-1">
                                            <i class="fa-solid fa-tv me-1"></i> Bảng Chiếu
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

<script>
function closeSession(sessionId) {
    if (!confirm('Bạn có chắc muốn ĐÓNG lượt thi thật này? Học sinh sẽ không thể tham gia nữa.')) return;

    const formData = new FormData();
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch('<?= base_url("teacher/sessions/end/") ?>' + sessionId, {
        method: 'POST',
        body: formData
    }).then(() => location.reload());
}
</script>
<?= $this->endSection() ?>
