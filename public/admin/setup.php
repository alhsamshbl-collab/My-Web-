<?php
declare(strict_types=1);
require __DIR__.'/../includes/bootstrap.php';
$error='';
$adminCount=(int)db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
if($adminCount>0){http_response_code(410);exit('تم إنشاء المدير الأول مسبقاً. صفحة التهيئة مغلقة.');}
if($_SERVER['REQUEST_METHOD']==='POST'){
 csrf_check();
 $key=(string)($_POST['setup_key']??'');
 $name=trim((string)($_POST['full_name']??''));
 $email=strtolower(trim((string)($_POST['email']??'')));
 $password=(string)($_POST['password']??'');
 $confirm=(string)($_POST['confirm_password']??'');
 if(!defined('ADMIN_SETUP_KEY')||ADMIN_SETUP_KEY==='CHANGE_TO_A_LONG_RANDOM_SETUP_KEY'||!hash_equals(ADMIN_SETUP_KEY,$key))$error='مفتاح التهيئة غير صحيح أو لم يتم تغييره في config.php.';
 elseif($name===''||!filter_var($email,FILTER_VALIDATE_EMAIL))$error='أدخل الاسم وبريداً إلكترونياً صحيحاً.';
 elseif(strlen($password)<12)$error='يجب أن تكون كلمة المرور 12 حرفاً على الأقل.';
 elseif(!hash_equals($password,$confirm))$error='كلمتا المرور غير متطابقتين.';
 else{
  try{
   db()->beginTransaction();
   if((int)db()->query('SELECT COUNT(*) FROM admins')->fetchColumn()>0)throw new RuntimeException('تم إنشاء المدير الأول بالفعل.');
   $q=db()->prepare('INSERT INTO admins(full_name,email,username,password_hash) VALUES(?,?,?,?)');
   $q->execute([$name,$email,$email,password_hash($password,PASSWORD_DEFAULT)]);
   db()->commit();
   session_regenerate_id(true);
   $_SESSION['admin_id']=(int)db()->lastInsertId();
   $_SESSION['admin_name']=$name;
   go('index.php');
  }catch(Throwable $e){if(db()->inTransaction())db()->rollBack();$error=$e instanceof PDOException?'تعذر إنشاء الحساب. تحقق من أن قاعدة البيانات تستخدم schema.sql المحدّث وأن البريد غير مستخدم.':$e->getMessage();}
 }
}
?><!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>إعداد المدير الأول</title><link rel="stylesheet" href="../assets/css/store.css"></head><body><main class="inner login"><h1>إنشاء المدير الأول</h1><p>هذه الصفحة تعمل مرة واحدة فقط قبل إنشاء أول مدير. بعد ذلك تُغلق تلقائياً. احذف ملف setup.php بعد إتمام الإعداد.</p><?php if($error):?><p class="notice"><?=h($error)?></p><?php endif?><form method="post" autocomplete="off"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><label>مفتاح التهيئة<input name="setup_key" type="password" required autocomplete="off"></label><label>اسم المدير<input name="full_name" maxlength="180" required></label><label>البريد الإلكتروني<input name="email" type="email" maxlength="190" required></label><label>كلمة المرور (12 حرفاً على الأقل)<input type="password" name="password" minlength="12" required autocomplete="new-password"></label><label>تأكيد كلمة المرور<input type="password" name="confirm_password" minlength="12" required autocomplete="new-password"></label><button class="gold" type="submit">حفظ المدير والدخول</button></form></main></body></html>