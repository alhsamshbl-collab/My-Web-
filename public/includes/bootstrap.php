<?php
declare(strict_types=1);
if(session_status()!==PHP_SESSION_ACTIVE)session_start();
require_once __DIR__.'/../config.php';
function db():PDO{return store_db();}
function h($v):string{return htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function money($v):string{return number_format((float)$v,2).' ر.س';}
function csrf_token():string{if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(32));return $_SESSION['csrf'];}
function csrf_check():void{if(!hash_equals($_SESSION['csrf']??'',$_POST['csrf']??'')){http_response_code(419);exit('انتهت صلاحية الطلب. حدّث الصفحة وحاول مجدداً.');}}
function cart_items():array{return $_SESSION['cart']??[];}
function cart_count():int{return array_sum(cart_items());}
function is_admin():bool{return !empty($_SESSION['admin_id']);}
function require_admin():void{if(!is_admin()){header('Location: login.php');exit;} $q=db()->prepare('SELECT is_active FROM admins WHERE id=?');$q->execute([$_SESSION['admin_id']]);if(!(int)$q->fetchColumn()){$_SESSION=[];session_destroy();header('Location: login.php');exit;}}
function admin_role():string{return (string)($_SESSION['admin_role']??'');}
function require_permission(string $permission):void{require_admin();$role=admin_role();$map=['owner'=>['all'],'assistant'=>['products','categories','banners','orders','settings'],'orders'=>['orders'],'products'=>['products','categories']];if(!in_array('all',$map[$role]??[],true)&&!in_array($permission,$map[$role]??[],true)){http_response_code(403);exit('ليس لديك صلاحية لتنفيذ هذا الإجراء.');}}
function go(string $url):never{header('Location: '.$url);exit;}
function setting(string $key,string $default=''):string{static $all=null;if($all===null){$all=[];try{foreach(db()->query('SELECT setting_key,setting_value FROM store_settings')->fetchAll() as $r)$all[$r['setting_key']]=$r['setting_value'];}catch(Throwable $e){}}return (string)($all[$key]??$default);}
function upload_store_image(string $field='image'):string{if(empty($_FILES[$field])||$_FILES[$field]['error']!==UPLOAD_ERR_OK)throw new RuntimeException('اختر صورة من جهازك أولاً.');$f=$_FILES[$field];if($f['size']>5*1024*1024)throw new RuntimeException('حجم الصورة يجب ألا يتجاوز 5 ميجابايت.');$mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);$types=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];if(!isset($types[$mime])||!getimagesize($f['tmp_name']))throw new RuntimeException('صيغة الصورة غير مدعومة. استخدم JPG أو PNG أو WEBP أو GIF.');$dir=__DIR__.'/../uploads';if(!is_dir($dir)&&!mkdir($dir,0755,true))throw new RuntimeException('تعذر إنشاء مجلد الصور.');$name=bin2hex(random_bytes(16)).'.'.$types[$mime];if(!move_uploaded_file($f['tmp_name'],$dir.'/'.$name))throw new RuntimeException('تعذر حفظ الصورة على الاستضافة.');return 'uploads/'.$name;}
