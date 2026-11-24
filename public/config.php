<?php
// أدخل بيانات قاعدة بيانات InfinityFree من لوحة التحكم هنا.
define('DB_HOST', 'sqlXXX.infinityfree.com');
define('DB_NAME', 'if0_XXXXXXXX_store');
define('DB_USER', 'if0_XXXXXXXX');
define('DB_PASS', 'CHANGE_THIS_PASSWORD');
// غيّر هذا المفتاح قبل فتح public/admin/setup.php، ثم احذف setup.php بعد إنشاء المدير.
define('ADMIN_SETUP_KEY', 'CHANGE_TO_A_LONG_RANDOM_SETUP_KEY');
function store_db(): PDO {
 static $pdo=null;
 if ($pdo instanceof PDO) return $pdo;
 $pdo=new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',DB_USER,DB_PASS,[
  PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES=>false
 ]);
 return $pdo;
}
