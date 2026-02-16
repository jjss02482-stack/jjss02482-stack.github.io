<?php
// 获取聊天室消息数量，用于在主页显示动态状态
$logFile = 'terminal_logs.json';
$msgCount = 0;
if (file_exists($logFile)) {
    $messages = json_decode(file_get_contents($logFile), true);
    $msgCount = is_array($messages) ? count($messages) : 0;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>0330 | 个人档案</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+SC:wght@700&family=Noto+Sans+SC:wght@300;400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --bg: #ffffff; --text: #000000; --accent: #eeeeee; }
        /* 增加对深色模式的基础支持 */
        @media (prefers-color-scheme: dark) {
            :root { --bg: #000000; --text: #ffffff; --accent: #222222; }
            .side-white { background: #000 !important; }
            .bio-text { color: #ccc !important; }
            .game-desc { background: #111 !important; border-color: #333 !important; }
            .matrix-cell { border-color: #333 !important; }
            .silhouette-white { background: #000 !important; border: 1px solid #fff; }
        }

        body { background-color: var(--bg); color: var(--text); font-family: 'Noto Sans SC', sans-serif; margin: 0; overflow-x: hidden; }

        /* 顶部黑白拼接 */
        .header-split { display: flex; height: 500px; width: 100%; border-bottom: 1px solid var(--accent); }
        .side-black { width: 50%; background: #000; position: relative; display: flex; justify-content: flex-end; align-items: flex-end; }
        .side-white { width: 50%; background: #fff; padding: 60px; display: flex; flex-direction: column; justify-content: center; position: relative; }
        
        .silhouette-white { width: 200px; height: 350px; background: #fff; clip-path: polygon(50% 0%, 80% 10%, 90% 35%, 50% 45%, 10% 35%, 20% 10%); margin-right: -100px; z-index: 10; }
        .silhouette-black { width: 200px; height: 350px; background: #000; clip-path: polygon(50% 0%, 80% 10%, 90% 35%, 50% 45%, 10% 35%, 20% 10%); margin-left: -100px; }

        .name { font-family: 'Noto Serif SC', serif; font-size: 64px; font-weight: 700; margin: 0; letter-spacing: 10px; }
        .birth-info { font-size: 14px; color: #999; margin: 10px 0 25px; letter-spacing: 2px; }
        .timer { font-family: monospace; font-size: 18px; font-weight: bold; border-left: 3px solid var(--text); padding-left: 15px; line-height: 1.5; }

        /* 主内容 */
        .main-content { display: grid; grid-template-columns: 1fr 1fr; padding: 80px; gap: 80px; }
        @media (max-width: 768px) { .main-content { grid-template-columns: 1fr; padding: 40px; } }

        .about-me h3 { font-family: 'Noto Serif SC', serif; font-size: 24px; border-bottom: 2px solid var(--text); padding-bottom: 10px; margin-bottom: 25px; }
        .bio-text { font-size: 15px; line-height: 1.8; color: #333; margin-bottom: 30px; }
        .skill-tag { display: inline-block; padding: 5px 12px; border: 1px solid var(--text); font-size: 12px; margin-right: 10px; margin-bottom: 10px; font-weight: bold; }

        .game-btn { display: block; width: 100%; padding: 15px; margin-bottom: 15px; background: transparent; color: var(--text); border: 1px solid var(--text); font-size: 14px; cursor: pointer; text-align: left; transition: 0.3s; }
        .game-btn:hover { background: var(--text); color: var(--bg); }
        .game-desc { display: none; padding: 15px; background: #f9f9f9; border: 1px dashed #ccc; font-size: 13px; color: #666; margin-bottom: 20px; }

        /* 底部矩阵 */
        .matrix-footer { display: grid; grid-template-columns: repeat(10, 1fr); gap: 12px; padding: 60px 80px; border-top: 1px solid var(--accent); }
        @media (max-width: 768px) { .matrix-footer { grid-template-columns: repeat(5, 1fr); padding: 20px; } }
        
        .matrix-cell { aspect-ratio: 1; border: 1px solid #eee; display: flex; align-items: center; justify-content: center; font-size: 11px; color: #ccc; transition: 0.3s; position: relative; }
        .matrix-cell.active { border-color: var(--text); }
        .matrix-cell.active a { color: var(--text); text-decoration: none; font-weight: 700; text-align: center; font-size: 11px; }
        .matrix-cell.active:hover { background: var(--text); }
        .matrix-cell.active:hover a { color: var(--bg); }
        
        /* 消息泡泡提示 */
        .msg-badge { position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; width: 16px; height: 16px; font-size: 9px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--bg); }
    </style>
</head>
<body>

<section class="header-split">
    <div class="side-black"><div class="silhouette-white"></div></div>
    <div class="side-white">
        <div class="silhouette-black" style="position: absolute; left: -100px; top: 100px;"></div>
        <h1 class="name">0330</h1>
        <div class="birth-info">COMMANDER PROFILE | EST. 2012.03.30</div>
        <div id="live-timer" class="timer">ARCHIVE LOADING...</div>
    </div>
</section>

<section class="main-content">
    <div class="about-me">
        <h3>个人档案 / BIO</h3>
        <div class="bio-text">
            我是 0330。一名专注于技术与策略的研究者。掌握 Python 自动化逻辑，并具备 C++ 基础架构认知。在数字化领域中，我追求极致的效率与精准的判断。
            <div style="margin-top: 15px;">
                <span class="skill-tag">PYTHON</span>
                <span class="skill-tag">C++ (BASICS)</span>
                <span class="skill-tag">STRATEGY</span>
            </div>
        </div>

        <h3>游戏库 / MISSIONS</h3>
        <button class="game-btn" onclick="toggleDesc('ab')">■ 暗区突围 (Arena Breakout)</button>
        <div id="ab" class="game-desc">硬核撤离类射击游戏。在极高风险的环境下进行资源博弈。</div>

        <button class="game-btn" onclick="toggleDesc('kards')">■ KARDS</button>
        <div id="kards" class="game-desc">二战题材卡牌策略。通过宏观层面的兵种克制与前线推进。</div>
    </div>

    <div class="radar-container">
        <canvas id="statChart"></canvas>
    </div>
</section>

<div class="matrix-footer">
    <div class="matrix-cell active" style="background:var(--text);"><a href="#" style="color:var(--bg);">G.R.I.M.<br>官网</a></div>
    
    <div class="matrix-cell active">
        <a href="chat.php">战术<br>终端</a>
        <?php if($msgCount > 0): ?>
            <span class="msg-badge"><?php echo $msgCount; ?></span>
        <?php endif; ?>
    </div>
    
    <?php for($i=3; $i<=20; $i++): ?>
        <div class="matrix-cell"><?php echo sprintf("%02d", $i); ?></div>
    <?php endfor; ?>
</div>

<script>
    // 实时计时器
    function updateAge() {
        const birth = new Date('2012-03-30T00:00:00');
        const now = new Date();
        const diff = now - birth;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        let years = now.getFullYear() - birth.getFullYear();
        if (now.getMonth() < birth.getMonth() || (now.getMonth() === birth.getMonth() && now.getDate() < birth.getDate())) { years--; }
        const timeStr = now.toLocaleTimeString('zh-CN', { hour12: false });
        document.getElementById('live-timer').innerHTML = `存续: ${years}岁 | 第 ${days} 天<br>当前: ${timeStr}`;
    }
    setInterval(updateAge, 1000);
    updateAge();

    function toggleDesc(id) {
        const desc = document.getElementById(id);
        desc.style.display = (desc.style.display === 'block') ? 'none' : 'block';
    }

    // 属性图
    const ctx = document.getElementById('statChart').getContext('2d');
    const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: ['攻击', '防御', '智力', '体力', '经验', '技术'],
            datasets: [{
                data: [0, 1, 7, 3, 5, 7],
                backgroundColor: isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.05)',
                borderColor: isDark ? '#fff' : '#000',
                borderWidth: 1.5,
                pointRadius: 4,
                pointBackgroundColor: isDark ? '#fff' : '#000'
            }]
        },
        options: {
            scales: { r: { grid: { color: isDark ? '#333' : '#eee' }, angleLines: { color: isDark ? '#333' : '#eee' }, pointLabels: { color: isDark ? '#fff' : '#000', font: { size: 14, weight: 'bold' } }, ticks: { display: false }, min: 0, max: 8 } },
            plugins: { legend: { display: false } }
        }
    });
</script>
</body>
</html>
