<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto space-y-6 pb-16">

    <?php if ($select_mode): ?>
        <!-- Step 1: Select Question Count (3 to 6) -->
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8 text-center space-y-6 my-8">
            <div class="w-20 h-20 mx-auto rounded-3xl bg-amber-100 text-amber-600 flex items-center justify-center text-4xl font-bold">
                <i class="fa-solid fa-gamepad"></i>
            </div>

            <div>
                <span class="badge bg-amber-100 text-amber-900 font-bold px-3 py-1 rounded-full text-xs mb-2">Luyện Tập Thi Thử</span>
                <h1 class="text-2xl font-bold text-gray-900 mb-1"><?= esc($test['title']) ?></h1>
                <p class="text-gray-500 text-sm mb-0">Điểm thi thử tự do <strong>không</strong> tính vào bảng điểm tích luỹ.</p>
            </div>

            <div class="p-4 bg-indigo-50 border border-indigo-100 rounded-2xl text-indigo-950 text-left">
                <div class="font-bold text-sm text-indigo-800 mb-1">Vui lòng chọn số lượng câu hỏi cho bài thi thử này:</div>
                <p class="text-xs text-indigo-600 mb-0">Hệ thống sẽ bốc ngẫu nhiên từ kho câu hỏi và xáo trộn đáp án.</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <a href="<?= base_url("student/mock-test/{$test['id']}?num_questions=3") ?>" class="btn btn-outline-indigo border-2 hover:bg-indigo-600 hover:text-white p-4 rounded-2xl text-center no-underline transition shadow-sm">
                    <div class="font-black text-2xl">3 CÂU</div>
                    <div class="text-xs opacity-75 font-normal">Thi thử 3 câu</div>
                </a>

                <a href="<?= base_url("student/mock-test/{$test['id']}?num_questions=4") ?>" class="btn btn-outline-indigo border-2 hover:bg-indigo-600 hover:text-white p-4 rounded-2xl text-center no-underline transition shadow-sm">
                    <div class="font-black text-2xl">4 CÂU</div>
                    <div class="text-xs opacity-75 font-normal">Thi thử 4 câu</div>
                </a>

                <a href="<?= base_url("student/mock-test/{$test['id']}?num_questions=5") ?>" class="btn btn-outline-indigo border-2 hover:bg-indigo-600 hover:text-white p-4 rounded-2xl text-center no-underline transition shadow-sm">
                    <div class="font-black text-2xl">5 CÂU</div>
                    <div class="text-xs opacity-75 font-normal">Thi thử 5 câu</div>
                </a>

                <a href="<?= base_url("student/mock-test/{$test['id']}?num_questions=6") ?>" class="btn btn-outline-indigo border-2 hover:bg-indigo-600 hover:text-white p-4 rounded-2xl text-center no-underline transition shadow-sm">
                    <div class="font-black text-2xl">6 CÂU</div>
                    <div class="text-xs opacity-75 font-normal">Thi thử 6 câu</div>
                </a>
            </div>

            <div class="pt-2">
                <a href="<?= base_url('student/dashboard') ?>" class="btn btn-light rounded-xl px-5 py-2.5 font-medium text-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay lại Dashboard
                </a>
            </div>
        </div>

    <?php else: ?>

        <!-- Step 2: 5-Second Countdown Overlay & Practice Exam Taking -->
        <div id="countdownBanner" class="bg-amber-500 text-white p-6 rounded-3xl shadow-xl text-center space-y-2 border-2 border-amber-300">
            <i class="fa-solid fa-gamepad text-4xl mb-1 animate-bounce"></i>
            <h2 class="text-2xl font-bold">Luyện Tập Kiểm Tra Thử (<?= $num_questions ?> câu)</h2>
            <p class="text-amber-100 text-sm">Điểm thi thử sẽ <strong>KHÔNG</strong> cộng dồn vào bảng điểm tích luỹ.</p>
            <div class="pt-2 text-3xl font-black font-mono">
                Bắt đầu sau: <span id="startCountdown" class="bg-amber-950 px-4 py-1 rounded-2xl text-yellow-300">5</span>s
            </div>
        </div>

        <!-- Exam Container (Hidden until 5s countdown ends) -->
        <div id="examContainer" class="space-y-4 hidden">
            <div class="bg-indigo-900 text-white p-4 rounded-2xl shadow-lg flex items-center justify-between">
                <span class="badge bg-amber-400 text-indigo-950 font-bold px-3 py-1 text-sm">THI THỬ (<?= $num_questions ?> CÂU)</span>
                <div class="font-bold text-sm text-white truncate max-w-xs"><?= esc($test['title']) ?></div>
            </div>

            <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-6 space-y-6">
                <div id="mockContent" class="text-gray-900 text-lg font-semibold">
                    <!-- Content -->
                </div>

                <div id="mockOptions" class="space-y-3">
                    <!-- Dynamic options -->
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <button id="btnMockPrev" onclick="prevMock()" class="btn btn-light rounded-xl px-4 py-3 font-bold" disabled>Câu Trước</button>
                    <button id="btnMockNext" onclick="nextMock()" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-5 py-3 font-bold">Câu Tiếp</button>
                    <button id="btnMockSubmit" onclick="finishMock()" class="btn btn-success bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl px-6 py-3 font-bold hidden">Hoàn Thành Thi Thử</button>
                </div>
            </div>

            <!-- Result Box (Hidden until submit) -->
            <div id="mockResultBox" class="bg-white rounded-3xl p-8 shadow-xl border border-gray-100 text-center space-y-4 hidden">
                <i class="fa-solid fa-trophy text-yellow-400 text-5xl"></i>
                <h2 class="text-2xl font-bold text-gray-900">Kết Quả Thi Thử</h2>
                <div id="mockScoreText" class="text-3xl font-black text-indigo-700"></div>
                <div class="flex justify-center gap-3 pt-2">
                    <a href="<?= base_url("student/mock-test/{$test['id']}") ?>" class="btn btn-outline-indigo font-bold rounded-xl px-5 py-3">Thi Thử Lại</a>
                    <a href="<?= base_url('student/dashboard') ?>" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl px-6 py-3">Về Dashboard</a>
                </div>
            </div>
        </div>

        <script>
        const mockData = <?= $mock_data_json ?>;
        const testId = <?= $test['id'] ?>;
        let mockIndex = 0;
        let mockUserAnswers = {};

        // 5-Second Countdown
        let countdown = 5;
        const timer = setInterval(() => {
            countdown--;
            document.getElementById('startCountdown').innerText = countdown;
            if (countdown <= 0) {
                clearInterval(timer);
                document.getElementById('countdownBanner').classList.add('hidden');
                document.getElementById('examContainer').classList.remove('hidden');
                renderMockQuestion();
            }
        }, 1000);

        function renderMockQuestion() {
            const q = mockData[mockIndex];
            document.getElementById('mockContent').innerHTML = `<strong>Câu ${mockIndex + 1} / ${mockData.length}:</strong><br><br>${q.content}`;

            const container = document.getElementById('mockOptions');
            container.innerHTML = '';

            const currentPick = mockUserAnswers[mockIndex] || null;

            q.display_options.forEach(opt => {
                const isSelected = (currentPick === opt.label);

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = `w-full min-h-[60px] text-left p-4 rounded-2xl border-2 transition-all flex items-center gap-4 text-base font-medium ${
                    isSelected ? 'border-indigo-600 bg-indigo-50 text-indigo-950 font-bold' : 'border-gray-200 bg-white text-gray-800'
                }`;

                btn.innerHTML = `
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white ${isSelected ? 'bg-indigo-600' : 'bg-gray-500'}">${opt.label}</span>
                    <div>${opt.content}</div>
                `;

                btn.onclick = () => {
                    mockUserAnswers[mockIndex] = opt.label;
                    renderMockQuestion();
                };

                container.appendChild(btn);
            });

            document.getElementById('btnMockPrev').disabled = (mockIndex === 0);
            if (mockIndex === mockData.length - 1) {
                document.getElementById('btnMockNext').classList.add('hidden');
                document.getElementById('btnMockSubmit').classList.remove('hidden');
            } else {
                document.getElementById('btnMockNext').classList.remove('hidden');
                document.getElementById('btnMockSubmit').classList.add('hidden');
            }
        }

        function prevMock() {
            if (mockIndex > 0) { mockIndex--; renderMockQuestion(); }
        }

        function nextMock() {
            if (mockIndex < mockData.length - 1) { mockIndex++; renderMockQuestion(); }
        }

        function finishMock() {
            let correct = 0;
            mockData.forEach((q, idx) => {
                const pickLabel = mockUserAnswers[idx];
                if (pickLabel && q.option_mapping[pickLabel] === q.correct_option) {
                    correct++;
                }
            });

            const scoreBase10 = ((correct / mockData.length) * 10).toFixed(1);

            document.getElementById('mockScoreText').innerText = `Đúng ${correct} / ${mockData.length} câu (${scoreBase10} điểm)`;
            document.getElementById('mockResultBox').classList.remove('hidden');

            // Save log to mock_test_logs table
            const formData = new FormData();
            formData.append('test_id', testId);
            formData.append('score_correct', correct);
            formData.append('score_total', mockData.length);
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            fetch('<?= base_url("student/submit-mock-test") ?>', {
                method: 'POST',
                body: formData
            });
        }
        </script>
    <?php endif; ?>

</div>
<?= $this->endSection() ?>
