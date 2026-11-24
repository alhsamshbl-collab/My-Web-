<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
try {
 require __DIR__.'/../includes/bootstrap.php';
 $id=filter_input(INPUT_GET,'category',FILTER_VALIDATE_INT);
 $term=trim((string)($_GET['q']??''));
 $sql='SELECT id,name,price,image_url,stock FROM products WHERE is_active=1';
 $args=[];
 if($id){$sql.=' AND category_id=?';$args[]=$id;}
 if($term!==''){$sql.=' AND (name LIKE ? OR description LIKE ?)';$like='%'.$term.'%';$args[]=$like;$args[]=$like;}
 $sql.=' ORDER BY id DESC LIMIT 60';
 $s=db()->prepare($sql);$s->execute($args);
 echo json_encode(['products'=>$s->fetchAll()],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
} catch(Throwable $e) {http_response_code(500);echo json_encode(['products'=>[],'message'=>'تعذر تحميل المنتجات.'],JSON_UNESCAPED_UNICODE);}
