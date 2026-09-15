<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<style>
.chotrong {
    background-color: #fef08a;
    color: #854d0e;
    font-weight: bold;
    padding: 2px 8px;
    border-radius: 4px;
    border: 1px dashed #ca8a04;
    cursor: pointer;
    user-select: none;
    transition: all 0.2s ease;
}
.chotrong:hover {
    background-color: #fde047;
    border-style: solid;
}
.chotrong.filled {
    background-color: #dcfce7;
    color: #166534;
    border-color: #22c55e;
    border-style: solid;
}
pre {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px;
}
</style>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- TOP BAR WITH TIMER & PROGRESS -->
    <div class="sticky-top bg-white border border-gray-200 rounded-2xl shadow-md p-4 flex flex-wrap items-center justify-between gap-4" style="top: 1rem; z-index: 1020;">
        <div>
            <span class="badge bg-indigo-100 text-indigo-800 me-2"><?= $is_mock ? 'THI THỬ TỰ DO' : 'BÀI KIỂM TRA THẬT' ?></span>
            <h2 class="text-lg font-extrabold text-gray-900 inline-block mb-0"><?= esc($quiz['title']) ?></h2>
        </div>
        <div class="flex items-center space-x-4">
            <div class="text-sm font-semibold text-gray-700">
                <i class="fa-solid fa-pen me-1 text-amber-500"></i>
                Còn trống: <span id="remainingCount" class="font-bold text-amber-600">0</span> / <span id="totalBlanksCount">0</span>
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
        ? base_url('student/fill-blank/submit-mock/' . $quiz['id']) 
        : base_url('student/fill-blank/submit-exam/' . $participant['id']);

    $correctAnswersMap = [];
    ?>

    <form id="quizForm" method="POST" action="<?= $submitUrl ?>">
        <div class="space-y-6">
            <?php foreach ($questionsData as $qIdx => $qItem): ?>
                <?php $correctAnswersMap[$qIdx] = $qItem['answers']; ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-3">
                    <div class="flex items-center justify-between border-b pb-2">
                        <h3 class="font-bold text-gray-900 text-base">
                            <span class="badge bg-indigo-600 text-white rounded-lg me-2">Câu <?= $qIdx + 1 ?></span>
                            <?= esc($qItem['question']['title']) ?>
                        </h3>
                        <span class="text-xs text-gray-400 font-mono">Phiên bản: <?= esc($qItem['variant_name']) ?></span>
                    </div>
                    <div class="question-content leading-relaxed text-gray-800" data-qindex="<?= $qIdx ?>">
                        <?= $qItem['html'] ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <input type="hidden" name="correct_answers_json" value="<?= esc(json_encode($correctAnswersMap)) ?>">

        <div class="mt-8 text-center">
            <button type="button" id="btnSubmit" onclick="confirmSubmit();" class="btn bg-emerald-600 text-white hover:bg-emerald-700 font-extrabold text-lg rounded-2xl px-8 py-3 shadow-lg">
                <i class="fa-solid fa-paper-plane me-2"></i> NỘP BÀI THI
            </button>
        </div>
    </form>
</div>

<script>
let totalBlanks = 0;
let filledBlanks = 0;
let remainingTime = <?= ((int)($quiz['time_limit'] ?: 15)) * 60 ?>;
const placeholderStr = "__________";

document.addEventListener("DOMContentLoaded", function() {
    // Setup blank elements
    const questions = document.querySelectorAll('.question-content');
    questions.forEach((qEl) => {
        const qIdx = qEl.getAttribute('data-qindex');
        const blanks = qEl.querySelectorAll('.chotrong');
        blanks.forEach((bEl) => {
            totalBlanks++;
            const bIdx = bEl.getAttribute('data-index');

            // Create hidden input inside form for submission
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `answers[${qIdx}][${bIdx}]`;
            input.id = `input_${qIdx}_${bIdx}`;
            input.value = '';
            document.getElementById('quizForm').appendChild(input);

            // Add click listener
            bEl.addEventListener('click', function() {
                const currentText = bEl.innerText === placeholderStr ? '' : bEl.innerText;
                const userVal = prompt(`Điền câu trả lời cho [Chỗ trống thứ ${parseInt(bIdx) + 1}]:`, currentText);
                
                if (userVal !== null) {
                    const trimmed = userVal.trim();
                    if (trimmed !== "" && trimmed !== placeholderStr) {
                        bEl.innerText = trimmed;
                        bEl.classList.add('filled');
                        input.value = trimmed;
                    } else {
                        bEl.innerText = placeholderStr;
                        bEl.classList.remove('filled');
                        input.value = '';
                    }
                    updateCounts();
                }
            });
        });
    });

    document.getElementById('totalBlanksCount').innerText = totalBlanks;
    updateCounts();

    // Start Timer
    startTimer();
});

function updateCounts() {
    let filled = 0;
    document.querySelectorAll('.chotrong').forEach(el => {
        if (el.classList.contains('filled')) filled++;
    });
    filledBlanks = filled;
    document.getElementById('remainingCount').innerText = (totalBlanks - filledBlanks);
}

function startTimer() {
    const timerInterval = setInterval(function() {
        const phut = Math.floor(remainingTime / 60);
        const giay = remainingTime % 60;

        document.getElementById('timerPhut').innerText = phut < 10 ? '0' + phut : phut;
        document.getElementById('timerGiay').innerText = giay < 10 ? '0' + giay : giay;

        if (remainingTime <= 0) {
            clearInterval(timerInterval);
            alert('Đã hết thời gian làm bài! Hệ thống sẽ tự động nộp bài.');
            document.getElementById('quizForm').submit();
        }
        remainingTime--;
    }, 1000);
}

function confirmSubmit() {
    if (filledBlanks < totalBlanks) {
        if (!confirm(`Bạn còn ${totalBlanks - filledBlanks} ô chưa điền! Bạn có chắc chắn muốn nộp bài ngay không?`)) {
            return;
        }
    } else {
        if (!confirm('Bạn đã điền đầy đủ các ô trống. Xác nhận nộp bài?')) {
            return;
        }
    }
    document.getElementById('quizForm').submit();
}
</script>
<?= $this->endSection() ?>
