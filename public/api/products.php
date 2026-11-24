<?php
header('Content-Type: application/json; charset=utf-8');
try {
 require __DIR__.'/../includes/bootstrap.php';
 $category=filter_input(INPUT_GET,'category',FILTER_VALIDATE_INT) ?: 0;
 $q=trim((string)($_GET['q']??''));
 $sql='SELECT id,name,price,image_url,stock FROM products WHERE is_active=1';
 $args=[];
 if($category>0){$sql.=' AND category_id=?';$args[]=$category;}
 if($q!==''){$sql.=' AND (name LIKE ? OR description LIKE ?)';$like='%'.$q.'%';$args[]=$like;$args[]=$like;}
 $sql.=' ORDER BY id DESC LIMIT 60';
 $stmt=db()->prepare($sql);$stmt->execute($args);
 echo json_encode(['products'=>$stmt->fetchAll(PDO::FETCH_ASSOC)],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
} catch(Throwable $e){http_response_code(500);echo json_encode(['products'=>[],'message'=>'تعذر تحميل المنتجات.'],JSON_UNESCAPED_UNICODE);}
