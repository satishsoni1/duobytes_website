<?php
/* Read .xlsx / .csv into rows (first row = headers). No external libraries. */
declare(strict_types=1);

function read_sheet(string $path, string $origName): array {
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if ($ext === 'csv' || $ext === 'txt') return read_csv($path);
    if ($ext === 'xlsx') return read_xlsx($path);
    throw new RuntimeException('Please upload an .xlsx or .csv file (old .xls is not supported – open it in Excel and "Save As" .xlsx).');
}

function read_csv(string $path): array {
    $txt = file_get_contents($path);
    $txt = preg_replace('/^\xEF\xBB\xBF/', '', $txt);               // strip BOM
    if (!mb_check_encoding($txt, 'UTF-8')) $txt = mb_convert_encoding($txt, 'UTF-8', 'Windows-1252');
    $first = strtok($txt, "\n");
    $delim = substr_count($first, ';') > substr_count($first, ',') ? ';' : (substr_count($first, "\t") > substr_count($first, ',') ? "\t" : ',');
    $rows = []; $fh = fopen('php://temp', 'r+'); fwrite($fh, $txt); rewind($fh);
    while (($r = fgetcsv($fh, 0, $delim, '"', '\\')) !== false) { if (array_filter($r, fn($v) => trim((string)$v) !== '')) $rows[] = $r; }
    return $rows;
}

function read_xlsx(string $path): array {
    if (!class_exists('ZipArchive')) throw new RuntimeException('ZipArchive PHP extension missing – upload a .csv instead.');
    $z = new ZipArchive(); if ($z->open($path) !== true) throw new RuntimeException('Could not open the Excel file.');
    $shared = [];
    if (($x = $z->getFromName('xl/sharedStrings.xml')) !== false) {
        $sx = simplexml_load_string($x);
        foreach ($sx->si as $si) {
            if (isset($si->t)) $shared[] = (string)$si->t;
            else { $s = ''; foreach ($si->r as $r) $s .= (string)$r->t; $shared[] = $s; }
        }
    }
    // first worksheet in workbook order
    $sheetFile = 'xl/worksheets/sheet1.xml';
    if (($wb = $z->getFromName('xl/workbook.xml')) !== false && ($rels = $z->getFromName('xl/_rels/workbook.xml.rels')) !== false) {
        $wbx = simplexml_load_string($wb); $wbx->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $first = $wbx->sheets->sheet[0] ?? null;
        if ($first) {
            $rid = (string)$first->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
            foreach (simplexml_load_string($rels)->Relationship as $rel)
                if ((string)$rel['Id'] === $rid) { $t = ltrim((string)$rel['Target'], '/'); $sheetFile = str_starts_with($t, 'xl/') ? $t : 'xl/' . $t; }
        }
    }
    $xml = $z->getFromName($sheetFile); $z->close();
    if ($xml === false) throw new RuntimeException('No worksheet found in the Excel file.');
    $sx = simplexml_load_string($xml); $rows = [];
    foreach ($sx->sheetData->row as $row) {
        $r = [];
        foreach ($row->c as $c) {
            $ref = (string)$c['r']; $col = col_index(preg_replace('/\d+/', '', $ref));
            $t = (string)$c['t'];
            if ($t === 's') $v = $shared[(int)$c->v] ?? '';
            elseif ($t === 'inlineStr') $v = (string)$c->is->t;
            else $v = (string)$c->v;
            $r[$col] = $v;
        }
        if (!$r) continue;
        $max = max(array_keys($r)); $line = [];
        for ($i = 0; $i <= $max; $i++) $line[] = $r[$i] ?? '';
        if (array_filter($line, fn($v) => trim($v) !== '')) $rows[] = $line;
    }
    return $rows;
}
function col_index(string $letters): int { $n = 0; foreach (str_split(strtoupper($letters)) as $ch) $n = $n * 26 + (ord($ch) - 64); return $n - 1; }

/** Guess which column holds what, from header names */
function guess_columns(array $headers): array {
    $map = ['email' => null, 'name' => null, 'institution' => null, 'city' => null, 'phone' => null];
    $pat = [
        'email' => '/e-?mail|mail id|email id/i',
        'name' => '/^(contact|principal|person|owner|director|doctor|name|contact person|full name|first name)/i',
        'institution' => '/school|college|institut|organi[sz]ation|company|hospital|clinic|firm|business|account/i',
        'city' => '/city|town|district|location|place/i',
        'phone' => '/phone|mobile|contact no|whatsapp|cell/i',
    ];
    foreach ($headers as $i => $h) {
        $h = trim((string)$h);
        foreach ($pat as $k => $p) if ($map[$k] === null && preg_match($p, $h)) { if ($k === 'name' && preg_match($pat['institution'], $h)) continue; $map[$k] = $i; break; }
    }
    return $map;
}
