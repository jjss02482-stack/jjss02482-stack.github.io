<?php
// --- 后端逻辑：数据处理 ---
$storageFile = 'terminal_logs.json';
if (!file_exists($storageFile)) { file_put_contents($storageFile, json_encode([])); }

// 处理发送
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['msg'])) {
    $messages = json_decode(file_get_contents($storageFile), true);
    $newMsg = [
        'u' => htmlspecialchars(mb_substr(trim($_POST['user'] ?? 'GUEST'), 0, 10)),
        'm' => htmlspecialchars(mb_substr(trim($_POST['msg']), 0, 140)),
        't' => date('H:i:s')
    ];
    array_unshift($messages, $newMsg);
    file_put_contents($storageFile, json_encode(array_slice($messages, 0, 30)));
    echo json_encode(['status' => 'ok']); exit;
}
// 处理获取
if (isset($_GET['fetch'])) {
    header('Content-Type: application/json');
    echo file_get_contents($storageFile); exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>0330 | 战术终端</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@300;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg: #ffffff; --text: #000000; --accent: #eeeeee; }
        @media (prefers-color-scheme: dark) { :root { --bg: #000000; --text: #ffffff; --accent: #222222; } }
        
        body { background: var(--bg); color: var(--text); font-family: 'Noto Sans SC', sans-serif; margin: 0; display: flex; flex-direction: column; height: 100vh; }
        
        /* 继承原档风格的头部 */
        header { padding: 30px 40px; border-bottom: 1px solid var(--accent); display: flex; justify-content: space-between; align-items: center; }
        .terminal-title { font-weight: 700; letter-spacing: 5px; font-size: 18px; }
        .back-link { text-decoration: none; color: var(--text); font-size: 12px; border: 1px solid var(--text); padding: 5px 10px; }

        /* 聊天记录区 */
        #logs { flex: 1; overflow-y: auto; padding: 20px 40px; display: flex; flex-direction: column-reverse; }
        .log-entry { margin-bottom: 15px; font-size: 14px; line-height: 1.6; border-left: 2px solid var(--accent); padding-left: 15px; animation: fadeIn 0.3s ease; }
        .log-time { color: #888; font-family: monospace; font-size: 12px; margin-right: 10px; }
        .log-user { font-weight: 700; text-transform: uppercase; margin-right: 10px; color: #555; }
        .dark .log-user { color: #aaa; }

        /* 输入区 */
        .input-area { padding: 30px 40px; border-top: 1px solid var(--accent); display: flex; gap: 10px; }
        input { background: transparent; border: 1px solid var(--text); color: var(--text); padding: 12px; font-size: 14px; outline: none; }
        #user-in { width: 80px; text-align: center; }
        #msg-in { flex: 1; }
        button { background: var(--text); color: var(--bg); border: none; padding: 0 25px; cursor: pointer; font-weight: 700; transition: 0.2s; }
        button:hover { opacity: 0.8; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<header>
    <div class="terminal-title">TACTICAL TERMINAL v1.0</div>
    <a href="index.html" class="back-link">RETURN</a>
</header>

<div id="logs">
    </div>

<form class="input-area" id="chatForm">
    <input type="text" id="user-in" placeholder="UID" required maxlength="10">
    <input type="text" id="msg-in" placeholder="INPUT COMMAND..." required maxlength="140">
    <button type="submit">SEND</button>
</form>

<script>
    const logs = document.getElementById('logs');
    const form = document.getElementById('chatForm');

    // 获取数据
    async function updateLogs() {
        const res = await fetch('?fetch=1');
        const data = await res.json();
        logs.innerHTML = data.map(item => `
            <div class="log-entry">
                <span class="log-time">[${item.t}]</span>
                <span class="log-user">${item.u}</span>
                <span class="log-msg">${item.m}</span>
            </div>
        `).join('');
    }

    // 发送数据
    form.onsubmit = async (e) => {
        e.preventDefault();
        const body = new FormData();
        body.append('user', document.getElementById('user-in').value);
        body.append('msg', document.getElementById('msg-in').value);
        
        await fetch('', { method: 'POST', body });
        document.getElementById('msg-in').value = '';
        updateLogs();
    }

    setInterval(updateLogs, 3000);
    updateLogs();
</script>
</body>
</html>
