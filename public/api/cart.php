<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__.'/../includes/bootstrap.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);echo json_encode(['ok'=>false,'message'=>'طريقة الطلب غير مسموحة.'],JSON_UNESCAPED_UNICODE);exit;}
try{
 $payload=$_POST;if(!$payload){parse_str(file_get_contents('php://input'),$payload);}
 if(!hash_equals($_SESSION['csrf']??'',(string)($payload['csrf']??''))){http_response_code(419);throw new RuntimeException('انتهت صلاحية الجلسة. حدّث الصفحة وحاول مجدداً.');}
 $id=(int)($payload['product_id']??0);$qty=max(1,(int)($payload['quantity']??1));
 if(($payload['action']??'')!=='add'||$id<1)throw new RuntimeException('بيانات المنتج غير صحيحة.');
 $stmt=db()->prepare('SELECT stock,is_active FROM products WHERE id=?');$stmt->execute([$id]);$p=$stmt->fetch(PDO::FETCH_ASSOC);
 if(!$p||(int)$p['is_active']!==1)throw new RuntimeException('هذا المنتج غير متاح حالياً.');
 if((int)$p['stock']<1)throw new RuntimeException('نفدت كمية هذا المنتج.');
 $_SESSION['cart'][$id]=min((int)$p['stock'],(int)($_SESSION['cart'][$id]??0)+$qty);
 echo json_encode(['ok'=>true,'count'=>cart_count()],JSON_UNESCAPED_UNICODE);
}catch(Throwable $e){if(http_response_code()<400)http_response_code(400);echo json_encode(['ok'=>false,'message'=>$e->getMessage()],JSON_UNESCAPED_UNICODE);}
