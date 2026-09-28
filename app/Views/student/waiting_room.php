<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-md mx-auto my-12 text-center">
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8 space-y-6">

        <div id="iconBox" class="w-20 h-20 mx-auto rounded-3xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-4xl font-bold animate-pulse">
            <i class="fa-solid fa-hourglass-half"></i>
        </div>

        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-1">Phòng Chờ Phê Duyệt</h1>
            <p class="text-gray-500 text-sm">Lượt thi: <span class="font-semibold text-indigo-600"><?= esc($session['session_title']) ?></span></p>
        </div>

        <!-- Waiting Alert -->
        <div id="statusAlert" class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-amber-900 text-sm">
            <i class="fa-solid fa-spinner fa-spin text-amber-600 me-2"></i>
            Bạn đã bấm Tham Gia. Vui lòng chờ Giáo viên phê duyệt trên bảng điều khiển...
        </div>

        <!-- Student Question Count Choice (Rendered after teacher approval) -->
        <div id="questionChoiceBox" class="hidden space-y-4 pt-2">
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-950 text-left">
                <div class="font-bold text-base text-emerald-800 mb-1 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-xl"></i>
                    Đã được Giáo viên phê duyệt!
                </div>
                <p class="text-xs text-emerald-700 mb-0">Vui lòng chọn số lượng câu hỏi bạn muốn làm cho lượt thi này (Từ 3 đến 6 câu):</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <a href="<?= base_url("student/exam/{$participant['id']}?num_questions=3") ?>" class="btn btn-outline-indigo border-2 hover:bg-indigo-600 hover:text-white p-3.5 rounded-2xl text-center no-underline transition">
                    <div class="font-black text-xl">3 CÂU</div>
                    <div class="text-xs opacity-75 font-normal">1.5 Phút (90s)</div>
                </a>

                <a href="<?= base_url("student/exam/{$participant['id']}?num_questions=4") ?>" class="btn btn-outline-indigo border-2 hover:bg-indigo-600 hover:text-white p-3.5 rounded-2xl text-center no-underline transition">
                    <div class="font-black text-xl">4 CÂU</div>
                    <div class="text-xs opacity-75 font-normal">2.0 Phút (120s)</div>
                </a>

                <a href="<?= base_url("student/exam/{$participant['id']}?num_questions=5") ?>" class="btn btn-outline-indigo border-2 hover:bg-indigo-600 hover:text-white p-3.5 rounded-2xl text-center no-underline transition">
                    <div class="font-black text-xl">5 CÂU</div>
                    <div class="text-xs opacity-75 font-normal">2.5 Phút (150s)</div>
                </a>

                <a href="<?= base_url("student/exam/{$participant['id']}?num_questions=6") ?>" class="btn btn-outline-indigo border-2 hover:bg-indigo-600 hover:text-white p-3.5 rounded-2xl text-center no-underline transition">
                    <div class="font-black text-xl">6 CÂU</div>
                    <div class="text-xs opacity-75 font-normal">3.0 Phút (180s)</div>
                </a>
            </div>
        </div>

        <!-- Cancel / Withdraw Request Box -->
        <div id="cancelRequestBox" class="pt-2 border-t border-gray-100 space-y-2">
            <p class="text-xs text-gray-500 mb-2">Nếu bạn bấm nhầm hoặc muốn đổi ý, bạn có thể rút lại yêu cầu:</p>
            <div class="flex items-center justify-center gap-3">
                <a href="<?= base_url('student/dashboard') ?>" class="btn btn-outline-secondary rounded-xl px-4 py-2 text-sm font-semibold">
                    <i class="fa-solid fa-arrow-left me-1"></i> Về Bảng Điều Khiển
                </a>
                <a href="<?= base_url("student/cancel-session/{$participant['id']}") ?>" 
                   class="btn btn-outline-danger hover:bg-red-600 hover:text-white rounded-xl px-4 py-2 text-sm font-bold shadow-sm transition"
                   onclick="return confirm('Bạn có chắc chắn muốn hủy / rút lại yêu cầu tham gia lượt thi này không?');">
                    <i class="fa-solid fa-xmark me-1"></i> Hủy Yêu Cầu Vào Thi
                </a>
            </div>
        </div>

        <div class="text-xs text-gray-400">
            <i class="fa-solid fa-wifi me-1"></i> Tự động kết nối thời gian thực với máy chủ.
        </div>
    </div>
</div>

<script>
const participantId = <?= $participant['id'] ?>;

function checkStatus() {
    fetch('<?= base_url("student/check-approval/") ?>' + participantId)
        .then(res => res.json())
        .then(data => {
            if (data.session_status === 'completed' || data.session_status === 'cancelled') {
                alert('Lượt thi đã được Giáo viên đóng.');
                window.location.href = '<?= base_url("student/dashboard") ?>';
                return;
            }

            if (data.approval_status === 'approved') {
                document.getElementById('statusAlert').classList.add('hidden');
                document.getElementById('cancelRequestBox').classList.add('hidden');
                document.getElementById('questionChoiceBox').classList.remove('hidden');
                document.getElementById('iconBox').className = 'w-20 h-20 mx-auto rounded-3xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-4xl font-bold';
                document.getElementById('iconBox').innerHTML = '<i class="fa-solid fa-list-check"></i>';
            } else if (data.approval_status === 'absent') {
                alert('Bạn đã bị Giáo viên đánh vắng trong lượt thi này.');
                window.location.href = '<?= base_url("student/dashboard") ?>';
            } else if (data.approval_status === 'rejected') {
                alert('Giáo viên đã từ chối cho bạn vào thi lượt này.');
                window.location.href = '<?= base_url("student/dashboard") ?>';
            }
        })
        .catch(err => console.error('Approval check error:', err));
}

setInterval(checkStatus, 2000);
checkStatus();
</script>
<?= $this->endSection() ?>
