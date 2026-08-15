<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About — AstroNode AI</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        .about-hero { text-align:center; padding:60px 0 40px; }
        .about-hero h1 { font-size:clamp(28px,5vw,48px); font-weight:700; letter-spacing:-1.5px; margin-bottom:12px; }
        .about-hero p { color:var(--text-dim); font-size:15px; max-width:520px; margin:0 auto; line-height:1.75; }

        /* Grid */
        .about-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:28px; }
        .about-card {
            background:var(--bg-card);
            border:1px solid var(--border);
            border-radius:16px; padding:24px;
            transition: border-color .2s, transform .2s;
        }
        .about-card:hover { border-color:var(--violet); transform:translateY(-2px); }
        .about-card h3 { font-size:15px; font-weight:600; color:var(--violet-lt); margin-bottom:12px; }
        .about-card p  { font-size:13px; color:var(--text-dim); line-height:1.8; }
        .about-card ul { padding-left:18px; }
        .about-card li { font-size:13px; color:var(--text-dim); line-height:1.9; }

        /* Tech Stack */
        .tech-stack { display:flex; flex-wrap:wrap; gap:8px; margin-top:14px; }
        .tech-pill {
            font-size:11px; font-family:var(--mono);
            padding:4px 12px; border-radius:20px;
            background:rgba(124,58,237,0.12);
            border:1px solid rgba(124,58,237,0.25);
            color:var(--violet-lt);
        }
        .tech-pill.cyan {
            background:rgba(6,182,212,0.1);
            border-color:rgba(6,182,212,0.25);
            color:var(--cyan);
        }
        .tech-pill.green {
            background:rgba(16,185,129,0.1);
            border-color:rgba(16,185,129,0.25);
            color:var(--emerald);
        }

        /* Architecture flow */
        .arch-card {
            background:var(--bg-card);
            border:1px solid var(--border);
            border-radius:16px; padding:24px;
            margin-bottom:20px;
        }
        .arch-card h3 { font-size:15px; font-weight:600; color:var(--violet-lt); margin-bottom:16px; }
        .arch-flow {
            display:flex; align-items:center;
            flex-wrap:wrap; gap:8px;
        }
        .arch-box {
            background:rgba(255,255,255,0.04);
            border:1px solid rgba(255,255,255,0.08);
            border-radius:8px; padding:8px 16px;
            font-size:12px; color:var(--text-dim);
        }
        .arch-arrow { color:var(--violet); font-size:20px; font-weight:700; }

        /* Pages list */
        .pages-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:28px; }
        .page-pill {
            background:var(--bg-card);
            border:1px solid rgba(255,255,255,0.06);
            border-radius:12px; padding:14px 16px;
            font-size:13px; color:var(--text-dim);
            display:flex; align-items:center; gap:10px;
            transition: border-color .2s;
            text-decoration:none;
        }
        .page-pill:hover { border-color:var(--violet); color:var(--violet-lt); }
        .page-pill-icon { font-size:18px; }

        /* Builder card */
        .builder-card {
            background:var(--bg-card);
            border:1px solid var(--border);
            border-radius:16px; padding:28px;
            margin-bottom:28px;
            display:flex; align-items:center; gap:24px;
        }
        .builder-avatar {
            width:64px; height:64px; border-radius:50%;
            background:linear-gradient(135deg, var(--violet), var(--cyan));
            display:flex; align-items:center; justify-content:center;
            font-size:28px; flex-shrink:0;
        }
        .builder-name { font-size:17px; font-weight:700; margin-bottom:4px; }
        .builder-role { font-size:12px; color:var(--text-muted); font-family:var(--mono); margin-bottom:10px; }
        .builder-tags { display:flex; flex-wrap:wrap; gap:6px; }
        .builder-tag {
            font-size:11px; padding:3px 10px;
            border-radius:20px;
            background:rgba(6,182,212,0.1);
            border:1px solid rgba(6,182,212,0.2);
            color:var(--cyan);
        }

        /* IBM badge */
        .ibm-card {
            background:linear-gradient(135deg, rgba(6,182,212,0.08), rgba(124,58,237,0.08));
            border:1px solid rgba(6,182,212,0.2);
            border-radius:16px; padding:24px;
            margin-bottom:28px;
            text-align:center;
        }
        .ibm-logo { font-size:32px; margin-bottom:10px; }
        .ibm-title { font-size:16px; font-weight:700; color:var(--cyan); margin-bottom:6px; }
        .ibm-sub { font-size:13px; color:var(--text-muted); }
        .ibm-badges { display:flex; justify-content:center; gap:10px; margin-top:14px; flex-wrap:wrap; }
        .ibm-badge {
            font-size:11px; padding:4px 14px;
            border-radius:20px; font-weight:600;
        }
        .ibm-badge-blue { background:rgba(6,182,212,0.15); border:1px solid rgba(6,182,212,0.3); color:var(--cyan); }
        .ibm-badge-purple { background:rgba(124,58,237,0.15); border:1px solid rgba(124,58,237,0.3); color:var(--violet-lt); }
        .ibm-badge-green { background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.3); color:var(--emerald); }

        @media(max-width:700px){
            .about-grid { grid-template-columns:1fr; }
            .pages-grid { grid-template-columns:repeat(2,1fr); }
            .builder-card { flex-direction:column; text-align:center; }
            .arch-flow { justify-content:center; }
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
                    <circle cx="6"  cy="10" r="1.5" fill="#06b6d4" opacity="0.8"/>
                    <circle cx="29" cy="26" r="1"   fill="#10b981" opacity="0.7"/>
                    <circle cx="28" cy="8"  r="1.2" fill="#f59e0b" opacity="0.8"/>
                </svg>
            </div>
            <div>
                <span class="logo-name">AstroNode <em>AI</em></span>
                <span class="logo-tag">Astronomical Intelligence</span>
            </div>
        </div>
        <nav class="main-nav">
            <a href="index.php"        class="nav-link">Query</a>
            <a href="explore.php"      class="nav-link">Explore</a>
            <a href="habitability.php" class="nav-link">Habitability</a>
            <a href="chat.php"         class="nav-link">AI Chat</a>
            <a href="apod.php"         class="nav-link">APOD</a>
            <a href="starmap.php"      class="nav-link">3D Map</a>
            <a href="timeline.php"     class="nav-link">Timeline</a>
            <a href="datasets.php"     class="nav-link">Datasets</a>
            <a href="about.php"        class="nav-link active">About</a>
        </nav>
        <div class="header-badges">
            <span class="badge badge-ibm">IBM watsonx</span>
            <span class="badge badge-nasa">NASA API</span>
        </div>
    </div>
</header>

<main class="main-content">

    <!-- Hero -->
    <div class="about-hero">
        <h1>About <span class="gradient-text">AstroNode AI</span></h1>
        <p>A natural language interface to NASA &amp; ESA astronomical datasets, powered by IBM Granite &amp; watsonx.ai. Making space data accessible to everyone.</p>
    </div>

    <!-- IBM Competition Card -->
    <div class="ibm-card">
        <div class="ibm-logo">🏆</div>
        <div class="ibm-title">IBM AI Builders Challenge 2026</div>
        <div class="ibm-sub">Space Exploration Theme · August 2026 Submission</div>
        <div class="ibm-badges">
            <span class="ibm-badge ibm-badge-blue">IBM Granite-3-8B</span>
            <span class="ibm-badge ibm-badge-purple">watsonx.ai</span>
            <span class="ibm-badge ibm-badge-green">NASA Open API</span>
            <span class="ibm-badge ibm-badge-blue">ESA Gaia DR3</span>
        </div>
    </div>

    <!-- Problem & Solution -->
    <div class="about-grid">
        <div class="about-card">
            <h3>🎯 The Problem</h3>
            <p>NASA and ESA datasets contain billions of astronomical records — but accessing them requires SQL expertise, domain knowledge, or expensive tools. Researchers, students, and the public struggle to explore this data.</p>
        </div>
        <div class="about-card">
            <h3>💡 The Solution</h3>
            <p>AstroNode AI lets anyone query terabytes of space data in plain English. IBM Granite translates natural language into structured queries and provides expert scientific context with every result.</p>
        </div>
        <div class="about-card">
            <h3>🔧 Tech Stack</h3>
            <div class="tech-stack">
                <span class="tech-pill">PHP 8</span>
                <span class="tech-pill">MySQL</span>
                <span class="tech-pill">XAMPP</span>
                <span class="tech-pill cyan">IBM watsonx.ai</span>
                <span class="tech-pill cyan">Granite-3-8B</span>
                <span class="tech-pill green">NASA API</span>
                <span class="tech-pill green">Three.js</span>
                <span class="tech-pill">Chart.js</span>
                <span class="tech-pill">HTML5 / CSS3</span>
                <span class="tech-pill">JavaScript</span>
            </div>
        </div>
        <div class="about-card">
            <h3>📡 Data Sources</h3>
            <ul>
                <li>NASA Exoplanet Archive — 5,700+ confirmed planets</li>
                <li>ESA Gaia DR3 — 1.8 billion stellar sources</li>
                <li>Hipparcos Catalog — nearby star distances</li>
                <li>NASA APOD — daily astronomy images</li>
                <li>Chandra X-ray — supernova remnants</li>
                <li>JWST Early Release Science data</li>
            </ul>
        </div>
    </div>

    <!-- Architecture -->
    <div class="arch-card">
        <h3>🏗️ System Architecture</h3>
        <div class="arch-flow">
            <div class="arch-box">👤 User (Plain English)</div>
            <div class="arch-arrow">→</div>
            <div class="arch-box">🔍 PHP Query Router</div>
            <div class="arch-arrow">→</div>
            <div class="arch-box">🗄️ MySQL Database</div>
            <div class="arch-arrow">→</div>
            <div class="arch-box">🤖 IBM Granite AI</div>
            <div class="arch-arrow">→</div>
            <div class="arch-box">📊 Chart.js Results</div>
        </div>
    </div>

    <!-- Pages -->
    <div class="section-title" style="font-size:13px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:16px;">
        🗂 Platform Pages
    </div>
    <div class="pages-grid">
        <a href="index.php" class="page-pill">
            <span class="page-pill-icon">🔍</span> NL Query Interface
        </a>
        <a href="explore.php" class="page-pill">
            <span class="page-pill-icon">🪐</span> Dataset Explorer
        </a>
        <a href="habitability.php" class="page-pill">
            <span class="page-pill-icon">🧬</span> Habitability Score
        </a>
        <a href="chat.php" class="page-pill">
            <span class="page-pill-icon">💬</span> AI Research Copilot
        </a>
        <a href="apod.php" class="page-pill">
            <span class="page-pill-icon">🌌</span> NASA APOD
        </a>
        <a href="starmap.php" class="page-pill">
            <span class="page-pill-icon">🌍</span> 3D Star Map
        </a>
        <a href="timeline.php" class="page-pill">
            <span class="page-pill-icon">📅</span> Discovery Timeline
        </a>
        <a href="datasets.php" class="page-pill">
            <span class="page-pill-icon">📡</span> Data Sources
        </a>
        <a href="about.php" class="page-pill">
            <span class="page-pill-icon">ℹ️</span> About
        </a>
    </div>

    <!-- Builder -->
    <div class="builder-card">
        <div class="builder-avatar">👩‍💻</div>
        <div>
            <div class="builder-name">Myat Noe Wai</div>
            <div class="builder-role">MIS Student · Nusaputra University · Indonesia</div>
            <div class="builder-tags">
                <span class="builder-tag">PHP / MySQL</span>
                <span class="builder-tag">IBM watsonx.ai</span>
                <span class="builder-tag">NASA API</span>
                <span class="builder-tag">AI Builder</span>
                <span class="builder-tag">Space Enthusiast</span>
            </div>
        </div>
    </div>

</main>

<footer class="site-footer">
    <div class="footer-inner">
        <span>AstroNode AI · IBM AI Builders Challenge 2026</span>
        <span>Built with PHP · MySQL · IBM watsonx.ai · NASA APIs</span>
    </div>
</footer>

<script src="starfield.js"></script>
</body>
</html>