<?php
// ============================================
// AstroNode AI — 3D Star Map
// File: starmap.php
// ============================================
require_once 'includes.db.php';

$db      = getDB();
$stars   = $db->query('SELECT * FROM stars ORDER BY distance_ly ASC')->fetchAll();
$planets = $db->query('SELECT * FROM exoplanets WHERE habitable_zone=1 ORDER BY distance_ly ASC')->fetchAll();

$starsJson   = json_encode($stars);
$planetsJson = json_encode($planets);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Star Map — AstroNode AI</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <style>
        body { overflow: hidden; }

        .map-header {
            position: fixed; top: 64px; left: 0; right: 0;
            z-index: 50; padding: 14px 24px;
            display: flex; align-items: center;
            justify-content: space-between;
            background: rgba(3,5,15,0.7);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .map-title { font-size: 14px; font-weight: 600; }
        .map-sub   { font-size: 11px; color: var(--text-muted); font-family: var(--mono); }
        .map-controls { display: flex; gap: 8px; }
        .map-btn {
            padding: 6px 14px; border-radius: 7px;
            font-family: var(--font); font-size: 12px;
            font-weight: 500; cursor: pointer;
            transition: all .2s; border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05); color: var(--text-dim);
        }
        .map-btn:hover, .map-btn.active {
            border-color: var(--violet);
            color: var(--violet-lt);
            background: rgba(124,58,237,0.12);
        }

        /* Canvas */
        #starMapCanvas {
            position: fixed;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            cursor: grab;
        }
        #starMapCanvas:active { cursor: grabbing; }

        /* Info Panel */
        .info-panel {
            position: fixed;
            right: 20px; top: 160px;
            width: 260px;
            background: rgba(8,13,31,0.92);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            backdrop-filter: blur(16px);
            z-index: 50;
            display: none;
        }
        .info-panel.show { display: block; animation: fadeUp .3s ease; }
        .info-close {
            position: absolute; top: 12px; right: 14px;
            background: none; border: none;
            color: var(--text-muted); cursor: pointer;
            font-size: 16px;
        }
        .info-type {
            font-size: 10px; text-transform: uppercase;
            letter-spacing: 1px; color: var(--text-muted);
            margin-bottom: 6px; font-family: var(--mono);
        }
        .info-name { font-size: 17px; font-weight: 700; margin-bottom: 14px; }
        .info-row {
            display: flex; justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            font-size: 12px;
        }
        .info-row:last-child { border-bottom: none; }
        .info-key   { color: var(--text-muted); }
        .info-val   { color: var(--text-dim); font-family: var(--mono); }
        .info-badge {
            display: inline-block; margin-top: 12px;
            padding: 3px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 600;
        }
        .badge-hab  { background:rgba(16,185,129,0.15); color:#6ee7b7; border:1px solid rgba(16,185,129,0.3); }
        .badge-star { background:rgba(245,158,11,0.15); color:#fcd34d; border:1px solid rgba(245,158,11,0.3); }

        /* Legend */
        .legend {
            position: fixed;
            left: 20px; bottom: 30px;
            background: rgba(8,13,31,0.88);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px 20px;
            backdrop-filter: blur(12px);
            z-index: 50;
        }
        .legend-title { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 10px; }
        .legend-row { display: flex; align-items: center; gap: 10px; margin-bottom: 7px; font-size: 12px; color: var(--text-dim); }
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

        /* Controls hint */
        .controls-hint {
            position: fixed;
            left: 50%; transform: translateX(-50%);
            bottom: 24px;
            background: rgba(8,13,31,0.7);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 20px;
            padding: 7px 18px;
            font-size: 11px; color: var(--text-muted);
            backdrop-filter: blur(8px);
            z-index: 50;
        }

        /* Stats bar */
        .stats-bar {
            position: fixed;
            left: 20px; top: 160px;
            display: flex; flex-direction: column; gap: 8px;
            z-index: 50;
        }
        .stat-pill {
            background: rgba(8,13,31,0.88);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 8px 14px;
            font-size: 12px;
            backdrop-filter: blur(10px);
            display: flex; align-items: center; gap: 8px;
        }
        .stat-pill-dot { width: 8px; height: 8px; border-radius: 50%; }
    </style>
</head>
<body>
<canvas id="starMapCanvas"></canvas>

<!-- Header -->
<header class="site-header" style="position:fixed;top:0;z-index:100;">
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
            <a href="starmap.php" class="nav-link active">3D Map</a>
            <a href="datasets.php" class="nav-link">Datasets</a>
        </nav>
        <div class="header-badges">
            <span class="badge badge-ibm">IBM watsonx</span>
            <span class="badge badge-nasa">NASA API</span>
        </div>
    </div>
</header>

<!-- Map Sub-header -->
<div class="map-header" style="top:64px;">
    <div>
        <div class="map-title">🌍 3D Interactive Star Map</div>
        <div class="map-sub">Solar neighborhood · Gaia DR3 · <?= count($stars) ?> stars · <?= count($planets) ?> habitable planets</div>
    </div>
    <div class="map-controls">
        <button class="map-btn active" id="btnStars" onclick="toggleLayer('stars',this)">⭐ Stars</button>
        <button class="map-btn active" id="btnPlanets" onclick="toggleLayer('planets',this)">🪐 Planets</button>
        <button class="map-btn active" id="btnLabels" onclick="toggleLayer('labels',this)">🏷 Labels</button>
        <button class="map-btn" onclick="resetCamera()">🎯 Reset View</button>
    </div>
</div>

<!-- Stats -->
<div class="stats-bar">
    <div class="stat-pill">
        <div class="stat-pill-dot" style="background:#f59e0b;box-shadow:0 0 6px #f59e0b;"></div>
        <span style="color:var(--text-dim)"><?= count($stars) ?> Nearby Stars</span>
    </div>
    <div class="stat-pill">
        <div class="stat-pill-dot" style="background:#10b981;box-shadow:0 0 6px #10b981;"></div>
        <span style="color:var(--text-dim)"><?= count($planets) ?> Habitable Planets</span>
    </div>
    <div class="stat-pill">
        <div class="stat-pill-dot" style="background:#7c3aed;box-shadow:0 0 6px #7c3aed;"></div>
        <span style="color:var(--text-dim)">☀️ Sun (Center)</span>
    </div>
</div>

<!-- Info Panel -->
<div class="info-panel" id="infoPanel">
    <button class="info-close" onclick="closeInfo()">✕</button>
    <div class="info-type" id="infoType">STAR</div>
    <div class="info-name" id="infoName">—</div>
    <div id="infoRows"></div>
    <div id="infoBadge"></div>
</div>

<!-- Legend -->
<div class="legend">
    <div class="legend-title">Legend</div>
    <div class="legend-row"><div class="legend-dot" style="background:#fffde7;box-shadow:0 0 8px #fff9c4;"></div>☀️ Sun</div>
    <div class="legend-row"><div class="legend-dot" style="background:#f59e0b;box-shadow:0 0 6px #f59e0b;"></div>Nearby Stars</div>
    <div class="legend-row"><div class="legend-dot" style="background:#10b981;box-shadow:0 0 6px #10b981;"></div>Habitable Planets</div>
    <div class="legend-row"><div class="legend-dot" style="background:#7c3aed;box-shadow:0 0 5px #7c3aed;"></div>Other Exoplanets</div>
</div>

<div class="controls-hint">🖱 Drag to rotate · Scroll to zoom · Click objects for info</div>

<script>
// ============================================
// 3D Star Map — Three.js
// ============================================
const STARS   = <?= $starsJson ?>;
const PLANETS = <?= $planetsJson ?>;

const canvas   = document.getElementById('starMapCanvas');
const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
renderer.setSize(window.innerWidth, window.innerHeight);
renderer.setPixelRatio(window.devicePixelRatio);

const scene  = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 2000);
camera.position.set(0, 30, 80);

// Layers
const starGroup   = new THREE.Group();
const planetGroup = new THREE.Group();
const labelGroup  = new THREE.Group();
scene.add(starGroup, planetGroup, labelGroup);

// --- Background stars (decorative) ---
const bgGeo = new THREE.BufferGeometry();
const bgVerts = [];
for (let i = 0; i < 3000; i++) {
    bgVerts.push(
        (Math.random() - 0.5) * 1800,
        (Math.random() - 0.5) * 1800,
        (Math.random() - 0.5) * 1800
    );
}
bgGeo.setAttribute('position', new THREE.Float32BufferAttribute(bgVerts, 3));
const bgMat  = new THREE.PointsMaterial({ color: 0xffffff, size: 0.4, transparent: true, opacity: 0.5 });
scene.add(new THREE.Points(bgGeo, bgMat));

// --- Sun ---
const sunGeo  = new THREE.SphereGeometry(3, 32, 32);
const sunMat  = new THREE.MeshBasicMaterial({ color: 0xfff9c4 });
const sun     = new THREE.Mesh(sunGeo, sunMat);
sun.userData  = { type: 'star', name: 'Sun ☀️', data: { Distance: '0 ly (center)', Type: 'G-type', Temperature: '5,778 K', Planets: '8 confirmed' } };
starGroup.add(sun);

// Glow
const glowGeo = new THREE.SphereGeometry(4.5, 32, 32);
const glowMat = new THREE.MeshBasicMaterial({ color: 0xfff176, transparent: true, opacity: 0.15 });
starGroup.add(new THREE.Mesh(glowGeo, glowMat));

// --- Scale: 1 unit = 1 light year ---
function getPos(index, total, distance) {
    // Spiral placement
    const phi   = Math.acos(1 - 2 * (index + 0.5) / total);
    const theta = Math.PI * (1 + Math.sqrt(5)) * index;
    const r     = Math.min(distance * 4, 120);
    return {
        x: r * Math.sin(phi) * Math.cos(theta),
        y: r * Math.sin(phi) * Math.sin(theta) * 0.4,
        z: r * Math.cos(phi),
    };
}

const starColors = {
    'M-dwarf': 0xef4444,
    'G-type':  0xfbbf24,
    'K-type':  0xf97316,
    'A-type':  0x93c5fd,
    'F-type':  0xfde68a,
};

// --- Add Stars ---
const starMeshes = [];
STARS.forEach((s, i) => {
    const pos   = getPos(i, STARS.length, s.distance_ly);
    const size  = s.distance_ly < 6 ? 2.2 : s.distance_ly < 10 ? 1.6 : 1.2;
    const color = starColors[s.star_type] || 0xfbbf24;

    const geo  = new THREE.SphereGeometry(size, 16, 16);
    const mat  = new THREE.MeshBasicMaterial({ color });
    const mesh = new THREE.Mesh(geo, mat);
    mesh.position.set(pos.x, pos.y, pos.z);
    mesh.userData = {
        type: 'star',
        name: s.name,
        data: {
            Distance:      s.distance_ly + ' ly',
            Type:          s.star_type,
            Temperature:   s.temperature_k + ' K',
            Constellation: s.constellation,
            'Has Planets': s.has_planets ? 'Yes ✓' : 'No',
        }
    };
    starGroup.add(mesh);
    starMeshes.push(mesh);

    // Distance ring for close stars
    if (s.distance_ly <= 6) {
        const ringGeo = new THREE.RingGeometry(size + 0.3, size + 0.7, 32);
        const ringMat = new THREE.MeshBasicMaterial({ color, transparent: true, opacity: 0.3, side: THREE.DoubleSide });
        const ring    = new THREE.Mesh(ringGeo, ringMat);
        ring.position.copy(mesh.position);
        ring.lookAt(camera.position);
        starGroup.add(ring);
    }
});

// --- Add Habitable Planets ---
const planetMeshes = [];
PLANETS.forEach((p, i) => {
    const angle = (i / PLANETS.length) * Math.PI * 2;
    const r     = Math.min(p.distance_ly * 3.5, 100) + 15;
    const x     = r * Math.cos(angle);
    const z     = r * Math.sin(angle);
    const y     = (Math.random() - 0.5) * 20;

    const geo  = new THREE.SphereGeometry(1.0, 16, 16);
    const mat  = new THREE.MeshBasicMaterial({ color: 0x10b981 });
    const mesh = new THREE.Mesh(geo, mat);
    mesh.position.set(x, y, z);
    mesh.userData = {
        type: 'planet',
        name: p.name,
        data: {
            'Host Star':   p.host_star,
            Distance:      p.distance_ly + ' ly',
            'Radius':      p.radius_earth + ' R⊕',
            'Temperature': p.temperature_k + ' K',
            Atmosphere:    p.atmosphere,
            Discovered:    p.year_discovered,
        }
    };
    planetGroup.add(mesh);
    planetMeshes.push(mesh);
});

// --- Canvas Labels ---
function makeLabel(text, position, color = '#ffffff') {
    const canvas2 = document.createElement('canvas');
    canvas2.width  = 256;
    canvas2.height = 48;
    const ctx = canvas2.getContext('2d');
    ctx.font = '18px Space Grotesk, sans-serif';
    ctx.fillStyle = color;
    ctx.fillText(text, 4, 32);

    const tex  = new THREE.CanvasTexture(canvas2);
    const mat  = new THREE.SpriteMaterial({ map: tex, transparent: true, opacity: 0.85 });
    const sp   = new THREE.Sprite(mat);
    sp.scale.set(12, 3, 1);
    sp.position.set(position.x + 2, position.y + 3, position.z);
    return sp;
}

// Sun label
const sunLabel = makeLabel('☀️ Sun', { x: 0, y: 3, z: 0 }, '#fff9c4');
labelGroup.add(sunLabel);

// Star labels
STARS.slice(0, 8).forEach((s, i) => {
    const pos = getPos(i, STARS.length, s.distance_ly);
    labelGroup.add(makeLabel(s.name, pos, '#fbbf24'));
});

// Planet labels
PLANETS.slice(0, 6).forEach((p, i) => {
    const angle = (i / PLANETS.length) * Math.PI * 2;
    const r     = Math.min(p.distance_ly * 3.5, 100) + 15;
    labelGroup.add(makeLabel(p.name, { x: r * Math.cos(angle) + 2, y: 3, z: r * Math.sin(angle) }, '#6ee7b7'));
});

// --- Orbit lines (Sun's grid) ---
const gridGeo = new THREE.CircleGeometry(50, 64);
gridGeo.vertices && gridGeo.vertices.shift();
const gridMat  = new THREE.LineBasicMaterial({ color: 0x1e293b, transparent: true, opacity: 0.3 });
[20, 40, 60, 80, 100].forEach(r => {
    const geo = new THREE.RingGeometry(r - 0.1, r + 0.1, 64);
    const mat = new THREE.MeshBasicMaterial({ color: 0x1e293b, transparent: true, opacity: 0.2, side: THREE.DoubleSide });
    const ring = new THREE.Mesh(geo, mat);
    ring.rotation.x = -Math.PI / 2;
    scene.add(ring);
});

// --- Raycaster (click) ---
const raycaster = new THREE.Raycaster();
const mouse     = new THREE.Vector2();
const allMeshes = [sun, ...starMeshes, ...planetMeshes];

canvas.addEventListener('click', (e) => {
    mouse.x = (e.clientX / window.innerWidth)  *  2 - 1;
    mouse.y = (e.clientY / window.innerHeight) * -2 + 1;
    raycaster.setFromCamera(mouse, camera);
    const hits = raycaster.intersectObjects(allMeshes);
    if (hits.length > 0) showInfo(hits[0].object.userData);
});

function showInfo(ud) {
    document.getElementById('infoType').textContent = ud.type.toUpperCase();
    document.getElementById('infoName').textContent = ud.name;
    const rows = Object.entries(ud.data || {}).map(([k,v]) =>
        `<div class="info-row"><span class="info-key">${k}</span><span class="info-val">${v}</span></div>`
    ).join('');
    document.getElementById('infoRows').innerHTML = rows;
    document.getElementById('infoBadge').innerHTML = ud.type === 'planet'
        ? '<span class="info-badge badge-hab">🌍 Habitable Zone</span>'
        : '<span class="info-badge badge-star">⭐ Star</span>';
    document.getElementById('infoPanel').classList.add('show');
}
function closeInfo() {
    document.getElementById('infoPanel').classList.remove('show');
}

// --- Layer toggles ---
const layerState = { stars: true, planets: true, labels: true };
function toggleLayer(layer, btn) {
    layerState[layer] = !layerState[layer];
    btn.classList.toggle('active', layerState[layer]);
    if (layer === 'stars')   starGroup.visible   = layerState[layer];
    if (layer === 'planets') planetGroup.visible = layerState[layer];
    if (layer === 'labels')  labelGroup.visible  = layerState[layer];
}

// --- Camera Controls (Orbit) ---
let isDragging = false, prevMouse = { x: 0, y: 0 };
let theta = 0.3, phi = 1.0, radius = 80;
const target = new THREE.Vector3(0, 0, 0);

canvas.addEventListener('mousedown', e => { isDragging = true; prevMouse = { x: e.clientX, y: e.clientY }; });
canvas.addEventListener('mouseup',   () => isDragging = false);
canvas.addEventListener('mousemove', e => {
    if (!isDragging) return;
    const dx = e.clientX - prevMouse.x;
    const dy = e.clientY - prevMouse.y;
    theta -= dx * 0.005;
    phi    = Math.max(0.2, Math.min(Math.PI - 0.2, phi - dy * 0.005));
    prevMouse = { x: e.clientX, y: e.clientY };
});
canvas.addEventListener('wheel', e => {
    radius = Math.max(20, Math.min(300, radius + e.deltaY * 0.1));
});

// Touch support
canvas.addEventListener('touchstart', e => {
    isDragging = true;
    prevMouse = { x: e.touches[0].clientX, y: e.touches[0].clientY };
});
canvas.addEventListener('touchend', () => isDragging = false);
canvas.addEventListener('touchmove', e => {
    if (!isDragging) return;
    const dx = e.touches[0].clientX - prevMouse.x;
    const dy = e.touches[0].clientY - prevMouse.y;
    theta -= dx * 0.005;
    phi    = Math.max(0.2, Math.min(Math.PI - 0.2, phi - dy * 0.005));
    prevMouse = { x: e.touches[0].clientX, y: e.touches[0].clientY };
});

function resetCamera() {
    theta = 0.3; phi = 1.0; radius = 80;
}

// --- Animate ---
let time = 0;
function animate() {
    requestAnimationFrame(animate);
    time += 0.005;

    // Orbit camera
    camera.position.x = target.x + radius * Math.sin(phi) * Math.cos(theta);
    camera.position.y = target.y + radius * Math.cos(phi);
    camera.position.z = target.z + radius * Math.sin(phi) * Math.sin(theta);
    camera.lookAt(target);

    // Pulse sun
    const pulse = 1 + 0.05 * Math.sin(time * 2);
    sun.scale.setScalar(pulse);

    // Slow auto-rotate when idle
    if (!isDragging) theta += 0.0008;

    renderer.render(scene, camera);
}
animate();

// Resize
window.addEventListener('resize', () => {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
});
</script>
</body>
</html>