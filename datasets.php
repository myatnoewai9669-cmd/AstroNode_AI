<?php require_once 'includes.db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datasets — AstroNode AI</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        .ds-hero { text-align:center; padding:60px 0 40px; }
        .ds-hero h1 { font-size:clamp(28px,5vw,48px); font-weight:700; letter-spacing:-1.5px; margin-bottom:12px; }
        .ds-hero p { color:var(--text-dim); font-size:15px; max-width:520px; margin:0 auto; }

        .ds-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:20px; margin-bottom:40px; }

        .ds-card {
            background:var(--bg-card);
            border:1px solid var(--border);
            border-radius:18px;
            padding:24px;
            transition: border-color .2s, transform .2s;
        }
        .ds-card:hover { border-color:var(--violet); transform:translateY(-2px); }

        .ds-card-top { display:flex; align-items:center; gap:14px; margin-bottom:16px; }
        .ds-icon { font-size:28px; }
        .ds-name { font-size:15px; font-weight:700; color:var(--text); }
        .ds-org  { font-size:11px; color:var(--text-muted); margin-top:2px; font-family:var(--mono); }

        .ds-desc { font-size:13px; color:var(--text-dim); line-height:1.75; margin-bottom:16px; }

        .ds-meta { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:16px; }
        .ds-pill {
            font-size:11px; padding:3px 10px; border-radius:20px;
            background:rgba(124,58,237,0.1);
            border:1px solid rgba(124,58,237,0.2);
            color:var(--violet-lt);
        }
        .ds-pill.cyan {
            background:rgba(6,182,212,0.1);
            border-color:rgba(6,182,212,0.2);
            color:var(--cyan);
        }
        .ds-pill.green {
            background:rgba(16,185,129,0.1);
            border-color:rgba(16,185,129,0.2);
            color:var(--emerald);
        }

        .ds-stats { display:flex; gap:20px; padding-top:14px; border-top:1px solid rgba(255,255,255,0.05); }
        .ds-stat-item { text-align:center; }
        .ds-stat-n { font-size:18px; font-weight:700; color:var(--violet-lt); font-family:var(--mono); }
        .ds-stat-l { font-size:10px; color:var(--text-muted); margin-top:2px; }

        .ds-link {
            display:inline-flex; align-items:center; gap:6px;
            font-size:12px; color:var(--cyan);
            text-decoration:none; margin-top:14px;
            transition:opacity .2s;
        }
        .ds-link:hover { opacity:.7; }

        .section-title {
            font-size:13px; font-weight:600;
            color:var(--text-muted);
            text-transform:uppercase; letter-spacing:1px;
            margin-bottom:16px;
        }

        .api-table { width:100%; border-collapse:collapse; font-size:13px; margin-bottom:32px; }
        .api-table th {
            text-align:left; padding:10px 12px;
            color:var(--text-muted); font-size:11px;
            text-transform:uppercase; letter-spacing:.5px;
            border-bottom:1px solid rgba(255,255,255,0.06);
        }
        .api-table td {
            padding:10px 12px;
            color:var(--text-dim);
            border-bottom:1px solid rgba(255,255,255,0.04);
        }
        .api-table tr:hover td { background:rgba(124,58,237,0.05); }
        .api-table td:first-child { color:var(--text); font-weight:500; }
        .status-live {
            display:inline-flex; align-items:center; gap:5px;
            font-size:11px; color:var(--emerald);
        }
        .status-dot { width:6px; height:6px; border-radius:50%; background:var(--emerald); }

        @media(max-width:700px){ .ds-grid{grid-template-columns:1fr;} }
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
            <a href="datasets.php" class="nav-link active">Datasets</a>
            <a href="about.php" class="nav-link">About</a>
        </nav>
        <div class="header-badges">
            <span class="badge badge-ibm">IBM watsonx</span>
            <span class="badge badge-nasa">NASA API</span>
        </div>
    </div>
</header>

<main class="main-content">

    <div class="ds-hero">
        <h1>Data <span class="gradient-text">Sources</span></h1>
        <p>AstroNode AI connects to leading NASA and ESA astronomical archives — all queryable in plain English.</p>
    </div>

    <!-- Dataset Cards -->
    <div class="ds-grid">

        <!-- NASA Exoplanet Archive -->
        <div class="ds-card">
            <div class="ds-card-top">
                <div class="ds-icon">🪐</div>
                <div>
                    <div class="ds-name">NASA Exoplanet Archive</div>
                    <div class="ds-org">NASA · IPAC · Caltech</div>
                </div>
            </div>
            <p class="ds-desc">The definitive catalog of confirmed exoplanets, planetary systems, and stellar hosts. Includes discovery method, orbital parameters, mass, radius, equilibrium temperature, and habitability data.</p>
            <div class="ds-meta">
                <span class="ds-pill">5,700+ Planets</span>
                <span class="ds-pill cyan">TAP/ADQL</span>
                <span class="ds-pill green">Free Access</span>
            </div>
            <div class="ds-stats">
                <div class="ds-stat-item"><div class="ds-stat-n">5,700+</div><div class="ds-stat-l">Confirmed</div></div>
                <div class="ds-stat-item"><div class="ds-stat-n">4,000+</div><div class="ds-stat-l">Host Stars</div></div>
                <div class="ds-stat-item"><div class="ds-stat-n">20+</div><div class="ds-stat-l">Parameters</div></div>
            </div>
            <a class="ds-link" href="https://exoplanetarchive.ipac.caltech.edu" target="_blank">
                → exoplanetarchive.ipac.caltech.edu
            </a>
        </div>

        <!-- ESA Gaia DR3 -->
        <div class="ds-card">
            <div class="ds-card-top">
                <div class="ds-icon">⭐</div>
                <div>
                    <div class="ds-name">ESA Gaia DR3</div>
                    <div class="ds-org">European Space Agency</div>
                </div>
            </div>
            <p class="ds-desc">Third data release of ESA's Gaia mission — the most detailed 3D map of the Milky Way. Contains precise parallax, proper motion, radial velocity, and photometry for 1.8 billion sources.</p>
            <div class="ds-meta">
                <span class="ds-pill">1.8B Sources</span>
                <span class="ds-pill cyan">ADQL / TAP</span>
                <span class="ds-pill green">Open Data</span>
            </div>
            <div class="ds-stats">
                <div class="ds-stat-item"><div class="ds-stat-n">1.8B</div><div class="ds-stat-l">Sources</div></div>
                <div class="ds-stat-item"><div class="ds-stat-n">150K</div><div class="ds-stat-l">RVS Stars</div></div>
                <div class="ds-stat-item"><div class="ds-stat-n">10µas</div><div class="ds-stat-l">Precision</div></div>
            </div>
            <a class="ds-link" href="https://www.cosmos.esa.int/gaia" target="_blank">
                → cosmos.esa.int/gaia
            </a>
        </div>

        <!-- JWST -->
        <div class="ds-card">
            <div class="ds-card-top">
                <div class="ds-icon">🔭</div>
                <div>
                    <div class="ds-name">James Webb Space Telescope</div>
                    <div class="ds-org">NASA · ESA · CSA</div>
                </div>
            </div>
            <p class="ds-desc">JWST is the most powerful space telescope ever built. Its NIRSpec and MIRI instruments provide unprecedented infrared spectroscopy for atmosphere characterization of exoplanets and deep-field imaging.</p>
            <div class="ds-meta">
                <span class="ds-pill">Infrared</span>
                <span class="ds-pill cyan">MAST Archive</span>
                <span class="ds-pill green">Since 2022</span>
            </div>
            <div class="ds-stats">
                <div class="ds-stat-item"><div class="ds-stat-n">6.5m</div><div class="ds-stat-l">Mirror</div></div>
                <div class="ds-stat-item"><div class="ds-stat-n">L2</div><div class="ds-stat-l">Orbit</div></div>
                <div class="ds-stat-item"><div class="ds-stat-n">20yr</div><div class="ds-stat-l">Lifespan</div></div>
            </div>
            <a class="ds-link" href="https://mast.stsci.edu/portal/Mashup/Clients/Mast/Portal.html" target="_blank">
                → MAST Archive
            </a>
        </div>

        <!-- Chandra -->
        <div class="ds-card">
            <div class="ds-card-top">
                <div class="ds-icon">💫</div>
                <div>
                    <div class="ds-name">Chandra X-ray Observatory</div>
                    <div class="ds-org">NASA · CXC · Harvard</div>
                </div>
            </div>
            <p class="ds-desc">NASA's flagship X-ray telescope providing high-resolution imaging and spectroscopy of hot universe phenomena including supernova remnants, black holes, neutron stars, and galaxy clusters.</p>
            <div class="ds-meta">
                <span class="ds-pill">X-ray</span>
                <span class="ds-pill cyan">CXC Archive</span>
                <span class="ds-pill green">Since 1999</span>
            </div>
            <div class="ds-stats">
                <div class="ds-stat-item"><div class="ds-stat-n">25yr</div><div class="ds-stat-l">Operating</div></div>
                <div class="ds-stat-item"><div class="ds-stat-n">0.5"</div><div class="ds-stat-l">Resolution</div></div>
                <div class="ds-stat-item"><div class="ds-stat-n">1000s</div><div class="ds-stat-l">Targets/yr</div></div>
            </div>
            <a class="ds-link" href="https://cxc.harvard.edu/cda/" target="_blank">
                → Chandra Data Archive
            </a>
        </div>

    </div>

    <!-- API Endpoints Table -->
    <div class="section-title">🔌 API Endpoints Used</div>
    <table class="api-table">
        <thead>
            <tr>
                <th>Endpoint</th>
                <th>Purpose</th>
                <th>Format</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>exoplanetarchive.ipac.caltech.edu/TAP/sync</td>
                <td>Exoplanet queries via ADQL</td>
                <td>JSON / VOTable</td>
                <td><span class="status-live"><span class="status-dot"></span>Live</span></td>
            </tr>
            <tr>
                <td>us-south.ml.cloud.ibm.com/ml/v1/text/generation</td>
                <td>IBM Granite AI analysis</td>
                <td>JSON</td>
                <td><span class="status-live"><span class="status-dot"></span>Live</span></td>
            </tr>
            <tr>
                <td>iam.cloud.ibm.com/identity/token</td>
                <td>IBM IAM authentication</td>
                <td>JSON</td>
                <td><span class="status-live"><span class="status-dot"></span>Live</span></td>
            </tr>
            <tr>
                <td>api.nasa.gov/planetary/apod</td>
                <td>Astronomy Picture of the Day</td>
                <td>JSON</td>
                <td><span class="status-live"><span class="status-dot"></span>Live</span></td>
            </tr>
            <tr>
                <td>localhost/astronode (MySQL)</td>
                <td>Local cached dataset</td>
                <td>PDO / SQL</td>
                <td><span class="status-live"><span class="status-dot"></span>Local</span></td>
            </tr>
        </tbody>
    </table>

    <!-- Local DB Stats -->
    <?php
    try {
        $db = getDB();
        $exo  = $db->query('SELECT COUNT(*) FROM exoplanets')->fetchColumn();
        $hab  = $db->query('SELECT COUNT(*) FROM exoplanets WHERE habitable_zone=1')->fetchColumn();
        $atm  = $db->query("SELECT COUNT(*) FROM exoplanets WHERE atmosphere='detected'")->fetchColumn();
        $stars = $db->query('SELECT COUNT(*) FROM stars')->fetchColumn();
        $logs  = $db->query('SELECT COUNT(*) FROM query_logs')->fetchColumn();
        ?>
    <div class="section-title">🗄️ Local Database (MySQL)</div>
    <div class="ds-grid" style="grid-template-columns:repeat(5,1fr); gap:12px;">
        <div class="stat-card"><div class="stat-num"><?= $exo ?></div><div class="stat-label">Exoplanets</div></div>
        <div class="stat-card"><div class="stat-num"><?= $hab ?></div><div class="stat-label">Habitable Zone</div></div>
        <div class="stat-card"><div class="stat-num"><?= $atm ?></div><div class="stat-label">Atm. Detected</div></div>
        <div class="stat-card"><div class="stat-num"><?= $stars ?></div><div class="stat-label">Nearby Stars</div></div>
        <div class="stat-card"><div class="stat-num"><?= $logs ?></div><div class="stat-label">Queries Run</div></div>
    </div>
    <?php } catch(Exception $e) {
        echo '<p style="color:var(--text-muted);font-size:13px;">⚠️ Database not connected. Import astronode.sql in phpMyAdmin first.</p>';
    } ?>

</main>

<footer class="site-footer">
    <div class="footer-inner">
        <span>AstroNode AI · IBM AI Builders Challenge 2026</span>
        <span>NASA · ESA · JWST · Gaia DR3 · Chandra</span>
    </div>
</footer>

<script src="starfield.js"></script>
</body>
</html>