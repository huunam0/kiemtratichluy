<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-xl mx-auto text-center py-12 space-y-6">
    <div class="bg-white rounded-3xl p-8 shadow-xl border border-gray-100 space-y-6">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-indigo-50 text-indigo-600 rounded-full text-3xl shadow-inner">
            <i class="fa-solid fa-hourglass-half fa-spin"></i>
        </div>

        <div>
            <h1 class="text-2xl font-black text-gray-900 mb-2">Đang Chờ Giáo Viên Phê Duyệt</h1>
            <p class="text-gray-500 text-sm">Bạn đã tham gia lượt kiểm tra thật điền vào chỗ trống: <strong class="text-gray-800"><?= esc($quiz['title']) ?></strong></p>
        </div>

        <div class="bg-indigo-50/60 rounded-2xl p-4 text-left space-y-2 border border-indigo-100 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">Mã phiên thi:</span><span class="font-mono font-bold text-indigo-900"><?= esc($session['session_code']) ?></span></div>
            <div class="flex justify-between"><span class="text-gray-500">Thời gian làm bài:</span><span class="font-bold text-gray-800"><?= $quiz['time_limit'] ?> phút</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Trạng thái phê duyệt:</span>
                <span id="approvalBadge" class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1 font-semibold">Đang chờ giáo viên duyệt...</span>
            </div>
        </div>

        <p class="text-xs text-gray-400">Trang web sẽ tự động chuyển sang bài làm ngay khi Giáo viên phê duyệt thành công.</p>

        <div>
            <a href="<?= base_url('student/dashboard') ?>" class="btn btn-outline-secondary rounded-xl px-5">
                <i class="fa-solid fa-arrow-left me-1"></i> Rời phòng chờ
            </a>
        </div>
    </div>
</div>

<script>
const partId = <?= $participant['id'] ?>;
const checkUrl = '<?= base_url('student/fill-blank/check-approval/') ?>' + partId;
const examUrl = '<?= base_url('student/fill-blank/exam/') ?>' + partId;

setInterval(function() {
    fetch(checkUrl)
        .then(response => response.json())
        .then(data => {
            if (data.approval_status === 'approved') {
                window.location.href = examUrl;
            } else if (data.approval_status === 'rejected') {
                document.getElementById('approvalBadge').className = 'badge bg-danger-subtle text-danger px-3 py-1';
                document.getElementById('approvalBadge').innerText = 'Giáo viên đã từ chối yêu cầu thi';
            }
        })
        .catch(err => console.error(err));
}, 3000);
</script>
<?= $this->endSection() ?>
