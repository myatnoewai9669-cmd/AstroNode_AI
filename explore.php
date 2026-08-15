<?php
require_once 'includes.db.php';
$db = getDB();

$exoCount  = $db->query('SELECT COUNT(*) FROM exoplanets')->fetchColumn();
$starCount  = $db->query('SELECT COUNT(*) FROM stars')->fetchColumn();
$habCount   = $db->query('SELECT COUNT(*) FROM exoplanets WHERE habitable_zone=1')->fetchColumn();
$detectedCount = $db->query("SELECT COUNT(*) FROM exoplanets WHERE atmosphere='detected'")->fetchColumn();

$exoplanets = $db->query('SELECT * FROM exoplanets ORDER BY year_discovered DESC')->fetchAll();
$stars      = $db->query('SELECT * FROM stars ORDER BY distance_ly ASC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Datasets — AstroNode AI</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        .explore-hero { text-align:center; padding: 60px 0 36px; }
        .explore-hero h1 { font-size: clamp(28px,5vw,48px); font-weight:700; letter-spacing:-1.5px; margin-bottom:10px; }
        .stat-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:40px; }
        .mini-stat { background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:18px; text-align:center; }
        .mini-stat .n { font-size:28px; font-weight:700; color:var(--violet-lt); font-family:var(--mono); }
        .mini-stat .l { font-size:11px; color:var(--text-muted); margin-top:4px; }
        .tabs { display:flex; gap:8px; margin-bottom:20px; }
        .tab-btn { padding:8px 20px; border-radius:8px; border:1px solid rgba(255,255,255,0.08); background:transparent; color:var(--text-dim); font-family:var(--font); font-size:13px; font-weight:500; cursor:pointer; transition:all .2s; }
        .tab-btn.active { background:rgba(124,58,237,0.15); border-color:var(--violet); color:var(--violet-lt); }
        .tab-content { display:none; }
        .tab-content.active { display:block; }
        .filter-bar { display:flex; gap:10px; margin-bottom:16px; flex-wrap:wrap; }
        .filter-input { background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:8px; padding:8px 14px; color:var(--text); font-family:var(--font); font-size:13px; outline:none; }
        .filter-input:focus { border-color:var(--violet); }
        .hab-badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:600; }
        .hab-yes { background:rgba(16,185,129,0.15); color:#6ee7b7; border:1px solid rgba(16,185,129,0.25); }
        .hab-no  { background:rgba(100,116,139,0.1); color:var(--text-muted); border:1px solid rgba(100,116,139,0.15); }
        .atm-detected { color:#6ee7b7; }
        .atm-possible { color:#fcd34d; }
        .atm-unknown  { color:var(--text-muted); }
        @media(max-width:700px){ .stat-grid{grid-template-columns:repeat(2,1fr);} }
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
            <a href="explore.php" class="nav-link active">Explore</a>
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
    <div class="explore-hero">
        <h1>Explore <span class="gradient-text">Astronomical Data</span></h1>
        <p style="color:var(--text-dim);font-size:15px;">Browse the full dataset — exoplanets, nearby stars, and more.</p>
    </div>

    <div class="stat-grid">
        <div class="mini-stat"><div class="n"><?= $exoCount ?></div><div class="l">Exoplanets</div></div>
        <div class="mini-stat"><div class="n"><?= $habCount ?></div><div class="l">Habitable Zone</div></div>
        <div class="mini-stat"><div class="n"><?= $detectedCount ?></div><div class="l">Atmosphere Detected</div></div>
        <div class="mini-stat"><div class="n"><?= $starCount ?></div><div class="l">Nearby Stars</div></div>
    </div>

    <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('exoplanets',this)">🪐 Exoplanets</button>
        <button class="tab-btn" onclick="switchTab('stars',this)">⭐ Stars</button>
    </div>

    <!-- Exoplanets Tab -->
    <div class="tab-content active" id="tab-exoplanets">
        <div class="filter-bar">
            <input class="filter-input" id="exoSearch" type="text" placeholder="Search by name..." oninput="filterTable('exoTable','exoSearch')">
        </div>
        <div class="table-wrap">
            <table class="data-table" id="exoTable">
                <thead><tr>
                    <th>Name</th><th>Year</th><th>Radius (R⊕)</th><th>Mass (M⊕)</th>
                    <th>Distance (ly)</th><th>Hab. Zone</th><th>Atmosphere</th><th>Telescope</th>
                </tr></thead>
                <tbody>
                <?php foreach ($exoplanets as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= $p['year_discovered'] ?></td>
                    <td><?= $p['radius_earth'] ?></td>
                    <td><?= $p['mass_earth'] ?></td>
                    <td><?= $p['distance_ly'] ?></td>
                    <td><span class="hab-badge <?= $p['habitable_zone'] ? 'hab-yes' : 'hab-no' ?>"><?= $p['habitable_zone'] ? 'Yes' : 'No' ?></span></td>
                    <td><span class="atm-<?= $p['atmosphere'] ?>"><?= ucfirst($p['atmosphere']) ?></span></td>
                    <td style="color:var(--text-muted);font-size:11px;"><?= htmlspecialchars($p['telescope']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Stars Tab -->
    <div class="tab-content" id="tab-stars">
        <div class="filter-bar">
            <input class="filter-input" id="starSearch" type="text" placeholder="Search by name..." oninput="filterTable('starTable','starSearch')">
        </div>
        <div class="table-wrap">
            <table class="data-table" id="starTable">
                <thead><tr>
                    <th>Name</th><th>Distance (ly)</th><th>Type</th><th>Temp (K)</th><th>Planets?</th><th>Constellation</th>
                </tr></thead>
                <tbody>
                <?php foreach ($stars as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['name']) ?></td>
                    <td><?= $s['distance_ly'] ?></td>
                    <td><?= $s['star_type'] ?></td>
                    <td><?= $s['temperature_k'] ?></td>
                    <td><span class="hab-badge <?= $s['has_planets'] ? 'hab-yes' : 'hab-no' ?>"><?= $s['has_planets'] ? 'Yes' : 'No' ?></span></td>
                    <td style="color:var(--text-muted)"><?= htmlspecialchars($s['constellation']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<footer class="site-footer">
    <div class="footer-inner">
        <span>AstroNode AI · IBM AI Builders Challenge 2026</span>
        <span>NASA · ESA · Gaia DR3 · Chandra</span>
    </div>
</footer>

<script src="starfield.js"></script>
<script>
function switchTab(name, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
}
function filterTable(tableId, inputId) {
    const filter = document.getElementById(inputId).value.toLowerCase();
    document.querySelectorAll('#' + tableId + ' tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
}
</script>
</body>
</html>
