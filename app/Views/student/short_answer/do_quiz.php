<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-3xl mx-auto space-y-6">
    <!-- STICKY TOP BAR WITH TIMER & PROGRESS -->
    <div class="sticky-top bg-white border border-gray-200 rounded-2xl shadow-md p-4 flex flex-wrap items-center justify-between gap-4" style="top: 1rem; z-index: 1020;">
        <div>
            <span class="badge <?= $is_mock ? 'bg-teal-100 text-teal-800' : 'bg-indigo-100 text-indigo-800' ?> me-2">
                <?= $is_mock ? 'LÀM BÀI THỬ' : 'KIỂM TRA CHÍNH THỨC' ?>
            </span>
            <h2 class="text-lg font-extrabold text-gray-900 inline-block mb-0"><?= esc($quiz['title']) ?></h2>
            <div class="text-xs text-gray-500 mt-0.5">Tổng điểm: <strong class="text-indigo-600"><?= number_format($totalPoints, 2) ?>đ</strong> (<?= count($questions) ?> câu hỏi)</div>
        </div>
        <div class="flex items-center space-x-4">
            <div class="text-sm font-semibold text-gray-700">
                <i class="fa-solid fa-pen me-1 text-indigo-600"></i>
                Đã điền: <span id="answeredCount" class="font-bold text-indigo-600">0</span> / <?= count($questions) ?>
            </div>
            <div class="bg-gray-900 text-emerald-400 font-mono font-bold text-xl px-4 py-1.5 rounded-xl shadow-inner flex items-center">
                <i class="fa-regular fa-clock me-2 text-emerald-400"></i>
                <span id="timerPhut">00</span>:<span id="timerGiay">00</span>
            </div>
        </div>
    </div>

    <!-- QUESTIONS FORM -->
    <?php
    $submitUrl = $is_mock 
        ? base_url('student/short-answer/submit-mock/' . $quiz['id']) 
        : base_url('student/short-answer/submit/' . $session['id']);
    ?>

    <form id="quizForm" method="POST" action="<?= $submitUrl ?>" onsubmit="return confirmSubmit();">
        <?= csrf_field() ?>
        <div class="space-y-5">
            <?php foreach ($questions as $idx => $q): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <div class="d-flex items-center justify-between pb-2 border-bottom">
                        <div class="d-flex items-center gap-2">
                            <span class="badge bg-indigo-600 text-white rounded-lg px-2.5 py-1 text-sm font-bold">Câu <?= $idx + 1 ?></span>
                            <span class="badge bg-gray-100 text-gray-700 text-xs font-semibold"><?= (float)$q['points'] ?> điểm</span>
                        </div>
                        <?php if (!empty($q['topic_name'])): ?>
                            <span class="text-xs text-gray-400"><?= esc($q['topic_name']) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- NỘI DUNG CÂU HỎI -->
                    <div class="text-gray-900 font-medium text-base leading-relaxed">
                        <?= nl2br(esc($q['question_text'])) ?>
                    </div>

                    <!-- Ô NHẬP CÂU TRẢ LỜI -->
                    <div>
                        <label class="form-label text-xs fw-semibold text-gray-600 mb-1">
                            <i class="fa-solid fa-keyboard me-1 text-indigo-500"></i> Câu trả lời của bạn:
                        </label>
                        <input type="text" 
                               name="answers[<?= $q['question_id'] ?>]" 
                               class="form-control form-control-lg rounded-xl answer-input font-medium" 
                               placeholder="Nhập câu trả lời ngắn vào đây..." 
                               autocomplete="off" 
                               oninput="updateAnsweredCount()">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-6 mb-8 text-center">
            <button type="submit" id="btnSubmit" class="btn bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-lg rounded-2xl px-8 py-3.5 shadow-lg shadow-indigo-200 transition">
                <i class="fa-solid fa-paper-plane me-2"></i> Nộp Bài Kiểm Tra
            </button>
        </div>
    </form>
</div>

<script>
    // TIMER LOGIC
    let timeLimitMinutes = <?= (int)$timeLimit ?>;
    let totalSeconds = timeLimitMinutes * 60;
    let isSubmitting = false;

    const timerKey = 'sa_timer_<?= $quiz['id'] ?>_<?= $is_mock ? 'mock' : ($session['id'] ?? 'real') ?>';
    let savedTime = sessionStorage.getItem(timerKey);
    if (savedTime && parseInt(savedTime) > 0) {
        totalSeconds = parseInt(savedTime);
    }

    function formatTime(sec) {
        let m = Math.floor(sec / 60);
        let s = sec % 60;
        return {
            m: m < 10 ? '0' + m : m,
            s: s < 10 ? '0' + s : s
        };
    }

    function updateTimerDisplay() {
        let ft = formatTime(totalSeconds);
        document.getElementById('timerPhut').innerText = ft.m;
        document.getElementById('timerGiay').innerText = ft.s;
    }

    updateTimerDisplay();

    const interval = setInterval(() => {
        totalSeconds--;
        sessionStorage.setItem(timerKey, totalSeconds);
        updateTimerDisplay();

        if (totalSeconds <= 0) {
            clearInterval(interval);
            sessionStorage.removeItem(timerKey);
            alert('Hết giờ làm bài! Hệ thống đang tự động nộp bài của bạn.');
            isSubmitting = true;
            document.getElementById('quizForm').submit();
        }
    }, 1000);

    // ANSWER COUNTER
    function updateAnsweredCount() {
        const inputs = document.querySelectorAll('.answer-input');
        let filled = 0;
        inputs.forEach(input => {
            if (input.value.trim() !== '') {
                filled++;
            }
        });
        document.getElementById('answeredCount').innerText = filled;
    }

    function confirmSubmit() {
        if (isSubmitting) return true;
        const total = <?= count($questions) ?>;
        const inputs = document.querySelectorAll('.answer-input');
        let filled = 0;
        inputs.forEach(input => {
            if (input.value.trim() !== '') filled++;
        });

        let msg = 'Bạn có chắc chắn muốn nộp bài?';
        if (filled < total) {
            msg = `Bạn mới trả lời ${filled}/${total} câu hỏi. Bạn có chắc chắn muốn nộp bài ngay?`;
        }
        
        if (confirm(msg)) {
            isSubmitting = true;
            sessionStorage.removeItem(timerKey);
            return true;
        }
        return false;
    }

    updateAnsweredCount();
</script>
<?= $this->endSection() ?>