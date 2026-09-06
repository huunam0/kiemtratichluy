<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BẢNG ĐIỂM LƯỢT THI - <?= esc($session['session_title']) ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            color: #ffffff;
            min-height: 100vh;
        }
        .gold-border { border: 2px solid #f59e0b; }
        .silver-border { border: 2px solid #9ca3af; }
        .bronze-border { border: 2px solid #d97706; }
    </style>
</head>
<body class="p-6">

    <div class="max-w-6xl mx-auto space-y-6">
        <!-- Top Header for Projector Screen -->
        <div class="text-center space-y-2 py-4 border-b border-indigo-900/60">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-sm font-semibold mb-1">
                <i class="fa-solid fa-trophy text-yellow-400"></i> BẢNG VINH DANH LƯỢT THI THỜI GIAN THỰC
            </div>
            <h1 class="text-3xl sm:text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-yellow-300 via-amber-200 to-yellow-400 tracking-tight">
                <?= esc($session['session_title']) ?>
            </h1>
            <p class="text-gray-400 text-lg">
                Bài kiểm tra: <span class="text-indigo-300 font-semibold"><?= esc($test['title']) ?></span> | 
                Mã Lượt: <code class="bg-indigo-950 px-3 py-1 rounded text-yellow-300 font-mono font-bold"><?= esc($session['session_code']) ?></code>
            </p>
        </div>

        <!-- Leaderboard Podium / Table -->
        <div class="bg-slate-900/80 rounded-3xl border border-indigo-900/50 p-6 shadow-2xl backdrop-blur">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-indigo-300 text-sm uppercase tracking-wider border-b border-indigo-900/80">
                            <th class="py-4 px-6 text-center" style="width: 80px;">HẠNG</th>
                            <th class="py-4 px-6">HỌ VÀ TÊN HỌC SINH</th>
                            <th class="py-4 px-6 text-center">ĐÚNG / TỔNG SỐ CÂU</th>
                            <th class="py-4 px-6 text-right">ĐIỂM SỐ LƯỢT THI</th>
                        </tr>
                    </thead>
                    <tbody id="leaderboardBody" class="divide-y divide-indigo-950/60 text-lg">
                        <?php if (empty($results)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-12 text-gray-500">
                                    <i class="fa-solid fa-spinner fa-spin text-4xl mb-3 text-indigo-500"></i>
                                    <p>Đang chờ học sinh hoàn thành nộp bài...</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($results as $index => $r): ?>
                                <?php 
                                    $rank = $index + 1;
                                    $rowBg = 'hover:bg-indigo-950/40';
                                    $rankBadge = "<span class='text-gray-400 font-bold'>#{$rank}</span>";

                                    if ($rank === 1) {
                                        $rowBg = 'bg-yellow-500/10 gold-border';
                                        $rankBadge = '<i class="fa-solid fa-crown text-yellow-400 text-2xl"></i>';
                                    } elseif ($rank === 2) {
                                        $rowBg = 'bg-slate-700/20 silver-border';
                                        $rankBadge = '<i class="fa-solid fa-medal text-gray-300 text-xl"></i>';
                                    } elseif ($rank === 3) {
                                        $rowBg = 'bg-amber-700/20 bronze-border';
                                        $rankBadge = '<i class="fa-solid fa-award text-amber-500 text-xl"></i>';
                                    }
                                ?>
                                <tr class="transition-colors <?= $rowBg ?>">
                                    <td class="py-4 px-6 text-center font-bold"><?= $rankBadge ?></td>
                                    <td class="py-4 px-6 font-bold text-white text-xl">
                                        <?= esc($r['student_name']) ?>
                                    </td>
                                    <td class="py-4 px-6 text-right font-black text-3xl text-yellow-400">
                                        <?= $r['score_correct'] ?> / <?= $r['score_total'] ?> <span class="text-lg font-bold text-indigo-200">câu đúng</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-center text-xs text-indigo-400">
            <i class="fa-solid fa-circle-dot text-emerald-400 animate-pulse me-1"></i>
            Tự động cập nhật bảng xếp hạng lượt thi mỗi 3 giây
        </div>
    </div>

    <!-- Auto Refresh Leaderboard Script -->
    <script>
    const sessionId = <?= $session['id'] ?>;

    function refreshLeaderboard() {
        fetch('<?= base_url("api/session-leaderboard/") ?>' + sessionId)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.results.length > 0) {
                    let html = '';
                    data.results.forEach((r, idx) => {
                        const rank = idx + 1;
                        let rankIcon = `<span class="text-gray-400 font-bold">#${rank}</span>`;
                        let borderClass = 'hover:bg-indigo-950/40';

                        if (rank === 1) {
                            rankIcon = '<i class="fa-solid fa-crown text-yellow-400 text-2xl"></i>';
                            borderClass = 'bg-yellow-500/10 gold-border';
                        } else if (rank === 2) {
                            rankIcon = '<i class="fa-solid fa-medal text-gray-300 text-xl"></i>';
                            borderClass = 'bg-slate-700/20 silver-border';
                        } else if (rank === 3) {
                            rankIcon = '<i class="fa-solid fa-award text-amber-500 text-xl"></i>';
                            borderClass = 'bg-amber-700/20 bronze-border';
                        }

                        html += `
                            <tr class="transition-colors ${borderClass}">
                                <td class="py-4 px-6 text-center font-bold">${rankIcon}</td>
                                <td class="py-4 px-6 font-bold text-white text-xl">${r.student_name}</td>
                                <td class="py-4 px-6 text-right font-black text-3xl text-yellow-400">
                                    ${r.score_correct} / ${r.score_total} <span class="text-lg font-bold text-indigo-200">câu đúng</span>
                                </td>
                            </tr>
                        `;
                    });
                    document.getElementById('leaderboardBody').innerHTML = html;
                }
            })
            .catch(err => console.error('Leaderboard refresh error:', err));
    }

    setInterval(refreshLeaderboard, 3000);
    </script>
</body>
</html>
