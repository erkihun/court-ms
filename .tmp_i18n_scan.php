<?php
error_reporting(E_ALL);
$base = getcwd();
$paths = ['resources/views','app/Http/Controllers','resources/js'];
$files=[];
foreach($paths as $p){
  $dir=$base.DIRECTORY_SEPARATOR.$p;
  if(!is_dir($dir)) continue;
  $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
  foreach($it as $f){ if($f->isFile()) $files[]=$f->getPathname(); }
}
$patterns = [
  '/__\(\s*[\'\"]([^\'\"]+)[\'\"]/',
  '/@lang\(\s*[\'\"]([^\'\"]+)[\'\"]/',
  '/trans\(\s*[\'\"]([^\'\"]+)[\'\"]/',
  '/trans_choice\(\s*[\'\"]([^\'\"]+)[\'\"]/'
];
$keys=[];
foreach($files as $file){
  $c=@file_get_contents($file); if($c===false) continue;
  foreach($patterns as $re){
    if(preg_match_all($re,$c,$m)){
      foreach($m[1] as $k){
        if(str_starts_with($k,'http')) continue;
        $keys[$k]=true;
      }
    }
  }
}
ksort($keys);
function hasJsonKey($locale,$key,$base){
  $f="$base/lang/$locale.json";
  if(!is_file($f)) return false;
  $j=json_decode(file_get_contents($f),true);
  return is_array($j) && array_key_exists($key,$j);
}
function hasPhpKey($locale,$key,$base){
  if(!str_contains($key,'.')) return false;
  [$file,$rest] = explode('.',$key,2);
  $path="$base/lang/$locale/$file.php";
  if(!is_file($path)) return false;
  $arr = include $path;
  if(!is_array($arr)) return false;
  $parts=explode('.',$rest);
  $v=$arr;
  foreach($parts as $p){
    if(is_array($v) && array_key_exists($p,$v)) $v=$v[$p]; else return false;
  }
  return true;
}
$missing=[];
foreach(array_keys($keys) as $k){
  $en = hasPhpKey('en',$k,$base) || hasJsonKey('en',$k,$base);
  $am = hasPhpKey('am',$k,$base) || hasJsonKey('am',$k,$base);
  if(!$en || !$am){ $missing[] = [$k,$en?'ok':'missing',$am?'ok':'missing']; }
}
foreach($missing as $m){ echo $m[0]."\tEN:".$m[1]."\tAM:".$m[2]."\n"; }
echo "TOTAL_KEYS=".count($keys)."\n";
echo "MISSING=".count($missing)."\n";
