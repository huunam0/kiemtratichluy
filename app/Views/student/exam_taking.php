<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto space-y-4 pb-16">

    <!-- Fixed Header Countdown Timer -->
    <div class="bg-indigo-900 text-white p-4 rounded-2xl shadow-lg flex items-center justify-between sticky top-16 z-40 border border-indigo-700">
        <div>
            <div class="text-xs text-indigo-300 font-semibold uppercase">Lượt thi đang diễn ra</div>
            <div class="font-bold text-sm text-white truncate max-w-[180px] sm:max-w-xs"><?= esc($session['session_title']) ?></div>
        </div>

        <!-- Timer Display -->
        <div class="bg-indigo-950 px-4 py-2 rounded-xl border border-indigo-700 flex items-center gap-2">
            <i class="fa-solid fa-stopwatch text-amber-400 text-xl animate-pulse"></i>
            <span id="timerDisplay" class="font-mono text-2xl font-black text-yellow-300">00:00</span>
        </div>
    </div>

    <!-- Question Navigation Dots / Bar -->
    <div class="bg-white p-3 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between gap-2 overflow-x-auto">
        <span class="text-xs font-bold text-gray-500 uppercase whitespace-nowrap">Câu hỏi:</span>
        <div id="navDotsContainer" class="flex gap-2"></div>
    </div>

    <!-- Main Question Card -->
    <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-6 space-y-6">
        <!-- Question Header -->
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <span id="questionOrderBadge" class="badge bg-indigo-600 text-white font-bold text-base px-3 py-1 rounded-xl">
                Câu 1 / 5
            </span>
            <span class="text-xs text-gray-400 font-medium">Chống gian lận - Đáp án đã xáo trộn</span>
        </div>

        <!-- Question Content -->
        <div id="questionContent" class="text-gray-900 text-lg sm:text-xl font-semibold leading-relaxed overflow-x-auto">
            Đang tải câu hỏi...
        </div>

        <!-- Dynamic Shuffled Option Buttons (Mobile-First Touch Targets) -->
        <div id="options-container" class="space-y-3 pt-2">
            <!-- Dynamically rendered via JS -->
        </div>

        <!-- Navigation Next/Prev and Submit -->
        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
            <button id="btnPrev" type="button" onclick="prevQuestion()" class="btn btn-light rounded-xl px-4 py-3 font-bold text-gray-700 touch-target" disabled>
                <i class="fa-solid fa-chevron-left me-1"></i> Câu Trước
            </button>

            <button id="btnNext" type="button" onclick="nextQuestion()" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-5 py-3 font-bold touch-target">
                Câu Tiếp <i class="fa-solid fa-chevron-right ms-1"></i>
            </button>

            <button id="btnSubmit" type="button" onclick="confirmSubmit()" class="btn btn-success bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl px-6 py-3 font-bold touch-target shadow-md hidden">
                NỘP BÀI <i class="fa-solid fa-paper-plane ms-1"></i>
            </button>
        </div>
    </div>

</div>

<!-- Real-time Exam Execution Script -->
<script>
const questions = <?= $questions_json ?>;
const participantId = <?= $participant['id'] ?>;
let remainingSeconds = <?= $remaining_seconds ?>;
let currentIndex = 0;
let answers = {}; // Maps sqa_id -> selected_option

// Initialize user selected answers if any
questions.forEach(q => {
    if (q.selected_option) {
        answers[q.id] = q.selected_option;
    }
});

// Render Timer
function startTimer() {
    const timerDisplay = document.getElementById('timerDisplay');
    
    const interval = setInterval(() => {
        if (remainingSeconds <= 0) {
            clearInterval(interval);
            timerDisplay.innerText = "00:00";
            alert("Đã hết thời gian làm bài! Hệ thống đang tự động nộp bài thi của bạn.");
            submitExam();
            return;
        }

        const mins = Math.floor(remainingSeconds / 60);
        const secs = remainingSeconds % 60;
        timerDisplay.innerText = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        
        if (remainingSeconds <= 30) {
            timerDisplay.className = "font-mono text-2xl font-black text-red-400 animate-bounce";
        }

        remainingSeconds--;
    }, 1000);
}

// Render Question Nav Dots
function renderNavDots() {
    const container = document.getElementById('navDotsContainer');
    container.innerHTML = '';

    questions.forEach((q, idx) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        const isAnswered = answers[q.id] ? true : false;
        const isCurrent = (idx === currentIndex);

        btn.className = `w-9 h-9 rounded-xl font-bold text-sm flex items-center justify-center transition-all ${
            isCurrent 
                ? 'bg-indigo-600 text-white ring-2 ring-indigo-400 shadow-md' 
                : (isAnswered ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-gray-100 text-gray-600')
        }`;
        btn.innerText = idx + 1;
        btn.onclick = () => jumpToQuestion(idx);
        container.appendChild(btn);
    });
}

// Render Question Content & Options (Dynamic 2, 3, or 4 options)
function renderQuestion() {
    const q = questions[currentIndex];

    document.getElementById('questionOrderBadge').innerText = `Câu ${currentIndex + 1} / ${questions.length}`;
    document.getElementById('questionContent').innerHTML = q.content;

    const optionsContainer = document.getElementById('options-container');
    optionsContainer.innerHTML = '';

    const currentSelected = answers[q.id] || null;

    // Render ONLY available options for this question
    q.display_options.forEach(opt => {
        const isSelected = (currentSelected === opt.label);

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = `btn-option w-full min-h-[64px] text-left p-4 rounded-2xl border-2 transition-all flex items-center gap-4 text-base font-medium select-none touch-manipulation cursor-pointer ${
            isSelected 
                ? 'border-indigo-600 bg-indigo-50/90 text-indigo-950 shadow-md ring-2 ring-indigo-500' 
                : 'border-gray-200 bg-white text-gray-800 active:bg-gray-100 hover:border-gray-300'
        }`;

        btn.innerHTML = `
            <span class="badge-label w-11 h-11 rounded-xl flex items-center justify-center font-bold text-lg text-white transition-colors shrink-0 ${
                isSelected ? 'bg-indigo-600 shadow-inner' : 'bg-gray-500'
            }">${opt.label}</span>
            <div class="option-text flex-1 overflow-x-auto break-words">${opt.content}</div>
        `;

        btn.onclick = () => selectOption(q.id, opt.label);
        optionsContainer.appendChild(btn);
    });

    // Update Controls
    document.getElementById('btnPrev').disabled = (currentIndex === 0);
    
    if (currentIndex === questions.length - 1) {
        document.getElementById('btnNext').classList.add('hidden');
        document.getElementById('btnSubmit').classList.remove('hidden');
    } else {
        document.getElementById('btnNext').classList.remove('hidden');
        document.getElementById('btnSubmit').classList.add('hidden');
    }

    renderNavDots();
}

function selectOption(sqaId, optionLabel) {
    answers[sqaId] = optionLabel;
    renderQuestion();

    // Auto-save answer to server via AJAX
    const formData = new FormData();
    formData.append('sqa_id', sqaId);
    formData.append('selected_option', optionLabel);
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

    fetch('<?= base_url("student/save-answer") ?>', {
        method: 'POST',
        body: formData
    });
}

function prevQuestion() {
    if (currentIndex > 0) {
        currentIndex--;
        renderQuestion();
    }
}

function nextQuestion() {
    if (currentIndex < questions.length - 1) {
        currentIndex++;
        renderQuestion();
    }
}

function jumpToQuestion(index) {
    currentIndex = index;
    renderQuestion();
}

function confirmSubmit() {
    const answeredCount = Object.keys(answers).length;
    const totalCount = questions.length;

    let msg = `Bạn đã làm ${answeredCount} / ${totalCount} câu. Bạn có chắc chắn muốn NỘP BÀI?`;
    if (answeredCount < totalCount) {
        msg = `Chú ý: Bạn còn ${totalCount - answeredCount} câu chưa trả lời. Bạn có chắc muốn NỘP BÀI?`;
    }

    if (confirm(msg)) {
        submitExam();
    }
}

function submitExam() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?= base_url("student/submit-exam/") ?>' + participantId;

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '<?= csrf_token() ?>';
    csrf.value = '<?= csrf_hash() ?>';
    form.appendChild(csrf);

    document.body.appendChild(form);
    form.submit();
}

// Start Timer & Exam UI
startTimer();
renderQuestion();
</script>
<?= $this->endSection() ?>
