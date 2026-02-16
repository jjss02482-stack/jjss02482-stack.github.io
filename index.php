<?php
/**
 * 极简 JSON 公共聊天室 - 无需数据库版
 * 功能：支持深色模式、响应式设计、JSON 持久化存储
 */

$storageFile = 'messages.json';

// 初始化数据文件
if (!file_exists($storageFile)) {
    file_put_contents($storageFile, json_encode([]));
}

// 处理消息发送请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
    $username = htmlspecialchars(mb_substr(trim($_POST['username'] ?? '匿名者'), 0, 15));
    $content = htmlspecialchars(mb_substr(trim($_POST['content'] ?? ''), 0, 200));

    if (!empty($content)) {
        $messages = json_decode(file_get_contents($storageFile), true);
        $newMessage = [
            'id' => time() . rand(100, 999),
            'user' => $username,
            'text' => $content,
            'time' => date('H:i')
        ];
        array_unshift($messages, $newMessage); // 新消息排在最前面
        $messages = array_slice($messages, 0, 50); // 只保留最近50条
        file_put_contents($storageFile, json_encode($messages));
        
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success']);
        exit;
    }
}

// 获取消息列表请求
if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
    header('Content-Type: application/json');
    echo file_get_contents($storageFile);
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP 轻量公共聊天室</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* 自定义深色模式平滑过渡 */
        body { transition: background-color 0.3s ease; }
        .glass { background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); }
        .dark .glass { background: rgba(30, 41, 59, 0.7); }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">

    <nav class="p-4 border-b border-gray-200 dark:border-gray-800 glass sticky top-0 z-10">
        <div class="max-w-2xl mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold bg-gradient-to-r from-blue-500 to-purple-600 bg-clip-text text-transparent">
                Public Chat
            </h1>
            <span class="text-xs bg-green-500 text-white px-2 py-1 rounded-full animate-pulse">Live</span>
        </div>
    </nav>

    <main id="chat-box" class="flex-1 max-w-2xl w-full mx-auto p-4 space-y-4 overflow-y-auto">
        </main>

    <footer class="p-4 glass border-t border-gray-200 dark:border-gray-800 sticky bottom-0">
        <form id="chat-form" class="max-w-2xl mx-auto space-y-3">
            <div class="flex gap-2">
                <input type="text" id="username" placeholder="昵称" class="w-24 p-2 rounded-lg border border-gray-300 dark:bg-gray-800 dark:border-gray-700 outline-none focus:ring-2 focus:ring-blue-500" required>
                <input type="text" id="content" placeholder="输入消息..." class="flex-1 p-2 rounded-lg border border-gray-300 dark:bg-gray-800 dark:border-gray-700 outline-none focus:ring-2 focus:ring-blue-500" required>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-all active:scale-95">发送</button>
            </div>
        </form>
    </footer>

    <script>
        const chatBox = document.getElementById('chat-box');
        const chatForm = document.getElementById('chat-form');

        // 获取并渲染消息
        async function fetchMessages() {
            try {
                const response = await fetch('?action=fetch');
                const data = await response.json();
                chatBox.innerHTML = data.map(msg => `
                    <div class="flex flex-col items-start animate-fadeIn">
                        <div class="flex items-center space-x-2 mb-1">
                            <span class="text-xs font-semibold text-blue-500">${msg.user}</span>
                            <span class="text-[10px] text-gray-400">${msg.time}</span>
                        </div>
                        <div class="bg-white dark:bg-gray-800 p-3 rounded-2xl rounded-tl-none shadow-sm border border-gray-100 dark:border-gray-700 max-w-[85%] break-words">
                            ${msg.text}
                        </div>
                    </div>
                `).join('');
            } catch (e) { console.error("加载失败"); }
        }

        // 提交表单
        chatForm.onsubmit = async (e) => {
            e.preventDefault();
            const formData = new FormData();
            formData.append('action', 'send');
            formData.append('username', document.getElementById('username').value);
            formData.append('content', document.getElementById('content').value);

            await fetch('', { method: 'POST', body: formData });
            document.getElementById('content').value = '';
            fetchMessages();
        };

        // 自动刷新
        fetchMessages();
        setInterval(fetchMessages, 3000);
    </script>
</body>
</html>
