<?php
// ============================================
// AstroNode AI — Habitability Score Calculator
// File: habitability.php
// ============================================
require_once 'includes.db.php';

// --- Calculate Score ---
function calcHabitability(array $p): array {
    $score  = 0;
    $notes  = [];
    $detail = [];

    // 1. Habitable Zone (25 pts)
    if ($p['habitable_zone']) {
        $score += 25;
        $notes[]  = "In habitable zone";
        $detail[] = ['label' => 'Habitable Zone', 'pts' => 25, 'max' => 25, 'note' => '✓ Confirmed'];
    } else {
        $detail[] = ['label' => 'Habitable Zone', 'pts' => 0, 'max' => 25, 'note' => '✗ Outside zone'];
    }

    // 2. Radius (20 pts) — Earth-like: 0.5–1.6 R⊕
    $r = (float)$p['radius_earth'];
    if ($r >= 0.5 && $r <= 1.6) {
        $pts = 20;
        $note = "Earth-like radius ({$r} R⊕)";
    } elseif ($r > 1.6 && $r <= 2.5) {
        $pts = 10;
        $note = "Super-Earth radius ({$r} R⊕)";
    } else {
        $pts = 0;
        $note = "Too large/small ({$r} R⊕)";
    }
    $score   += $pts;
    $detail[] = ['label' => 'Radius', 'pts' => $pts, 'max' => 20, 'note' => $note];

    // 3. Temperature (20 pts) — Liquid water: 200–320 K
    $t = (int)$p['temperature_k'];
    if ($t >= 200 && $t <= 320) {
        $pts = 20;
        $note = "Liquid water possible ({$t} K)";
    } elseif (($t >= 150 && $t < 200) || ($t > 320 && $t <= 400)) {
        $pts = 8;
        $note = "Marginal temperature ({$t} K)";
    } else {
        $pts = 0;
        $note = "Extreme temperature ({$t} K)";
    }
    $score   += $pts;
    $detail[] = ['label' => 'Temperature', 'pts' => $pts, 'max' => 20, 'note' => $note];

    // 4. Atmosphere (20 pts)
    $atm = $p['atmosphere'];
    if ($atm === 'detected') {
        $pts = 20; $note = "Atmosphere confirmed (JWST)";
    } elseif ($atm === 'possible') {
        $pts = 12; $note = "Atmosphere possible";
    } elseif ($atm === 'unknown') {
        $pts = 5;  $note = "Atmosphere unknown";
    } else {
        $pts = 0;  $note = "No atmosphere";
    }
    $score   += $pts;
    $detail[] = ['label' => 'Atmosphere', 'pts' => $pts, 'max' => 20, 'note' => $note];

    // 5. Distance (15 pts) — Closer = more accessible
    $d = (float)$p['distance_ly'];
    if ($d <= 50) {
        $pts = 15; $note = "Very close ({$d} ly)";
    } elseif ($d <= 200) {
        $pts = 10; $note = "Reachable range ({$d} ly)";
    } elseif ($d <= 1000) {
        $pts = 5;  $note = "Distant ({$d} ly)";
    } else {
        $pts = 2;  $note = "Very distant ({$d} ly)";
    }
    $score   += $pts;
    $detail[] = ['label' => 'Distance', 'pts' => $pts, 'max' => 15, 'note' => $note];

    // Grade
    if ($score >= 80)      { $grade = 'A+'; $label = 'Prime Candidate'; $color = '#10b981'; }
    elseif ($score >= 65)  { $grade = 'A';  $label = 'High Potential';  $color = '#34d399'; }
    elseif ($score >= 50)  { $grade = 'B';  $label = 'Promising';       $color = '#06b6d4'; }
    elseif ($score >= 35)  { $grade = 'C';  $label = 'Marginal';        $color = '#f59e0b'; }
    else                   { $grade = 'D';  $label = 'Unlikely';        $color = '#ef4444'; }

    return [
        'score'  => $score,
        'grade'  => $grade,
        'label'  => $label,
        'color'  => $color,
        'detail' => $detail,
    ];
}

// --- Get all exoplanets ---
$db       = getDB();
$planets  = $db->query('SELECT * FROM exoplanets ORDER BY name ASC')->fetchAll();

// --- Selected planet ---
$selected = null;
$result   = null;
if (!empty($_GET['planet'])) {
    $stmt = $db->prepare('SELECT * FROM exoplanets WHERE id = ?');
    $stmt->execute([(int)$_GET['planet']]);
    $selected = $stmt->fetch();
    if ($selected) $result = calcHabitability($selected);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Habitability Score — AstroNode AI</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        .hab-hero { text-align:center; padding:60px 0 36px; }
        .hab-hero h1 { font-size:clamp(28px,5vw,48px); font-weight:700; letter-spacing:-1.5px; margin-bottom:12px; }

        /* Selector */
        .planet-selector {
            background:var(--bg-card);
            border:1px solid var(--border);
            border-radius:18px;
            padding:24px;
            margin-bottom:28px;
        }
        .selector-label { font-size:13px; color:var(--text-muted); margin-bottom:12px; }
        .selector-wrap { display:flex; gap:10px; }
        .planet-select {
            flex:1;
            background:rgba(255,255,255,0.04);
            border:1px solid rgba(255,255,255,0.1);
            border-radius:8px;
            padding:12px 16px;
            color:var(--text);
            font-family:var(--font);
            font-size:14px;
            outline:none;
            cursor:pointer;
        }
        .planet-select:focus { border-color:var(--violet); }
        .calc-btn {
            padding:12px 24px;
            background:linear-gradient(135deg, var(--violet), #5b21b6);
            border:none; border-radius:8px;
            color:white; font-family:var(--font);
            font-size:14px; font-weight:600;
            cursor:pointer; transition:all .2s;
        }
        .calc-btn:hover { transform:translateY(-1px); box-shadow:0 6px 20px rgba(124,58,237,0.4); }

        /* Score Display */
        .score-layout { display:grid; grid-template-columns:280px 1fr; gap:24px; margin-bottom:32px; }

        .score-circle-card {
            background:var(--bg-card);
            border:1px solid var(--border);
            border-radius:18px;
            padding:32px 24px;
            display:flex; flex-direction:column;
            align-items:center; text-align:center;
        }
        .planet-name-big { font-size:18px; font-weight:700; margin-bottom:24px; }

        .circle-wrap { position:relative; width:160px; height:160px; margin-bottom:20px; }
        .circle-wrap svg { transform:rotate(-90deg); }
        .circle-bg { fill:none; stroke:rgba(255,255,255,0.06); stroke-width:10; }
        .circle-fill { fill:none; stroke-width:10; stroke-linecap:round; transition:stroke-dashoffset 1s ease; }
        .circle-text {
            position:absolute; inset:0;
            display:flex; flex-direction:column;
            align-items:center; justify-content:center;
        }
        .score-num  { font-size:42px; font-weight:700; font-family:var(--mono); line-height:1; }
        .score-max  { font-size:12px; color:var(--text-muted); }
        .grade-badge {
            font-size:22px; font-weight:700;
            padding:6px 20px; border-radius:20px;
            margin-bottom:8px;
        }
        .grade-label { font-size:13px; color:var(--text-dim); }

        /* Detail bars */
        .detail-card {
            background:var(--bg-card);
            border:1px solid var(--border);
            border-radius:18px;
            padding:24px;
        }
        .detail-title { font-size:14px; font-weight:600; margin-bottom:20px; color:var(--text); }
        .detail-row { margin-bottom:18px; }
        .detail-row-top {
            display:flex; justify-content:space-between;
            margin-bottom:7px; font-size:13px;
        }
        .detail-row-name { color:var(--text); font-weight:500; }
        .detail-row-pts  { color:var(--text-muted); font-family:var(--mono); }
        .detail-note { font-size:11px; color:var(--text-muted); margin-bottom:6px; }
        .bar-track {
            height:7px; background:rgba(255,255,255,0.06);
            border-radius:10px; overflow:hidden;
        }
        .bar-fill {
            height:100%; border-radius:10px;
            transition:width 1s ease;
        }

        /* Rankings */
        .rankings-card {
            background:var(--bg-card);
            border:1px solid var(--border);
            border-radius:18px;
            padding:24px;
            margin-bottom:32px;
        }
        .rankings-title { font-size:14px; font-weight:600; margin-bottom:16px; }
        .rank-row {
            display:flex; align-items:center; gap:14px;
            padding:10px 0;
            border-bottom:1px solid rgba(255,255,255,0.04);
        }
        .rank-row:last-child { border-bottom:none; }
        .rank-num  { font-size:12px; color:var(--text-muted); font-family:var(--mono); width:20px; }
        .rank-name { flex:1; font-size:13px; font-weight:500; }
        .rank-score-wrap { display:flex; align-items:center; gap:10px; }
        .rank-bar  { width:80px; height:5px; background:rgba(255,255,255,0.06); border-radius:10px; overflow:hidden; }
        .rank-bar-fill { height:100%; border-radius:10px; }
        .rank-pts  { font-size:12px; font-family:var(--mono); color:var(--text-muted); width:36px; text-align:right; }
        .rank-grade {
            font-size:10px; font-weight:700;
            padding:2px 8px; border-radius:10px;
            background:rgba(124,58,237,0.15);
            color:var(--violet-lt);
        }

        @media(max-width:700px){
            .score-layout { grid-template-columns:1fr; }
            .selector-wrap { flex-direction:column; }
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
            <a href="habitability.php" class="nav-link active">Habitability</a>
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
    <div class="hab-hero">
        <h1>🧬 AI <span class="gradient-text">Habitability Score</span></h1>
        <p style="color:var(--text-dim);font-size:15px;">
            Scientific scoring system — 5 parameters, 0–100 points.<br>
            Based on NASA Exoplanet Archive data.
        </p>
    </div>

    <!-- Planet Selector -->
    <div class="planet-selector">
        <div class="selector-label">Select an exoplanet to analyze:</div>
        <form method="GET" action="habitability.php">
            <div class="selector-wrap">
                <select name="planet" class="planet-select">
                    <option value="">— Choose a planet —</option>
                    <?php foreach ($planets as $p): ?>
                    <option value="<?= $p['id'] ?>"
                        <?= (isset($_GET['planet']) && $_GET['planet'] == $p['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['name']) ?>
                        (<?= $p['year_discovered'] ?> · <?= $p['distance_ly'] ?> ly)
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="calc-btn">🧬 Calculate Score</button>
            </div>
        </form>
    </div>

    <!-- Score Result -->
    <?php if ($selected && $result): ?>
    <?php
        $score = $result['score'];
        $color = $result['color'];
        $circ  = 2 * M_PI * 70; // radius=70
        $offset = $circ - ($score / 100) * $circ;
    ?>
    <div class="score-layout">

        <!-- Circle Score -->
        <div class="score-circle-card">
            <div class="planet-name-big"><?= htmlspecialchars($selected['name']) ?></div>
            <div class="circle-wrap">
                <svg viewBox="0 0 160 160" width="160" height="160">
                    <circle class="circle-bg" cx="80" cy="80" r="70"/>
                    <circle class="circle-fill"
                        cx="80" cy="80" r="70"
                        stroke="<?= $color ?>"
                        stroke-dasharray="<?= $circ ?>"
                        stroke-dashoffset="<?= $offset ?>"
                        id="scoreCircle"
                    />
                </svg>
                <div class="circle-text">
                    <span class="score-num" style="color:<?= $color ?>"><?= $score ?></span>
                    <span class="score-max">/100</span>
                </div>
            </div>
            <div class="grade-badge" style="background:<?= $color ?>22; color:<?= $color ?>; border:1px solid <?= $color ?>44;">
                Grade <?= $result['grade'] ?>
            </div>
            <div class="grade-label"><?= $result['label'] ?></div>

            <div style="margin-top:20px; text-align:left; width:100%;">
                <?php
                $fields = [
                    'Host Star'   => $selected['host_star'],
                    'Year'        => $selected['year_discovered'],
                    'Method'      => $selected['discovery_method'],
                    'Telescope'   => $selected['telescope'],
                ];
                foreach ($fields as $k => $v): ?>
                <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,0.05);font-size:12px;">
                    <span style="color:var(--text-muted)"><?= $k ?></span>
                    <span style="color:var(--text-dim)"><?= htmlspecialchars($v) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Detail Bars -->
        <div class="detail-card">
            <div class="detail-title">📊 Score Breakdown</div>
            <?php foreach ($result['detail'] as $d): ?>
            <?php $pct = $d['max'] > 0 ? ($d['pts'] / $d['max']) * 100 : 0; ?>
            <div class="detail-row">
                <div class="detail-row-top">
                    <span class="detail-row-name"><?= $d['label'] ?></span>
                    <span class="detail-row-pts"><?= $d['pts'] ?> / <?= $d['max'] ?> pts</span>
                </div>
                <div class="detail-note"><?= $d['note'] ?></div>
                <div class="bar-track">
                    <div class="bar-fill" style="width:<?= $pct ?>%; background:<?= $pct >= 75 ? '#10b981' : ($pct >= 40 ? '#06b6d4' : '#f59e0b') ?>;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- All Planets Rankings -->
    <div class="rankings-card">
        <div class="rankings-title">🏆 All Planets — Habitability Ranking</div>
        <?php
        $ranked = [];
        foreach ($planets as $p) {
            $r = calcHabitability($p);
            $ranked[] = array_merge($p, ['hab_score' => $r['score'], 'hab_grade' => $r['grade'], 'hab_color' => $r['color']]);
        }
        usort($ranked, fn($a, $b) => $b['hab_score'] - $a['hab_score']);
        foreach ($ranked as $i => $p):
        ?>
        <div class="rank-row">
            <div class="rank-num">#<?= $i+1 ?></div>
            <div class="rank-name">
                <?= htmlspecialchars($p['name']) ?>
                <span style="font-size:11px;color:var(--text-muted);margin-left:6px;"><?= $p['distance_ly'] ?> ly</span>
            </div>
            <div class="rank-score-wrap">
                <div class="rank-bar">
                    <div class="rank-bar-fill" style="width:<?= $p['hab_score'] ?>%; background:<?= $p['hab_color'] ?>;"></div>
                </div>
                <div class="rank-pts" style="color:<?= $p['hab_color'] ?>"><?= $p['hab_score'] ?></div>
                <div class="rank-grade"><?= $p['hab_grade'] ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</main>

<footer class="site-footer">
    <div class="footer-inner">
        <span>AstroNode AI · IBM AI Builders Challenge 2026</span>
        <span>NASA · ESA · JWST · Gaia DR3</span>
    </div>
</footer>

<script src="starfield.js"></script>
</body>
</html>