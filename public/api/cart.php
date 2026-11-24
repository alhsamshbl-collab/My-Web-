<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require __DIR__.'/../includes/bootstrap.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);echo json_encode(['ok'=>false,'message'=>'طريقة الطلب غير مسموحة']);exit;}
if(!hash_equals($_SESSION['csrf']??'',$_POST['csrf']??'')){http_response_code(419);echo json_encode(['ok'=>false,'message'=>'انتهت صلاحية الصفحة، حدّثها وحاول مجدداً.']);exit;}
$id=filter_var($_POST['product_id']??null,FILTER_VALIDATE_INT);$qty=filter_var($_POST['quantity']??1,FILTER_VALIDATE_INT);
if(!$id||!$qty||$qty<1){http_response_code(422);echo json_encode(['ok'=>false,'message'=>'اختر كمية صحيحة.']);exit;}
$q=db()->prepare('SELECT stock,is_active FROM products WHERE id=?');$q->execute([$id]);$p=$q->fetch();
if(!$p||!(int)$p['is_active']){http_response_code(404);echo json_encode(['ok'=>false,'message'=>'المنتج غير متاح.']);exit;}
if((int)$p['stock']<1){http_response_code(409);echo json_encode(['ok'=>false,'message'=>'نفدت الكمية من هذا المنتج.']);exit;}
$current=(int)($_SESSION['cart'][$id]??0);if($current+$qty>(int)$p['stock']){http_response_code(409);echo json_encode(['ok'=>false,'message'=>'الكمية المطلوبة تتجاوز المخزون المتاح.']);exit;}
$_SESSION['cart'][$id]=$current+$qty;
echo json_encode(['ok'=>true,'count'=>cart_count()],JSON_UNESCAPED_UNICODE);
