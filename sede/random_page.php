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
        <input type="text" name="user" placeholder="Username" required>
    <input type="password" name="pass" placeholder="Password" required>
    <button type="submit">Login</button>
</form>
</body>
</html>
