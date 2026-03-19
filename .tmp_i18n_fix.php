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

function readJson($path){
  if(!is_file($path)) return [];
  $raw=file_get_contents($path);
  $raw=ltrim($raw, "\xEF\xBB\xBF");
  $j=json_decode($raw,true);
  return is_array($j)?$j:[];
}
function writeJson($path,$arr){
  ksort($arr);
  file_put_contents($path, json_encode($arr, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."\n");
}
function readPhpArr($path){
  if(!is_file($path)) return [];
  $arr = include $path;
  return is_array($arr)?$arr:[];
}
function writePhpArr($path,$arr){
  ksort($arr);
  $export = var_export($arr,true);
  $content = "<?php\n\nreturn ".$export.";\n";
  file_put_contents($path,$content);
}
function getNested($arr,$parts,&$found=false){
  $v=$arr; $found=true;
  foreach($parts as $p){
    if(is_array($v) && array_key_exists($p,$v)) $v=$v[$p];
    else { $found=false; return null; }
  }
  return $v;
}
function setNested(&$arr,$parts,$val){
  $ref=&$arr;
  foreach($parts as $p){
    if(!isset($ref[$p]) || !is_array($ref[$p])) $ref[$p]=[];
    $ref=&$ref[$p];
  }
  if($ref===[] || is_array($ref)) $ref=$val; else $ref=$val;
}
function humanize($s){
  $s=str_replace(['_','-'], ' ', $s);
  $s=preg_replace('/\s+/', ' ', trim($s));
  return $s===''?'Text':ucfirst($s);
}
function isDynamicKey($k){
  return str_contains($k,'$') || str_ends_with($k,'.') || str_contains($k,'..') || preg_match('/\{\{|\}\}/',$k);
}

$enJsonPath="$base/lang/en.json"; $amJsonPath="$base/lang/am.json";
$enJson=readJson($enJsonPath); $amJson=readJson($amJsonPath);
$phpCache=['en'=>[],'am'=>[]];
$phpTouched=['en'=>[],'am'=>[]];
$created=[];

$getPhp = function($locale,$group) use ($base,&$phpCache){
  if(!isset($phpCache[$locale][$group])){
    $path="$base/lang/$locale/$group.php";
    $phpCache[$locale][$group]= readPhpArr($path);
  }
  return $phpCache[$locale][$group];
};
$setPhp = function($locale,$group,$arr) use (&$phpCache,&$phpTouched){
  $phpCache[$locale][$group]=$arr;
  $phpTouched[$locale][$group]=true;
};

$stats=['json_added_en'=>0,'json_added_am'=>0,'php_added_en'=>0,'php_added_am'=>0,'skipped'=>0];

foreach(array_keys($keys) as $k){
  if(trim($k)==='') continue;
  if(isDynamicKey($k)){ $stats['skipped']++; continue; }

  $hasDot = str_contains($k,'.') && !preg_match('/\s/',$k);
  if($hasDot){
    [$group,$rest]=explode('.',$k,2);
    if($group===''||$rest===''){ $stats['skipped']++; continue; }
    $parts=explode('.',$rest);

    $enArr=$getPhp('en',$group); $amArr=$getPhp('am',$group);
    $enFound=false; $amFound=false;
    $enVal=getNested($enArr,$parts,$enFound);
    $amVal=getNested($amArr,$parts,$amFound);

    if(!$enFound){
      $val = $amFound && is_string($amVal) ? $amVal : humanize(end($parts));
      setNested($enArr,$parts,$val);
      $setPhp('en',$group,$enArr);
      $stats['php_added_en']++;
    }
    if(!$amFound){
      $source = $enFound && is_string($enVal) ? $enVal : humanize(end($parts));
      setNested($amArr,$parts,$source);
      $setPhp('am',$group,$amArr);
      $stats['php_added_am']++;
    }
  } else {
    $enHas=array_key_exists($k,$enJson); $amHas=array_key_exists($k,$amJson);
    if(!$enHas){ $enJson[$k]=$amHas ? $amJson[$k] : $k; $stats['json_added_en']++; }
    if(!$amHas){ $amJson[$k]=$enHas ? $enJson[$k] : $k; $stats['json_added_am']++; }
  }
}

writeJson($enJsonPath,$enJson);
writeJson($amJsonPath,$amJson);
foreach(['en','am'] as $loc){
  foreach($phpTouched[$loc] as $group=>$_){
    $path="$base/lang/$loc/$group.php";
    if(!is_file($path)) $created[]=$path;
    writePhpArr($path,$phpCache[$loc][$group]);
  }
}

echo "UPDATED_JSON_EN={$stats['json_added_en']}\n";
echo "UPDATED_JSON_AM={$stats['json_added_am']}\n";
echo "UPDATED_PHP_EN={$stats['php_added_en']}\n";
echo "UPDATED_PHP_AM={$stats['php_added_am']}\n";
echo "SKIPPED={$stats['skipped']}\n";
if($created){ echo "CREATED_FILES=\n".implode("\n",$created)."\n"; }
