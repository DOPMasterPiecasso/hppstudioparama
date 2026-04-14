<?php
require "config/db.php";
$db = getDB();

// 1. Create Tables
$db->exec("
CREATE TABLE IF NOT EXISTS tbl_fs_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pkg VARCHAR(50),
    min_siswa INT,
    max_siswa INT,
    harga INT,
    pages INT
);
CREATE TABLE IF NOT EXISTS tbl_cetak_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    range_idx INT,
    label VARCHAR(50),
    min_siswa INT,
    max_siswa INT,
    pages INT,
    harga INT
);
CREATE TABLE IF NOT EXISTS tbl_addons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(50),
    sub_id VARCHAR(50),
    name VARCHAR(255),
    type VARCHAR(50),
    price INT DEFAULT 0,
    min_qty INT DEFAULT 0,
    max_qty INT DEFAULT 9999
);
CREATE TABLE IF NOT EXISTS tbl_grad_packages (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255),
    price INT,
    description TEXT,
    color VARCHAR(50)
);
CREATE TABLE IF NOT EXISTS tbl_grad_addons (
    id VARCHAR(50) PRIMARY KEY,
    category VARCHAR(50),
    name VARCHAR(255),
    price INT
);
");

$db->exec("TRUNCATE TABLE tbl_fs_prices");
$db->exec("TRUNCATE TABLE tbl_cetak_prices");
$db->exec("TRUNCATE TABLE tbl_addons");
$db->exec("TRUNCATE TABLE tbl_grad_packages");
$db->exec("TRUNCATE TABLE tbl_grad_addons");

// RAW DATA
$fs = json_decode('{"handy":[[30,50,465000,30],[51,75,415000,30],[76,100,370000,45],[101,125,350000,55],[126,150,335000,60],[151,175,315000,65],[176,200,295000,75],[201,225,260000,80],[226,250,250000,80],[251,275,240000,90],[276,300,230000,100],[300,325,220000,100],[326,350,210000,120],[351,375,200000,120],[376,400,190000,135],[401,425,185000,135],[426,450,165000,145],[451,475,175000,150],[476,500,150000,160]],"minimal":[[30,50,450000,30],[51,75,400000,30],[76,100,355000,45],[101,125,335000,55],[126,150,320000,60],[151,175,300000,65],[176,200,280000,75],[201,225,245000,80],[226,250,235000,80],[251,275,240000,90],[276,300,215000,100],[300,325,205000,100],[326,350,195000,120],[351,375,185000,120],[376,400,180000,135],[401,425,170000,135],[426,450,160000,145],[451,475,150000,150],[476,500,140000,160]],"large":[[30,50,480000,30],[51,75,430000,30],[76,100,405000,45],[101,125,365000,55],[126,150,350000,60],[151,175,330000,65],[176,200,310000,75],[201,225,275000,80],[226,250,265000,80],[251,275,255000,90],[276,300,245000,100],[300,325,235000,100],[326,350,225000,120],[351,375,215000,120],[376,400,205000,135],[401,425,195000,135],[426,450,175000,145],[451,475,165000,150],[476,500,155000,160]]}', true);
$addon = json_decode('{"finishing":[{"id":"binding","name":"Binding Paku/Jepang/Spiral","type":"flat","tiers":[[25,75,50000],[76,150,35000],[151,9999,30000]]},{"id":"popup","name":"Pop Up 2D","type":"flat","tiers":[[25,75,55000],[76,150,40000],[151,9999,35000]]},{"id":"tunnel","name":"Cover Tunnel","type":"flat","tiers":[[25,75,75000],[76,150,60000],[151,9999,50000]]},{"id":"klip","name":"Cover Klip/Cetekan","type":"flat","tiers":[[25,75,15000],[76,150,10000],[151,9999,8000]]},{"id":"covbahan","name":"Cover Bahan","type":"flat","tiers":[[25,75,55000],[76,150,40000],[151,9999,35000]]}],"kertas":[{"id":"ivory","name":"Ivory Paper","type":"per_hal","tiers":[[25,50,450],[51,100,250],[101,150,200],[151,9999,150]]},{"id":"laminasi","name":"Laminasi Paper","type":"per_hal","tiers":[[25,50,600],[51,100,450],[101,150,400],[151,9999,350]]}],"halaman":[{"id":"extrahal","name":"Halaman Tambahan","type":"extra_hal","tiers":[[25,50,3000],[51,100,2000],[101,150,1300],[151,9999,1000]]}],"video":[{"id":"drone","name":"Drone Video (1-2 mnt)","type":"flat_video","price":1500000},{"id":"docudrama","name":"Docudrama Video (5-10 mnt)","type":"flat_video","price":3000000}],"pkg1":[{"id":"slidebox","name":"Slide Box","type":"flat","tiers":[[25,50,45000],[51,100,40000],[101,150,35000],[151,200,30000],[201,9999,25000]]},{"id":"stdbox1","name":"Standart Box 1","type":"flat","tiers":[[25,50,150000],[51,100,95000],[101,150,80000],[151,200,70000],[201,9999,65000]]},{"id":"stdbox2","name":"Standart Box 2","type":"flat","tiers":[[25,50,150000],[51,100,100000],[101,150,80000],[151,200,75000],[201,9999,70000]]},{"id":"hardbox","name":"Hard Box 3 (Akrilik)","type":"flat","tiers":[[25,50,125000],[51,100,100000],[101,150,90000],[151,200,80000],[201,9999,75000]]}],"pkg2":[{"id":"cbox1","name":"Custom Box 1","type":"flat","tiers":[[25,50,200000],[51,100,170000],[101,150,130000],[151,200,120000],[201,9999,110000]]},{"id":"cbox2","name":"Custom Box 2","type":"flat","tiers":[[25,50,165000],[51,100,150000],[101,150,130000],[151,200,120000],[201,9999,110000]]},{"id":"cbox3","name":"Custom Box 3","type":"flat","tiers":[[25,50,200000],[51,100,170000],[101,150,130000],[151,200,120000],[201,9999,110000]]},{"id":"cbox4","name":"Custom Box 4","type":"flat","tiers":[[25,50,200000],[51,100,170000],[101,150,140000],[151,200,130000],[201,9999,120000]]},{"id":"cbox5","name":"Custom Box 5","type":"flat","tiers":[[25,50,200000],[51,100,170000],[101,150,145000],[151,200,135000],[201,9999,130000]]}]}', true);
$cetakBase = json_decode('[{"lo":30,"hi":50,"label":"30-50 siswa","pages":{"30":92000,"45":102000,"60":115000,"65":127000,"75":140000,"80":140000,"90":152000,"100":165000,"110":176000,"120":176000,"135":176000,"150":176000,"160":176000}},{"lo":51,"hi":75,"label":"51-75 siswa","pages":{"30":80000,"45":90000,"60":100000,"65":110000,"75":122000,"80":122000,"90":134000,"100":145000,"110":158000,"120":162000,"135":165000,"150":168000,"160":170000}},{"lo":76,"hi":100,"label":"76-100 siswa","pages":{"30":70000,"45":80000,"60":90000,"65":98000,"75":108000,"80":108000,"90":118000,"100":130000,"110":140000,"120":145000,"135":150000,"150":155000,"160":160000}},{"lo":101,"hi":125,"label":"101-125 siswa","pages":{"30":62000,"45":72000,"60":82000,"65":88000,"75":97000,"80":97000,"90":106000,"100":116000,"110":126000,"120":130000,"135":135000,"150":140000,"160":145000}},{"lo":126,"hi":150,"label":"126-150 siswa","pages":{"30":58000,"45":68000,"60":76000,"65":82000,"75":90000,"80":90000,"90":98000,"100":108000,"110":118000,"120":122000,"135":127000,"150":132000,"160":137000}},{"lo":151,"hi":175,"label":"151-175 siswa","pages":{"30":54000,"45":63000,"60":71000,"65":76000,"75":84000,"80":84000,"90":91000,"100":100000,"110":109000,"120":113000,"135":118000,"150":123000,"160":128000}},{"lo":176,"hi":200,"label":"176-200 siswa","pages":{"30":50000,"45":59000,"60":66000,"65":71000,"75":78000,"80":78000,"90":85000,"100":93000,"110":101000,"120":105000,"135":110000,"150":115000,"160":119000}},{"lo":201,"hi":225,"label":"201-225 siswa","pages":{"30":47000,"45":55000,"60":62000,"65":66000,"75":73000,"80":73000,"90":79000,"100":87000,"110":95000,"120":98000,"135":103000,"150":107000,"160":111000}},{"lo":226,"hi":250,"label":"226-250 siswa","pages":{"30":44000,"45":52000,"60":58000,"65":62000,"75":68000,"80":68000,"90":74000,"100":81000,"110":88000,"120":92000,"135":96000,"150":100000,"160":104000}},{"lo":251,"hi":275,"label":"251-275 siswa","pages":{"30":41000,"45":49000,"60":55000,"65":58000,"75":64000,"80":64000,"90":70000,"100":76000,"110":83000,"120":86000,"135":90000,"150":94000,"160":98000}},{"lo":276,"hi":300,"label":"276-300 siswa","pages":{"30":39000,"45":46000,"60":52000,"65":55000,"75":60000,"80":60000,"90":66000,"100":72000,"110":78000,"120":81000,"135":85000,"150":89000,"160":92000}},{"lo":301,"hi":325,"label":"301-325 siswa","pages":{"30":37000,"45":44000,"60":49000,"65":52000,"75":57000,"80":57000,"90":62000,"100":68000,"110":74000,"120":77000,"135":80000,"150":84000,"160":87000}},{"lo":326,"hi":350,"label":"326-350 siswa","pages":{"30":35000,"45":42000,"60":47000,"65":50000,"75":54000,"80":54000,"90":59000,"100":65000,"110":70000,"120":73000,"135":76000,"150":80000,"160":83000}},{"lo":351,"hi":375,"label":"351-375 siswa","pages":{"30":33000,"45":40000,"60":45000,"65":47000,"75":52000,"80":52000,"90":56000,"100":62000,"110":67000,"120":70000,"135":73000,"150":76000,"160":79000}},{"lo":376,"hi":400,"label":"376-400 siswa","pages":{"30":31000,"45":38000,"60":42000,"65":45000,"75":49000,"80":49000,"90":53000,"100":58000,"110":63000,"120":66000,"135":69000,"150":72000,"160":75000}},{"lo":401,"hi":425,"label":"401-425 siswa","pages":{"30":30000,"45":36000,"60":40000,"65":42000,"75":46000,"80":46000,"90":50000,"100":55000,"110":60000,"120":62000,"135":65000,"150":68000,"160":71000}},{"lo":426,"hi":450,"label":"426-450 siswa","pages":{"30":28000,"45":34000,"60":38000,"65":40000,"75":44000,"80":44000,"90":48000,"100":52000,"110":57000,"120":59000,"135":62000,"150":65000,"160":67000}},{"lo":451,"hi":475,"label":"451-475 siswa","pages":{"30":27000,"45":32000,"60":36000,"65":38000,"75":42000,"80":42000,"90":45000,"100":50000,"110":54000,"120":56000,"135":59000,"150":62000,"160":64000}},{"lo":476,"hi":500,"label":"476-500 siswa","pages":{"30":26000,"45":31000,"60":34000,"65":36000,"75":40000,"80":40000,"90":43000,"100":47000,"110":51000,"120":53000,"135":56000,"150":59000,"160":61000}}]', true);
$grad = json_decode('{"packages":[{"id":"gphv","name":"Photo & Video","price":4500000,"desc":"2 Fotografer + 1 Videografer","color":"acc"},{"id":"gvideo","name":"Video Only","price":2000000,"desc":"1 Videografer","color":""},{"id":"gphoto","name":"Photo Only","price":2750000,"desc":"2 Fotografer","color":""},{"id":"gbooth","name":"Photo Booth","price":3850000,"desc":"1-2 Crew profesional","color":""},{"id":"g360","name":"Glamation 360","price":4100000,"desc":"1-2 Crew profesional","color":""},{"id":"gcomplete","name":"Complete Package","price":7750000,"desc":"Photo + Video","color":"feat"}],"addons":[{"id":"gvideo_add","name":"Tambah 1 Videografer","price":1500000},{"id":"gphoto_add","name":"Tambah 1 Fotografer","price":1250000},{"id":"gbooth_add","name":"Tambah 1 Jam Photobooth/360","price":500000},{"id":"gwork_add","name":"Tambah 1 Jam Kerja/Orang","price":350000}],"cetak":[{"id":"g4r","name":"Cetak Foto 4R","price":4000},{"id":"g8r","name":"Cetak Foto 8R","price":8000},{"id":"g10r","name":"Cetak Foto 10R","price":15000},{"id":"g12r","name":"Cetak Foto 12R","price":20000}]}', true);

// SEED tbl_fs_prices
$stmtFs = $db->prepare("INSERT INTO tbl_fs_prices (pkg, min_siswa, max_siswa, harga, pages) VALUES (?,?,?,?,?)");
foreach($fs as $pkg => $tiers) {
    foreach($tiers as $tier) {
        $stmtFs->execute([$pkg, $tier[0], $tier[1], $tier[2], $tier[3]]);
    }
}

// SEED tbl_cetak_prices
$stmtCetak = $db->prepare("INSERT INTO tbl_cetak_prices (range_idx, label, min_siswa, max_siswa, pages, harga) VALUES (?,?,?,?,?,?)");
foreach($cetakBase as $idx => $rn) {
    foreach($rn['pages'] as $pgs => $hrg) {
        $stmtCetak->execute([$idx, $rn['label'], $rn['lo'], $rn['hi'], $pgs, $hrg]);
    }
}

// SEED tbl_addons
$stmtAddon = $db->prepare("INSERT INTO tbl_addons (category, sub_id, name, type, min_qty, max_qty, price) VALUES (?,?,?,?,?,?,?)");
foreach($addon as $cat => $items) {
    foreach($items as $item) {
        if (isset($item['tiers'])) {
            foreach($item['tiers'] as $tier) {
                // $tier is [min, max, price]
                $stmtAddon->execute([$cat, $item['id'], $item['name'], $item['type'], $tier[0], $tier[1], $tier[2]]);
            }
        } else {
            // flat price
            $stmtAddon->execute([$cat, $item['id'], $item['name'], $item['type'], 0, 9999, $item['price']??0]);
        }
    }
}

// SEED tbl_grad_packages
$stmtGradPkg = $db->prepare("INSERT INTO tbl_grad_packages (id, name, price, description, color) VALUES (?,?,?,?,?)");
foreach($grad['packages'] as $g) {
    $stmtGradPkg->execute([$g['id'], $g['name'], $g['price'], $g['desc'], $g['color']]);
}

// SEED tbl_grad_addons
$stmtGradAddon = $db->prepare("INSERT INTO tbl_grad_addons (id, category, name, price) VALUES (?,?,?,?)");
foreach($grad['addons'] as $add) {
    $stmtGradAddon->execute([$add['id'], 'addons', $add['name'], $add['price']]);
}
foreach($grad['cetak'] as $ct) {
    $stmtGradAddon->execute([$ct['id'], 'cetak', $ct['name'], $ct['price']]);
}

echo "Tables Normalized & Seeded Successfully!\n";
