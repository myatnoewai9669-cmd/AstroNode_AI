<?php
// ============================================
// AstroNode AI — NASA APOD Page
// File: apod.php
// ============================================
require_once 'config.php';

// --- Fetch NASA APOD ---
function getAPOD(): array {
    $url = 'https://api.nasa.gov/planetary/apod?api_key=' . NASA_API_KEY;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code === 200) {
        $data = json_decode($res, true);
        if (!empty($data['url'])) return $data;
    }

    // Fallback data
    return [
        'title'       => 'Pillars of Creation — JWST NIRCam',
        'date'        => date('Y-m-d'),
        'explanation' => 'The James Webb Space Telescope captured this breathtaking infrared view of the Pillars of Creation in the Eagle Nebula (M16), located 6,500 light-years away. The towering columns of gas and dust are star-forming regions where new stars are being born. JWST\'s NIRCam reveals thousands of previously hidden stars and fine structures in the nebula\'s dusty pillars.',
        'url'         => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/Pillars_of_creation_2014_HST_WFC3-UVIS_full-res_denoised.jpg/800px-Pillars_of_creation_2014_HST_WFC3-UVIS_full-res_denoised.jpg',
        'media_type'  => 'image',
        'copyright'   => 'NASA/ESA/JWST',
        'hdurl'       => '',
    ];
}

$apod = getAPOD();
$isImage = ($apod['media_type'] ?? 'image') === 'image';

// --- Past APOD dates (last 7 days) ---
$pastDates = [];
for ($i = 1; $i <= 7; $i++) {
    $pastDates[] = date('Y-m-d', strtotime("-{$i} days"));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NASA APOD — AstroNode AI</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        /* Hero */
        .apod-hero {
            position: relative;
            width: 100%;
            min-height: 520px;
            border-radius: 24px;
            overflow: hidden;
            margin-bottom: 28px;
            border: 1px solid var(--border);
        }
        .apod-img {
            width: 100%; height: 520px;
            object-fit: cover;
            display: block;
            transition: transform 8s ease;
        }
        .apod-hero:hover .apod-img { transform: scale(1.04); }

        .apod-video-wrap {
            width: 100%; height: 520px;
        }
        .apod-video-wrap iframe {
            width: 100%; height: 100%;
            border: none;
        }

        /* Overlay */
        .apod-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to top,
                rgba(3,5,15,0.97) 0%,
                rgba(3,5,15,0.5) 50%,
                rgba(3,5,15,0.1) 100%
            );
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 36px;
        }
        .apod-badge {
            display: inline-flex; align-items: center; gap: 7px;
            background: rgba(239,68,68,0.15);
            border: 1px solid rgba(239,68,68,0.3);
            color: #fca5a5;
            font-size: 11px; font-weight: 600;
            padding: 4px 12px; border-radius: 20px;
            text-transform: uppercase; letter-spacing: 1px;
            margin-bottom: 14px; width: fit-content;
        }
        .apod-date-badge {
            display: inline-flex; align-items: center; gap: 7px;
            background: rgba(124,58,237,0.15);
            border: 1px solid rgba(124,58,237,0.3);
            color: var(--violet-lt);
            font-size: 11px; font-family: var(--mono);
            padding: 4px 12px; border-radius: 20px;
            margin-bottom: 14px; margin-left: 8px; width: fit-content;
        }
        .apod-title {
            font-size: clamp(22px, 4vw, 38px);
            font-weight: 700;
            letter-spacing: -1px;
            margin-bottom: 12px;
            line-height: 1.2;
            text-shadow: 0 2px 20px rgba(0,0,0,0.8);
        }
        .apod-copyright {
            font-size: 11px;
            color: rgba(255,255,255,0.4);
            font-family: var(--mono);
            margin-bottom: 6px;
        }
        .apod-actions { display: flex; gap: 10px; margin-top: 14px; }
        .apod-btn {
            padding: 9px 18px;
            border-radius: 8px; border: none;
            font-family: var(--font); font-size: 13px;
            font-weight: 600; cursor: pointer;
            transition: all .2s; text-decoration: none;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .apod-btn-primary {
            background: linear-gradient(135deg, var(--violet), #5b21b6);
            color: white;
        }
        .apod-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(124,58,237,0.4); }
        .apod-btn-secondary {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            color: var(--text-dim);
        }
        .apod-btn-secondary:hover { background: rgba(255,255,255,0.12); color: var(--text); }

        /* Explanation card */
        .explain-layout { display: grid; grid-template-columns: 1fr 320px; gap: 20px; margin-bottom: 28px; }

        .explain-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 28px;
        }
        .explain-title { font-size: 14px; font-weight: 600; color: var(--violet-lt); margin-bottom: 14px; }
        .explain-text { font-size: 14px; color: var(--text-dim); line-height: 1.85; }

        /* Date picker card */
        .date-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 24px;
        }
        .date-title { font-size: 13px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px; }

        .date-input-wrap { margin-bottom: 16px; }
        .date-input {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: 10px 14px;
            color: var(--text);
            font-family: var(--mono);
            font-size: 13px;
            outline: none;
        }
        .date-input:focus { border-color: var(--violet); }

        .date-go-btn {
            width: 100%;
            padding: 10px;
            background: linear-gradient(135deg, var(--violet), #5b21b6);
            border: none; border-radius: 8px;
            color: white; font-family: var(--font);
            font-size: 13px; font-weight: 600;
            cursor: pointer; margin-bottom: 16px;
            transition: all .2s;
        }
        .date-go-btn:hover { opacity: .85; }

        .past-label { font-size: 11px; color: var(--text-muted); margin-bottom: 10px; }
        .past-date {
            display: block; width: 100%;
            padding: 8px 12px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 7px;
            color: var(--text-dim); font-family: var(--mono);
            font-size: 12px; text-decoration: none;
            margin-bottom: 6px;
            transition: all .2s;
            cursor: pointer;
        }
        .past-date:hover { border-color: var(--violet); color: var(--violet-lt); background: rgba(124,58,237,0.08); }

        /* Fun facts */
        .facts-row { display: grid; grid-template-columns: repeat(3,1fr); gap: 16px; margin-bottom: 28px; }
        .fact-card {
            background: var(--bg-card);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 14px;
            padding: 20px;
            text-align: center;
        }
        .fact-icon { font-size: 26px; margin-bottom: 10px; }
        .fact-val { font-size: 20px; font-weight: 700; color: var(--violet-lt); font-family: var(--mono); }
        .fact-label { font-size: 11px; color: var(--text-muted); margin-top: 4px; }

        @media(max-width:700px){
            .explain-layout { grid-template-columns: 1fr; }
            .facts-row { grid-template-columns: repeat(2,1fr); }
            .apod-overlay { padding: 20px; }
        }
    </style>
</head>
<body>
<canvas id="starfield"></canvas>
<div class="nebula nebula-1"></div>
<div class="nebula nebula-2"></div>

<!-- Header -->
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
            <a href="chat.php" class="nav-link">AI Chat</a>
            <a href="apod.php" class="nav-link active">APOD</a>
            <a href="datasets.php" class="nav-link">Datasets</a>
            <a href="about.php" class="nav-link">About</a>
        </nav>
        <div class="header-badges">
            <span class="badge badge-ibm">IBM watsonx</span>
            <span class="badge badge-nasa">NASA API</span>
        </div>
    </div>
</header>

<main class="main-content">

    <!-- APOD Hero -->
    <div class="apod-hero">
        <?php if ($isImage): ?>
        <img
            class="apod-img"
            src="<?= htmlspecialchars($apod['url']) ?>"
            alt="<?= htmlspecialchars($apod['title']) ?>"
            onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/Pillars_of_creation_2014_HST_WFC3-UVIS_full-res_denoised.jpg/800px-Pillars_of_creation_2014_HST_WFC3-UVIS_full-res_denoised.jpg'"
        >
        <?php else: ?>
        <div class="apod-video-wrap">
            <iframe src="<?= htmlspecialchars($apod['url']) ?>" allowfullscreen></iframe>
        </div>
        <?php endif; ?>

        <div class="apod-overlay">
            <div style="display:flex; flex-wrap:wrap; gap:0;">
                <div class="apod-badge">🔴 NASA APOD</div>
                <div class="apod-date-badge">📅 <?= htmlspecialchars($apod['date']) ?></div>
            </div>
            <h1 class="apod-title"><?= htmlspecialchars($apod['title']) ?></h1>
            <?php if (!empty($apod['copyright'])): ?>
            <div class="apod-copyright">© <?= htmlspecialchars($apod['copyright']) ?></div>
            <?php endif; ?>
            <div class="apod-actions">
                <?php if ($isImage && !empty($apod['hdurl'])): ?>
                <a href="<?= htmlspecialchars($apod['hdurl']) ?>" target="_blank" class="apod-btn apod-btn-primary">
                    🔭 View HD Image
                </a>
                <?php endif; ?>
                <a href="https://apod.nasa.gov/apod/astropix.html" target="_blank" class="apod-btn apod-btn-secondary">
                    🌐 NASA APOD Site
                </a>
                <a href="chat.php" class="apod-btn apod-btn-secondary">
                    💬 Ask AI About This
                </a>
            </div>
        </div>
    </div>

    <!-- Explanation + Date Picker -->
    <div class="explain-layout">
        <div class="explain-card">
            <div class="explain-title">📖 About Today's Image</div>
            <p class="explain-text"><?= htmlspecialchars($apod['explanation']) ?></p>
        </div>

        <div class="date-card">
            <div class="date-title">📅 Browse by Date</div>
            <div class="date-input-wrap">
                <input
                    type="date"
                    class="date-input"
                    id="apodDate"
                    min="1995-06-16"
                    max="<?= date('Y-m-d') ?>"
                    value="<?= date('Y-m-d') ?>"
                >
            </div>
            <button class="date-go-btn" onclick="goToDate()">🔭 Load APOD</button>

            <div class="past-label">Recent APODs:</div>
            <?php foreach ($pastDates as $d): ?>
            <a class="past-date" href="apod.php?date=<?= $d ?>">
                📷 <?= $d ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Fun Facts -->
    <div class="facts-row">
        <div class="fact-card">
            <div class="fact-icon">📸</div>
            <div class="fact-val">10,000+</div>
            <div class="fact-label">APOD Images Since 1995</div>
        </div>
        <div class="fact-card">
            <div class="fact-icon">🔭</div>
            <div class="fact-val">June 16</div>
            <div class="fact-label">First APOD (1995)</div>
        </div>
        <div class="fact-card">
            <div class="fact-icon">🌌</div>
            <div class="fact-val">NASA</div>
            <div class="fact-label">Powered by NASA Open API</div>
        </div>
    </div>

</main>

<footer class="site-footer">
    <div class="footer-inner">
        <span>AstroNode AI · IBM AI Builders Challenge 2026</span>
        <span>NASA APOD API · Free & Open</span>
    </div>
</footer>

<script src="starfield.js"></script>
<script>
function goToDate() {
    const d = document.getElementById('apodDate').value;
    if (d) window.location.href = 'apod.php?date=' + d;
}
document.getElementById('apodDate').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') goToDate();
});
</script>
</body>
</html>