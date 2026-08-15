<?php
// ============================================
// AstroNode AI — Discovery Timeline
// File: timeline.php
// ============================================
require_once 'includes.db.php';

$db = getDB();
$planets = $db->query('
    SELECT * FROM exoplanets
    ORDER BY year_discovered ASC
')->fetchAll();

$byYear = [];
foreach ($planets as $p) {
    $byYear[$p['year_discovered']][] = $p;
}
ksort($byYear);

$planetsJson = json_encode($planets);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discovery Timeline — AstroNode AI</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .tl-hero { text-align:center; padding:60px 0 36px; }
        .tl-hero h1 { font-size:clamp(28px,5vw,48px); font-weight:700; letter-spacing:-1.5px; margin-bottom:12px; }

        /* Chart */
        .chart-card {
            background:var(--bg-card);
            border:1px solid var(--border);
            border-radius:18px;
            padding:24px;
            margin-bottom:32px;
        }
        .chart-card-title { font-size:13px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; margin-bottom:16px; }

        /* Filter bar */
        .filter-row {
            display:flex; gap:8px; flex-wrap:wrap;
            margin-bottom:28px; align-items:center;
        }
        .filter-label { font-size:12px; color:var(--text-muted); }
        .filter-chip {
            padding:6px 14px; border-radius:20px;
            border:1px solid rgba(255,255,255,0.08);
            background:rgba(255,255,255,0.04);
            color:var(--text-dim); font-family:var(--font);
            font-size:12px; cursor:pointer; transition:all .2s;
        }
        .filter-chip:hover, .filter-chip.active {
            border-color:var(--violet);
            color:var(--violet-lt);
            background:rgba(124,58,237,0.1);
        }

        /* Timeline */
        .timeline { position:relative; padding-left:32px; }
        .timeline::before {
            content:'';
            position:absolute; left:10px; top:0; bottom:0;
            width:2px;
            background:linear-gradient(to bottom, var(--violet), var(--cyan), transparent);
            border-radius:2px;
        }

        .year-block { margin-bottom:40px; }

        .year-marker {
            position:relative;
            display:flex; align-items:center;
            gap:14px; margin-bottom:16px;
        }
        .year-dot {
            position:absolute; left:-28px;
            width:16px; height:16px;
            border-radius:50%;
            background:var(--violet);
            border:3px solid var(--bg);
            box-shadow:0 0 12px rgba(124,58,237,0.6);
        }
        .year-label {
            font-size:22px; font-weight:700;
            font-family:var(--mono);
            color:var(--violet-lt);
            letter-spacing:-1px;
        }
        .year-count {
            font-size:11px; color:var(--text-muted);
            background:rgba(124,58,237,0.1);
            border:1px solid rgba(124,58,237,0.2);
            border-radius:20px; padding:2px 10px;
        }
        .year-mission {
            font-size:11px; color:var(--cyan);
            font-family:var(--mono);
        }

        /* Planet cards */
        .planet-cards { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:12px; }

        .planet-card {
            background:var(--bg-card);
            border:1px solid rgba(255,255,255,0.06);
            border-radius:14px; padding:16px;
            transition:all .2s; cursor:pointer;
            animation:fadeUp .4s ease;
        }
        .planet-card:hover {
            border-color:var(--violet);
            transform:translateY(-2px);
            box-shadow:0 8px 24px rgba(124,58,237,0.15);
        }
        .planet-card-top {
            display:flex; align-items:center;
            justify-content:space-between; margin-bottom:10px;
        }
        .planet-card-name { font-size:14px; font-weight:600; }
        .planet-card-icon { font-size:20px; }

        .planet-chip {
            font-size:10px; padding:2px 8px;
            border-radius:10px; font-weight:600;
        }
        .chip-hab  { background:rgba(16,185,129,0.15); color:#6ee7b7; border:1px solid rgba(16,185,129,0.25); }
        .chip-atm  { background:rgba(6,182,212,0.12); color:var(--cyan); border:1px solid rgba(6,182,212,0.2); }
        .chip-unkn { background:rgba(100,116,139,0.1); color:var(--text-muted); border:1px solid rgba(100,116,139,0.15); }

        .planet-meta { display:flex; flex-direction:column; gap:4px; }
        .planet-meta-row {
            display:flex; justify-content:space-between;
            font-size:11px;
        }
        .pm-key { color:var(--text-muted); }
        .pm-val { color:var(--text-dim); font-family:var(--mono); }

        .telescope-tag {
            margin-top:10px; font-size:10px;
            color:var(--text-muted); font-family:var(--mono);
            display:flex; align-items:center; gap:4px;
        }

        /* Mission events */
        .mission-events {
            background:var(--bg-card);
            border:1px solid var(--border-2);
            border-radius:18px; padding:24px;
            margin-bottom:32px;
        }
        .mission-title { font-size:14px; font-weight:600; margin-bottom:16px; color:var(--cyan); }
        .mission-row {
            display:flex; align-items:flex-start;
            gap:14px; padding:10px 0;
            border-bottom:1px solid rgba(255,255,255,0.04);
        }
        .mission-row:last-child { border-bottom:none; }
        .mission-year {
            font-size:12px; font-family:var(--mono);
            color:var(--violet-lt); min-width:42px;
            font-weight:600;
        }
        .mission-name { font-size:13px; font-weight:600; margin-bottom:3px; }
        .mission-desc { font-size:12px; color:var(--text-muted); line-height:1.6; }

        @media(max-width:700px){
            .planet-cards { grid-template-columns:1fr; }
        }
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
            <a href="chat.php" class="nav-link">AI Chat</a>
            <a href="apod.php" class="nav-link">APOD</a>
            <a href="starmap.php" class="nav-link">3D Map</a>
            <a href="timeline.php" class="nav-link active">Timeline</a>
            <a href="datasets.php" class="nav-link">Datasets</a>
        </nav>
        <div class="header-badges">
            <span class="badge badge-ibm">IBM watsonx</span>
            <span class="badge badge-nasa">NASA API</span>
        </div>
    </div>
</header>

<main class="main-content">
    <div class="tl-hero">
        <h1>📅 Discovery <span class="gradient-text">Timeline</span></h1>
        <p style="color:var(--text-dim);font-size:15px;">
            Exoplanet discoveries from 2004–2026 · JWST · TESS · Kepler · Gaia
        </p>
    </div>

    <!-- Chart -->
    <div class="chart-card">
        <div class="chart-card-title">📊 Discoveries Per Year</div>
        <canvas id="timelineChart" height="100"></canvas>
    </div>

    <!-- Mission Highlights -->
    <div class="mission-events">
        <div class="mission-title">🚀 Key Mission Milestones</div>
        <div class="mission-row">
            <div class="mission-year">2004</div>
            <div>
                <div class="mission-name">55 Cancri e — First Super-Earth</div>
                <div class="mission-desc">Discovered via radial velocity. Later studied by Hubble and JWST for atmospheric composition.</div>
            </div>
        </div>
        <div class="mission-row">
            <div class="mission-year">2011</div>
            <div>
                <div class="mission-name">HARPS finds GJ 667Cc</div>
                <div class="mission-desc">ESO's HARPS spectrograph discovers a super-Earth in the habitable zone of a triple star system.</div>
            </div>
        </div>
        <div class="mission-row">
            <div class="mission-year">2015</div>
            <div>
                <div class="mission-name">Kepler Mission Peak — K2-18b, Kepler-442b</div>
                <div class="mission-desc">NASA's Kepler telescope discovers hundreds of worlds. K2-18b later confirmed to have water vapor by JWST.</div>
            </div>
        </div>
        <div class="mission-row">
            <div class="mission-year">2016</div>
            <div>
                <div class="mission-name">Proxima Centauri b — Nearest Exoplanet</div>
                <div class="mission-desc">ESO discovers an Earth-mass planet orbiting our nearest stellar neighbor, just 4.24 light years away.</div>
            </div>
        </div>
        <div class="mission-row">
            <div class="mission-year">2017</div>
            <div>
                <div class="mission-name">TRAPPIST-1 System — 7 Earth-sized Worlds</div>
                <div class="mission-desc">NASA/Spitzer announces 7 Earth-sized planets around an ultracool dwarf, 3 in the habitable zone.</div>
            </div>
        </div>
        <div class="mission-row">
            <div class="mission-year">2020</div>
            <div>
                <div class="mission-name">TESS Discovers TOI-700d</div>
                <div class="mission-desc">NASA's TESS satellite finds an Earth-sized planet in the habitable zone of a nearby M-dwarf star.</div>
            </div>
        </div>
        <div class="mission-row">
            <div class="mission-year">2022</div>
            <div>
                <div class="mission-name">JWST First Light — New Era of Exoplanet Science</div>
                <div class="mission-desc">James Webb Space Telescope launches a new era — detecting CO₂ on WASP-39b and water on K2-18b.</div>
            </div>
        </div>
        <div class="mission-row">
            <div class="mission-year">2024</div>
            <div>
                <div class="mission-name">TOI-715b & GJ 12b — New Habitable Candidates</div>
                <div class="mission-desc">TESS and CHEOPS discover two new promising habitable zone planets within 140 light years of Earth.</div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="filter-row">
        <span class="filter-label">Filter:</span>
        <button class="filter-chip active" onclick="filterCards('all',this)">All</button>
        <button class="filter-chip" onclick="filterCards('habitable',this)">🌍 Habitable Zone</button>
        <button class="filter-chip" onclick="filterCards('detected',this)">💧 Atmosphere Detected</button>
        <button class="filter-chip" onclick="filterCards('recent',this)">🆕 2020+</button>
    </div>

    <!-- Timeline -->
    <div class="timeline" id="timelineEl">
        <?php foreach ($byYear as $year => $yPlanets): ?>
        <?php
        $missions = [
            2004 => 'HARPS / Hubble',
            2011 => 'HARPS / ESO',
            2015 => 'Kepler / K2',
            2016 => 'ESO / HARPS',
            2017 => 'Spitzer / JWST',
            2020 => 'TESS',
            2024 => 'TESS / CHEOPS',
        ];
        ?>
        <div class="year-block" data-year="<?= $year ?>">
            <div class="year-marker">
                <div class="year-dot"></div>
                <div class="year-label"><?= $year ?></div>
                <div class="year-count"><?= count($yPlanets) ?> planet<?= count($yPlanets) > 1 ? 's' : '' ?></div>
                <?php if (isset($missions[$year])): ?>
                <div class="year-mission">🛰 <?= $missions[$year] ?></div>
                <?php endif; ?>
            </div>
            <div class="planet-cards">
                <?php foreach ($yPlanets as $p): ?>
                <?php
                $icon = $p['habitable_zone'] ? '🌍' : '🪐';
                $chipClass = $p['habitable_zone'] ? 'chip-hab' : 'chip-unkn';
                $chipText  = $p['habitable_zone'] ? 'Habitable' : 'Non-hab';
                $atmClass  = $p['atmosphere'] === 'detected' ? 'chip-atm' : 'chip-unkn';
                ?>
                <div class="planet-card"
                     data-hab="<?= $p['habitable_zone'] ?>"
                     data-atm="<?= $p['atmosphere'] ?>"
                     data-year="<?= $year ?>">
                    <div class="planet-card-top">
                        <div class="planet-card-name"><?= htmlspecialchars($p['name']) ?></div>
                        <div class="planet-card-icon"><?= $icon ?></div>
                    </div>
                    <div style="display:flex;gap:6px;margin-bottom:10px;">
                        <span class="planet-chip <?= $chipClass ?>"><?= $chipText ?></span>
                        <span class="planet-chip <?= $atmClass ?>">Atm: <?= $p['atmosphere'] ?></span>
                    </div>
                    <div class="planet-meta">
                        <div class="planet-meta-row">
                            <span class="pm-key">Distance</span>
                            <span class="pm-val"><?= $p['distance_ly'] ?> ly</span>
                        </div>
                        <div class="planet-meta-row">
                            <span class="pm-key">Radius</span>
                            <span class="pm-val"><?= $p['radius_earth'] ?> R⊕</span>
                        </div>
                        <div class="planet-meta-row">
                            <span class="pm-key">Temperature</span>
                            <span class="pm-val"><?= $p['temperature_k'] ?> K</span>
                        </div>
                    </div>
                    <div class="telescope-tag">🔭 <?= htmlspecialchars($p['telescope']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</main>

<footer class="site-footer">
    <div class="footer-inner">
        <span>AstroNode AI · IBM AI Builders Challenge 2026</span>
        <span>NASA · ESA · JWST · Kepler · TESS · Gaia</span>
    </div>
</footer>

<script src="starfield.js"></script>
<script>
const PLANETS = <?= $planetsJson ?>;

// --- Chart ---
const byYear = {};
PLANETS.forEach(p => {
    byYear[p.year_discovered] = (byYear[p.year_discovered] || 0) + 1;
});
const years  = Object.keys(byYear).sort();
const counts = years.map(y => byYear[y]);

new Chart(document.getElementById('timelineChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: years,
        datasets: [{
            data: counts,
            backgroundColor: years.map((_, i) =>
                ['#7c3aed','#06b6d4','#10b981','#f59e0b','#8b5cf6','#3b82f6','#14b8a6'][i % 7] + 'cc'
            ),
            borderColor: years.map((_, i) =>
                ['#7c3aed','#06b6d4','#10b981','#f59e0b','#8b5cf6','#3b82f6','#14b8a6'][i % 7]
            ),
            borderWidth: 1,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { ticks: { color: '#64748b' }, grid: { color: 'rgba(255,255,255,0.04)' } },
            y: { ticks: { color: '#64748b', stepSize: 1 }, grid: { color: 'rgba(255,255,255,0.04)' } }
        }
    }
});

// --- Filter ---
function filterCards(type, btn) {
    document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.planet-card').forEach(card => {
        let show = true;
        if (type === 'habitable') show = card.dataset.hab === '1';
        if (type === 'detected')  show = card.dataset.atm === 'detected';
        if (type === 'recent')    show = parseInt(card.dataset.year) >= 2020;
        card.style.display = show ? '' : 'none';
    });

    // Hide empty year blocks
    document.querySelectorAll('.year-block').forEach(block => {
        const visible = [...block.querySelectorAll('.planet-card')]
            .some(c => c.style.display !== 'none');
        block.style.display = visible ? '' : 'none';
    });
}
</script>
</body>
</html>