<?php
// ============================================
// AstroNode AI — Agentic Follow-up Chat
// File: chat.php
// ============================================
session_start();
require_once 'config.php';
require_once 'includes.db.php';

// Clear chat
if (isset($_GET['clear'])) {
    $_SESSION['chat_history'] = [];
    header('Location: chat.php');
    exit;
}

if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat — AstroNode AI</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body { display:flex; flex-direction:column; height:100vh; overflow:hidden; }
        .site-header { flex-shrink:0; }

        .chat-layout {
            flex:1; display:flex;
            max-width:1000px; width:100%;
            margin:0 auto; padding:0 20px;
            gap:20px; overflow:hidden;
            padding-top:20px; padding-bottom:0;
        }

        /* Sidebar */
        .chat-sidebar {
            width:220px; flex-shrink:0;
            display:flex; flex-direction:column;
            gap:12px;
        }
        .sidebar-card {
            background:var(--bg-card);
            border:1px solid var(--border);
            border-radius:14px;
            padding:16px;
        }
        .sidebar-title { font-size:11px; text-transform:uppercase; letter-spacing:1px; color:var(--text-muted); margin-bottom:12px; }
        .quick-btn {
            width:100%; text-align:left;
            background:rgba(255,255,255,0.03);
            border:1px solid rgba(255,255,255,0.07);
            border-radius:8px; padding:8px 12px;
            color:var(--text-dim); font-family:var(--font);
            font-size:12px; cursor:pointer;
            transition:all .2s; margin-bottom:6px;
            display:block;
        }
        .quick-btn:hover { border-color:var(--violet); color:var(--violet-lt); background:rgba(124,58,237,0.08); }
        .clear-btn {
            width:100%; padding:8px;
            background:rgba(239,68,68,0.08);
            border:1px solid rgba(239,68,68,0.2);
            border-radius:8px; color:#fca5a5;
            font-family:var(--font); font-size:12px;
            cursor:pointer; transition:all .2s;
        }
        .clear-btn:hover { background:rgba(239,68,68,0.15); }

        /* Chat Main */
        .chat-main {
            flex:1; display:flex;
            flex-direction:column;
            background:var(--bg-card);
            border:1px solid var(--border);
            border-radius:18px 18px 0 0;
            overflow:hidden;
        }
        .chat-topbar {
            padding:14px 20px;
            border-bottom:1px solid rgba(255,255,255,0.05);
            display:flex; align-items:center; gap:10px;
            flex-shrink:0;
        }
        .chat-topbar-icon { font-size:20px; }
        .chat-topbar-title { font-size:14px; font-weight:600; }
        .chat-topbar-sub { font-size:11px; color:var(--text-muted); font-family:var(--mono); }
        .model-dot { width:7px; height:7px; border-radius:50%; background:var(--emerald); margin-left:auto; box-shadow:0 0 6px var(--emerald); animation:pulse-live 2s infinite; }

        /* Messages */
        .chat-messages {
            flex:1; overflow-y:auto;
            padding:20px; display:flex;
            flex-direction:column; gap:16px;
        }
        .chat-messages::-webkit-scrollbar { width:4px; }
        .chat-messages::-webkit-scrollbar-thumb { background:#334155; border-radius:2px; }

        .msg { display:flex; gap:10px; animation:fadeUp .3s ease; }
        .msg.user { flex-direction:row-reverse; }

        .msg-avatar {
            width:32px; height:32px; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            font-size:14px; flex-shrink:0;
            background:linear-gradient(135deg, var(--violet), #06b6d4);
        }
        .msg.user .msg-avatar { background:rgba(124,58,237,0.3); border:1px solid rgba(124,58,237,0.4); }

        .msg-bubble {
            max-width:75%;
            padding:12px 16px;
            border-radius:16px;
            font-size:14px; line-height:1.7;
        }
        .msg.ai .msg-bubble {
            background:rgba(255,255,255,0.04);
            border:1px solid rgba(255,255,255,0.08);
            color:var(--text-dim);
            border-radius:4px 16px 16px 16px;
        }
        .msg.user .msg-bubble {
            background:rgba(124,58,237,0.2);
            border:1px solid rgba(124,58,237,0.3);
            color:var(--violet-lt);
            border-radius:16px 4px 16px 16px;
            text-align:right;
        }
        .msg-time { font-size:10px; color:var(--text-muted); margin-top:5px; font-family:var(--mono); }

        /* Context badge */
        .context-badge {
            display:inline-flex; align-items:center; gap:5px;
            font-size:10px; padding:3px 10px;
            background:rgba(6,182,212,0.1);
            border:1px solid rgba(6,182,212,0.2);
            border-radius:20px; color:var(--cyan);
            margin-bottom:8px;
        }

        /* Typing */
        .typing-msg { display:flex; gap:10px; }
        .typing-bubble {
            background:rgba(255,255,255,0.04);
            border:1px solid rgba(255,255,255,0.08);
            border-radius:4px 16px 16px 16px;
            padding:12px 16px;
            display:none;
        }
        .typing-dots { display:flex; gap:5px; }
        .typing-dots span {
            width:7px; height:7px; border-radius:50%;
            background:var(--violet);
            animation:bounce 1.2s ease-in-out infinite;
        }
        .typing-dots span:nth-child(2) { animation-delay:.2s; }
        .typing-dots span:nth-child(3) { animation-delay:.4s; }

        /* Input */
        .chat-input-wrap {
            padding:16px 20px;
            border-top:1px solid rgba(255,255,255,0.05);
            display:flex; gap:10px; flex-shrink:0;
            background:var(--bg-card);
        }
        .chat-input {
            flex:1; background:rgba(255,255,255,0.04);
            border:1px solid rgba(255,255,255,0.1);
            border-radius:10px; padding:12px 16px;
            color:var(--text); font-family:var(--font);
            font-size:14px; outline:none; resize:none;
            transition:border-color .2s;
        }
        .chat-input:focus { border-color:var(--violet); }
        .chat-input::placeholder { color:var(--text-muted); }
        .send-btn {
            width:44px; height:44px;
            background:linear-gradient(135deg, var(--violet), #5b21b6);
            border:none; border-radius:10px; cursor:pointer;
            display:flex; align-items:center; justify-content:center;
            transition:all .2s; flex-shrink:0;
        }
        .send-btn:hover { transform:translateY(-1px); box-shadow:0 4px 15px rgba(124,58,237,0.4); }
        .send-btn:disabled { opacity:.4; cursor:not-allowed; transform:none; }

        @media(max-width:700px){ .chat-sidebar{ display:none; } }
    </style>
</head>
<body>
<canvas id="starfield"></canvas>
<div class="nebula nebula-1"></div>
<div class="nebula nebula-2"></div>

<header class="site-header">
    <div class="header-inner">
        <div class="logo">
            <div class="logo-icon">
                <svg viewBox="0 0 36 36" fill="none">
                    <circle cx="18" cy="18" r="7" fill="#7c3aed"/>
                    <ellipse cx="18" cy="18" rx="17" ry="5.5" stroke="#7c3aed" stroke-width="1.5" fill="none" opacity="0.6" transform="rotate(-25 18 18)"/>
                    <circle cx="18" cy="18" r="3" fill="#a78bfa"/>
                </svg>
            </div>
            <div>
                <span class="logo-name">AstroNode <em>AI</em></span>
                <span class="logo-tag">Astronomical Intelligence</span>
            </div>
        </div>
        <nav class="main-nav">
            <a href="index.php" class="nav-link">Query</a>
            <a href="explore.php" class="nav-link">Explore</a>
            <a href="habitability.php" class="nav-link">Habitability</a>
            <a href="chat.php" class="nav-link active">AI Chat</a>
            <a href="datasets.php" class="nav-link">Datasets</a>
        </nav>
        <div class="header-badges">
            <span class="badge badge-ibm">IBM watsonx</span>
            <span class="badge badge-nasa">NASA API</span>
        </div>
    </div>
</header>

<div class="chat-layout">

    <!-- Sidebar -->
    <div class="chat-sidebar">
        <div class="sidebar-card">
            <div class="sidebar-title">💡 Quick Questions</div>
            <button class="quick-btn" onclick="sendQuick(this)">Which exoplanet is most promising for life?</button>
            <button class="quick-btn" onclick="sendQuick(this)">Explain TRAPPIST-1 system simply</button>
            <button class="quick-btn" onclick="sendQuick(this)">What has JWST discovered recently?</button>
            <button class="quick-btn" onclick="sendQuick(this)">How do we detect atmospheres?</button>
            <button class="quick-btn" onclick="sendQuick(this)">What is the habitable zone?</button>
        </div>
        <div class="sidebar-card">
            <div class="sidebar-title">🧠 Context</div>
            <div style="font-size:12px; color:var(--text-muted); line-height:1.7;">
                AI remembers your full conversation. Ask follow-up questions naturally.
            </div>
            <div style="margin-top:10px; font-size:11px; font-family:var(--mono); color:var(--violet-lt);">
                <?= count($_SESSION['chat_history']) ?> messages in memory
            </div>
        </div>
        <div class="sidebar-card">
            <a href="chat.php?clear=1">
                <button class="clear-btn">🗑 Clear Conversation</button>
            </a>
        </div>
    </div>

    <!-- Chat Main -->
    <div class="chat-main">
        <div class="chat-topbar">
            <div class="chat-topbar-icon">🤖</div>
            <div>
                <div class="chat-topbar-title">AstroNode AI Research Copilot</div>
                <div class="chat-topbar-sub">IBM Granite-3-8B · watsonx.ai · Context-aware</div>
            </div>
            <div class="model-dot"></div>
        </div>

        <div class="chat-messages" id="chatMessages">

            <!-- Welcome message -->
            <div class="msg ai">
                <div class="msg-avatar">🔭</div>
                <div>
                    <div class="msg-bubble">
                        Welcome! I'm your AstroNode AI Research Copilot powered by IBM Granite.<br><br>
                        I remember our full conversation, so you can ask follow-up questions naturally.<br><br>
                        Try asking: <em>"Show me Earth-like exoplanets"</em> then follow up with <em>"Which one is most promising?"</em>
                    </div>
                    <div class="msg-time">AstroNode AI · Now</div>
                </div>
            </div>

            <!-- Render session history -->
            <?php foreach ($_SESSION['chat_history'] as $msg): ?>
            <div class="msg <?= $msg['role'] === 'user' ? 'user' : 'ai' ?>">
                <div class="msg-avatar"><?= $msg['role'] === 'user' ? '👤' : '🔭' ?></div>
                <div>
                    <?php if ($msg['role'] === 'assistant' && !empty($msg['context'])): ?>
                    <div class="context-badge">🧠 <?= htmlspecialchars($msg['context']) ?></div>
                    <?php endif; ?>
                    <div class="msg-bubble"><?= nl2br(htmlspecialchars($msg['content'])) ?></div>
                    <div class="msg-time"><?= $msg['time'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Typing indicator -->
            <div class="typing-msg" id="typingIndicator">
                <div class="msg-avatar">🔭</div>
                <div class="typing-bubble" id="typingBubble">
                    <div class="typing-dots">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Input -->
        <div class="chat-input-wrap">
            <textarea
                id="chatInput"
                class="chat-input"
                rows="1"
                placeholder="Ask anything about space, exoplanets, stars..."
            ></textarea>
            <button class="send-btn" id="sendBtn" onclick="sendMessage()">
                <svg viewBox="0 0 20 20" fill="none" width="16" height="16">
                    <path d="M4 10h12M11 5l5 5-5 5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    </div>

</div>

<script src="starfield.js"></script>
<script>
const input   = document.getElementById('chatInput');
const sendBtn = document.getElementById('sendBtn');
const messages = document.getElementById('chatMessages');
const typing  = document.getElementById('typingBubble');

// Auto-resize textarea
input.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});

// Enter to send
input.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

function sendQuick(btn) {
    input.value = btn.textContent.trim();
    sendMessage();
}

function scrollBottom() {
    messages.scrollTop = messages.scrollHeight;
}

function addMessage(role, text, context = '') {
    const now = new Date().toLocaleTimeString('en', {hour:'2-digit', minute:'2-digit'});
    const div = document.createElement('div');
    div.className = `msg ${role} fade-in`;

    const avatar = role === 'user' ? '👤' : '🔭';
    const ctxBadge = (role === 'ai' && context)
        ? `<div class="context-badge">🧠 ${context}</div>` : '';

    div.innerHTML = `
        <div class="msg-avatar">${avatar}</div>
        <div>
            ${ctxBadge}
            <div class="msg-bubble">${text.replace(/\n/g,'<br>')}</div>
            <div class="msg-time">${role === 'user' ? 'You' : 'AstroNode AI'} · ${now}</div>
        </div>
    `;
    messages.insertBefore(div, document.getElementById('typingIndicator'));
    scrollBottom();
}

async function sendMessage() {
    const text = input.value.trim();
    if (!text) return;

    // Add user message
    addMessage('user', text);
    input.value = '';
    input.style.height = 'auto';
    sendBtn.disabled = true;

    // Show typing
    typing.style.display = 'flex';
    scrollBottom();

    try {
        const fd = new FormData();
        fd.append('message', text);

        const res  = await fetch('api.chat.php', { method:'POST', body:fd });
        const data = await res.json();

        typing.style.display = 'none';
        addMessage('ai', data.reply || 'Sorry, I could not process that.', data.context || '');

    } catch(e) {
    typing.style.display = 'none';
    const q = text.toLowerCase();
    let reply = '';

    if (q.includes('habitable zone') || q.includes('goldilocks')) {
        reply = "The habitable zone is the range of orbital distances where liquid water could exist on a planet's surface. For Sun-like stars it spans roughly 0.95–1.37 AU. TRAPPIST-1e, 1f, and 1g all orbit within their star's habitable zone just 39 light years away.";
    } else if (q.includes('trappist')) {
        reply = "The TRAPPIST-1 system hosts 7 Earth-sized planets orbiting an ultracool dwarf star 39 light years away. Three planets — e, f, and g — lie in the habitable zone. JWST has been actively studying their atmospheres since 2022.";
    } else if (q.includes('jwst') || q.includes('james webb')) {
        reply = "JWST has transformed exoplanet science since 2022. Key discoveries include confirmed water vapor on K2-18b, a potential biosignature (dimethyl sulfide), and detailed atmospheric maps of hot Jupiters. Its NIRSpec instrument detects CO₂, CH₄ and H₂O with unprecedented precision.";
    } else if (q.includes('most promising') || q.includes('best candidate')) {
        reply = "Based on current data, TRAPPIST-1e and GJ 12b are the most promising candidates for life. TRAPPIST-1e sits firmly in the habitable zone with near-Earth radius, while GJ 12b discovered in 2024 orbits a quiet M-dwarf just 40 light years away.";
    } else if (q.includes('atmosphere') || q.includes('detect')) {
        reply = "Astronomers detect exoplanet atmospheres using transmission spectroscopy — measuring starlight filtered through a planet's atmosphere during transit. Different molecules absorb specific wavelengths creating a chemical fingerprint. JWST can detect water, CO₂, methane, and biosignatures at hundreds of light years distance.";
    } else if (q.includes('proxima') || q.includes('closest') || q.includes('nearest')) {
        reply = "Proxima Centauri b orbits our nearest stellar neighbor at just 4.24 light years away. It's an Earth-mass planet in the habitable zone, discovered in 2016 via radial velocity. However, Proxima Centauri is an active flare star which may affect habitability.";
    } else if (q.includes('star') || q.includes('sun')) {
        reply = "Our solar neighborhood contains many interesting stars. The three closest are Proxima Centauri (4.24 ly), Alpha Centauri A and B (4.37 ly), and Barnard's Star (5.96 ly). Most nearby stars are cool M-dwarf red dwarfs, which are the most common stellar type in the Milky Way.";
    } else if (q.includes('planet') || q.includes('exoplanet')) {
        reply = "Over 5,700 exoplanets have been confirmed as of 2026. NASA's TESS satellite continues finding new worlds while JWST characterizes their atmospheres. K2-18b remains the most exciting candidate with detected water vapor and possible biosignatures at 124 light years distance.";
    } else {
        reply = "That's a fascinating astronomical question! Our universe contains over 2 trillion galaxies, with the Milky Way alone hosting an estimated 100-400 billion stars. Current NASA and ESA missions including JWST, TESS, and Gaia are revolutionizing our understanding of exoplanets and stellar physics. What specific aspect would you like to explore?";
    }

    addMessage('ai', reply, 'Local Knowledge Base');
}

    sendBtn.disabled = false;
    scrollBottom();
}

scrollBottom();
</script>
</body>
</html>