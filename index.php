<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AstroNode AI</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<canvas id="starfield"></canvas>
<div class="nebula nebula-1"></div>
<div class="nebula nebula-2"></div>
<div class="nebula nebula-3"></div>

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
    <a href="index.php" class="nav-link active">Query</a>
    <a href="explore.php" class="nav-link">Explore</a>
    <a href="habitability.php" class="nav-link">Habitability</a>
    <a href="chat.php" class="nav-link">AI Chat</a>
  <a href="starmap.php" class="nav-link">3D starmap</a>

<a href="timeline.php" class="nav-link">Discovery Timeline</a>
    <a href="apod.php" class="nav-link">Apod</a>
    <a href="about.php" class="nav-link">About</a>
</nav>
        <div class="header-badges">
            <span class="badge badge-ibm">IBM watsonx</span>
            <span class="badge badge-nasa">NASA API</span>
        </div>
    </div>
</header>

<main class="main-content">
    <section class="hero">
        <div class="hero-eyebrow">
            <span class="dot-live"></span> Live · NASA Exoplanet Archive · ESA Gaia DR3
        </div>
        <h1 class="hero-title">Ask the Universe<br><span class="gradient-text">in Plain Language</span></h1>
        <p class="hero-sub">Query NASA &amp; ESA astronomical data using natural language. Powered by IBM Granite &amp; watsonx.ai.</p>
    </section>

    <section class="query-section">
        <div class="query-card">
            <div class="query-header">
                <span class="query-label">🔍 Natural Language Query</span>
                <span class="model-badge">IBM Granite-3-8B</span>
            </div>
            <div class="input-wrap">
                <input type="text" id="queryInput" class="query-input"
                    placeholder='Try: "Show me Earth-like exoplanets found after 2022"'
                    autocomplete="off">
                <button class="query-btn" onclick="submitQuery()">
                    <svg viewBox="0 0 20 20" fill="none" width="18" height="18">
                        <path d="M4 10h12M11 5l5 5-5 5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Search
                </button>
            </div>
            <div class="sample-chips">
                <span class="chip-label">Try:</span>
                <button class="chip" onclick="setQuery(this)">Exoplanets in habitable zone 2024</button>
                <button class="chip" onclick="setQuery(this)">Stars within 10 light years</button>
                <button class="chip" onclick="setQuery(this)">JWST gas giant discoveries</button>
                <button class="chip" onclick="setQuery(this)">Planets with water vapor detected</button>
                <button class="chip" onclick="setQuery(this)">Supernova remnants last 5 years</button>
            </div>
        </div>
    </section>

    <section class="results-section" id="resultsSection" style="display:none;">
        <div class="result-card ai-card">
            <div class="card-header">
                <div class="card-header-left">
                    <div class="ai-avatar">🤖</div>
                    <div>
                        <div class="card-title">IBM Granite Analysis</div>
                        <div class="card-sub">watsonx.ai · Granite-3-8B-Instruct</div>
                    </div>
                </div>
                <span class="source-tag">AI</span>
            </div>
            <div id="aiResponse" class="ai-response">
                <div class="typing-dots"><span></span><span></span><span></span></div>
            </div>
        </div>

        <div class="result-card data-card">
            <div class="card-header">
                <div class="card-header-left">
                    <div class="data-icon">📡</div>
                    <div>
                        <div class="card-title" id="dataSource">NASA Exoplanet Archive</div>
                        <div class="card-sub" id="resultCount">Loading...</div>
                    </div>
                </div>
                <span class="source-tag nasa">NASA</span>
            </div>
            <div class="chart-wrap"><canvas id="astroChart" height="200"></canvas></div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead id="tableHead"></thead>
                    <tbody id="tableBody"></tbody>
                </table>
            </div>
            <div class="result-tags" id="resultTags"></div>
        </div>
    </section>

    <section class="stats-row">
        <div class="stat-card"><div class="stat-num">5,700+</div><div class="stat-label">Confirmed Exoplanets</div></div>
        <div class="stat-card"><div class="stat-num">100B+</div><div class="stat-label">Stars in Milky Way</div></div>
        <div class="stat-card"><div class="stat-num">13.8B</div><div class="stat-label">Universe Age (years)</div></div>
        <div class="stat-card"><div class="stat-num">2M+</div><div class="stat-label">Gaia DR3 Sources</div></div>
    </section>
</main>

<footer class="site-footer">
    <div class="footer-inner">
        <span>AstroNode AI · IBM AI Builders Challenge 2026</span>
        <span>NASA · ESA · JWST · Gaia DR3</span>
    </div>
</footer>

<script src="starfield.js"></script>
<script src="query.js"></script>
</body>
</html>