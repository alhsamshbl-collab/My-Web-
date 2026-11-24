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
function require_admin():void{if(!is_admin()){header('Location: login.php');exit;}}
function go(string $url):never{header('Location: '.$url);exit;}
