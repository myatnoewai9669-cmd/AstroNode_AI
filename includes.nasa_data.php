<?php
require_once 'C:/xampp/htdocs/as/includes.db.php';
require_once 'C:/xampp/htdocs/as/config.php';
function routeQuery(string $query): array {
    $q = strtolower($query);
    if (str_contains($q, 'star') || str_contains($q, 'closest') || str_contains($q, 'nearest') || str_contains($q, 'light year')) {
        return queryStars($q);
    }
    if (str_contains($q, 'supernova') || str_contains($q, 'remnant')) {
        return supernovaData();
    }
    return queryExoplanets($q);
}

function queryExoplanets(string $q): array {
    $db = getDB();
    $where = ['1=1'];
    $params = [];

    if (preg_match('/after (\d{4})/', $q, $m) || preg_match('/(\d{4})/', $q, $m)) {
        $yr = (int)$m[1];
        if ($yr >= 2000 && $yr <= 2025) {
            $where[] = 'year_discovered >= :yr';
            $params[':yr'] = $yr;
        }
    }
    if (str_contains($q, 'habitable') || str_contains($q, 'earth-like')) {
        $where[] = 'habitable_zone = 1';
    }
    if (str_contains($q, 'atmosphere') || str_contains($q, 'water')) {
        $where[] = "atmosphere IN ('detected','possible')";
    }
    if (preg_match('/within (\d+) light/', $q, $m)) {
        $where[] = 'distance_ly <= :dist';
        $params[':dist'] = (int)$m[1];
    }
    if (str_contains($q, 'jwst') || str_contains($q, 'james webb')) {
        $where[] = "telescope LIKE '%JWST%'";
    }
    if (str_contains($q, 'gas giant') || str_contains($q, 'large planet')) {
        $where[] = 'radius_earth > 1.5';
    }

    $sql = 'SELECT * FROM exoplanets WHERE ' . implode(' AND ', $where) . ' ORDER BY year_discovered DESC LIMIT 15';
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $chartData = array_map(fn($r) => [
        'x' => (float)$r['distance_ly'],
        'y' => (float)$r['radius_earth'],
        'label' => $r['name']
    ], $rows);

    $tableRows = array_map(fn($r) => [
        $r['name'], $r['year_discovered'],
        $r['radius_earth'] . ' R⊕',
        $r['distance_ly'] . ' ly',
        $r['habitable_zone'] ? '✓' : '✗',
        $r['atmosphere']
    ], array_slice($rows, 0, 8));

    return [
        'source' => 'NASA Exoplanet Archive',
        'count'  => count($rows),
        'chart'  => ['type' => 'scatter', 'data' => $chartData, 'xLabel' => 'Distance (ly)', 'yLabel' => 'Radius (R⊕)'],
        'table'  => ['headers' => ['Name','Year','Radius','Distance','Hab. Zone','Atmosphere'], 'rows' => $tableRows],
        'tags'   => ['Exoplanets','NASA','TESS','Kepler','JWST'],
    ];
}

function queryStars(string $q): array {
    $db = getDB();
    $maxDist = 50;
    if (preg_match('/(\d+) light/', $q, $m)) $maxDist = (int)$m[1];

    $stmt = $db->prepare('SELECT * FROM stars WHERE distance_ly <= :d ORDER BY distance_ly ASC LIMIT 12');
    $stmt->execute([':d' => $maxDist]);
    $rows = $stmt->fetchAll();

    $chartData   = array_map(fn($r) => (float)$r['distance_ly'], $rows);
    $chartLabels = array_map(fn($r) => explode(' ', $r['name'])[0], $rows);

    $tableRows = array_map(fn($r) => [
        $r['name'], $r['distance_ly'] . ' ly',
        $r['star_type'], $r['temperature_k'] . ' K',
        $r['has_planets'] ? 'Yes' : 'No'
    ], $rows);

    return [
        'source' => 'Hipparcos / Gaia DR3',
        'count'  => count($rows),
        'chart'  => ['type' => 'bar', 'labels' => $chartLabels, 'data' => $chartData, 'yLabel' => 'Distance (ly)'],
        'table'  => ['headers' => ['Star','Distance','Type','Temperature','Planets?'], 'rows' => $tableRows],
        'tags'   => ['Stars','Gaia','Hipparcos'],
    ];
}

function supernovaData(): array {
    return [
        'source' => 'Chandra / ESA / JWST',
        'count'  => 5,
        'chart'  => [
            'type'   => 'bar',
            'labels' => ['Cas A','Crab','SN 1987A','N132D','G292'],
            'data'   => [340, 970, 37, 3150, 1600],
            'yLabel' => 'Age (years)'
        ],
        'table'  => [
            'headers' => ['Name','Age (yr)','Distance','Telescope'],
            'rows'    => [
                ['Cassiopeia A', '340',  '11,000 ly',  'Chandra/JWST'],
                ['Crab Nebula',  '970',  '6,500 ly',   'Hubble/Chandra'],
                ['SN 1987A',     '37',   '168,000 ly', 'JWST/HST'],
                ['N132D',        '3150', '160,000 ly', 'Chandra'],
                ['G292.0+1.8',   '1600', '20,000 ly',  'Chandra'],
            ]
        ],
        'tags' => ['Supernova','Chandra','JWST'],
    ];
}