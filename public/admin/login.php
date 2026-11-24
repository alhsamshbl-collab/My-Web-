<?php
require __DIR__.'/../includes/bootstrap.php';
if(is_admin())go('index.php');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 csrf_check();
 $identity=trim((string)($_POST['identity']??''));
 $s=db()->prepare('SELECT id,full_name,email,username,password_hash FROM admins WHERE email=? OR username=? LIMIT 1');
 $s->execute([$identity,$identity]);$a=$s->fetch();
 if($a&&password_verify((string)($_POST['password']??''),$a['password_hash'])){
  session_regenerate_id(true);$_SESSION['admin_id']=$a['id'];$_SESSION['admin_name']=$a['full_name']?:$a['username'];go('index.php');
 }
 $error='البريد الإلكتروني أو كلمة المرور غير صحيحة.';
}
?><!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>دخول الإدارة</title><link rel="stylesheet" href="../assets/css/store.css"></head><body><main class="inner login"><h1>دخول الإدارة</h1><?php if($error):?><p class="notice"><?=h($error)?></p><?php endif?><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><label>البريد الإلكتروني<input type="email" name="identity" required autocomplete="username"></label><label>كلمة المرور<input type="password" name="password" required autocomplete="current-password"></label><button class="gold">دخول</button></form></main></body></html>