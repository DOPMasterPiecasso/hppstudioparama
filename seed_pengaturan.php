<?php
require "config/db.php";
$db = getDB();

$db->exec("
CREATE TABLE IF NOT EXISTS tbl_overhead (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(50) UNIQUE,
    label VARCHAR(255),
    amount INT
);
CREATE TABLE IF NOT EXISTS tbl_multipliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(50),
    key_name VARCHAR(50) UNIQUE,
    label VARCHAR(255),
    value FLOAT
);
");

$db->exec("TRUNCATE TABLE tbl_overhead");
$db->exec("TRUNCATE TABLE tbl_multipliers");

$oh = [
    'total' => ['Total Overhead', 65540000],
    'marketing' => ['Marketing & Pemasaran', 12750000],
    'creative' => ['Tim Kreatif (Desain, Video)', 7670000],
    'designer' => ['Fotografer & Tim Lapangan', 16700000],
    'pm' => ['Project Manager', 7200000],
    'sosmed' => ['Sosmed & Konten', 6430000],
    'freelance' => ['Cadangan Freelance', 3204000],
    'ops' => ['Ops (Tools, Transport, Server)', 11586000]
];

foreach($oh as $k => $v) {
    $db->prepare("INSERT INTO tbl_overhead (key_name, label, amount) VALUES (?,?,?)")->execute([$k, $v[0], $v[1]]);
}

$mults = [
    ['cetak', 'handy', 'Handy Book A4+', 1.0],
    ['cetak', 'minimal', 'Minimal Book SQ', 0.95],
    ['cetak', 'large', 'Large Book B4', 1.15],
    ['alc', 'ebook', 'E-Book Package (%)', 72],
    ['alc', 'editcetak', 'Edit+Desain+Cetak (%)', 62],
    ['alc', 'desain', 'Desain Only (%)', 22],
    ['alc', 'cetakonly', 'Cetak Only (%)', 30]
];

foreach ($mults as $m) {
    $db->prepare("INSERT INTO tbl_multipliers (category, key_name, label, value) VALUES (?,?,?,?)")->execute([$m[0], $m[1], $m[2], $m[3]]);
}
echo "tbl_overhead and tbl_multipliers done\n";
