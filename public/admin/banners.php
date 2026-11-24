<?php
require __DIR__.'/../includes/bootstrap.php';
require_permission('banners');
$msg='';$error='';
function banner_remove_image(?string $path):void {
 if(!$path||!str_starts_with($path,'uploads/'))return;
 $file=realpath(__DIR__.'/../'.$path);$base=realpath(__DIR__.'/../uploads');
 if($file&&$base&&str_starts_with($file,$base.DIRECTORY_SEPARATOR)&&is_file($file))@unlink($file);
}
$editId=(int)($_GET['edit']??0);$editing=null;
if($editId){$q=db()->prepare('SELECT * FROM banners WHERE id=?');$q->execute([$editId]);$editing=$q->fetch();if(!$editing)$editId=0;}
if($_SERVER['REQUEST_METHOD']==='POST'){
 csrf_check();
 try{
  $action=$_POST['action']??'save';
  if($action==='delete'){
   $id=(int)($_POST['id']??0);$q=db()->prepare('SELECT image_url FROM banners WHERE id=?');$q->execute([$id]);$old=$q->fetchColumn();
   if(!$id)throw new RuntimeException('البانر غير محدد.');
   db()->prepare('DELETE FROM banners WHERE id=?')->execute([$id]);banner_remove_image($old?:null);$msg='تم حذف البانر وصورته.';
  }elseif($action==='save' && !empty($_POST['id'])){
   $id=(int)$_POST['id'];$title=trim($_POST['title']??'');if($title==='')throw new RuntimeException('عنوان البانر مطلوب.');
   $q=db()->prepare('SELECT image_url FROM banners WHERE id=?');$q->execute([$id]);$old=$q->fetchColumn();if(!$old)throw new RuntimeException('البانر غير موجود.');
   $image=$old;if(!empty($_FILES['image']['name'])){$image=upload_store_image('image');banner_remove_image($old);}
   db()->prepare('UPDATE banners SET title=?,subtitle=?,image_url=?,link_url=?,sort_order=?,is_active=? WHERE id=?')->execute([$title,trim($_POST['subtitle']??''),$image,trim($_POST['link_url']??''),(int)($_POST['sort_order']??0),isset($_POST['is_active'])?1:0,$id]);
   $msg='تم تعديل البانر.';
  }else{
   $title=trim($_POST['title']??'');if($title==='')throw new RuntimeException('عنوان البانر مطلوب.');
   $files=$_FILES['images']??null;if(!$files||empty($files['name'][0]))throw new RuntimeException('اختر صورة واحدة أو عدة صور من الهاتف.');
   $valid=[];foreach($files['name'] as $i=>$name){if($name!=='' && ($files['error'][$i]??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_OK)$valid[]=$i;}
   if(!$valid)throw new RuntimeException('لم يتم اختيار صور صالحة.');
   $stmt=db()->prepare('INSERT INTO banners(title,subtitle,image_url,link_url,sort_order,is_active) VALUES(?,?,?,?,?,?)');
   foreach($valid as $n=>$i){$single=['name'=>$files['name'][$i],'type'=>$files['type'][$i],'tmp_name'=>$files['tmp_name'][$i],'error'=>$files['error'][$i],'size'=>$files['size'][$i]];$image=upload_store_image_array($single);$label=count($valid)>1?$title.' - '.($n+1):$title;$stmt->execute([$label,trim($_POST['subtitle']??''),$image,trim($_POST['link_url']??''),(int)($_POST['sort_order']??0)+$n,isset($_POST['is_active'])?1:0]);}
   $msg='تمت إضافة '.count($valid).' بانر/بانرات بنجاح.';
  }
  $editId=0;$editing=null;
 }catch(Throwable $e){$error=$e->getMessage();}
}
if($editId&&!$editing){$q=db()->prepare('SELECT * FROM banners WHERE id=?');$q->execute([$editId]);$editing=$q->fetch();}
$items=db()->query('SELECT * FROM banners ORDER BY sort_order,id')->fetchAll();
?><!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>إدارة البانرات</title><link rel="stylesheet" href="../assets/css/store.css"></head><body><main class="inner"><a href="index.php">← لوحة التحكم</a><h1>إدارة البانرات والصور العلوية</h1>
<?php if($msg):?><p class="notice"><?=h($msg)?></p><?php endif?><?php if($error):?><p class="notice"><?=h($error)?></p><?php endif?>
<h2><?=$editing?'تعديل البانر':'إضافة بانرات جديدة'?></h2>
<form method="post" enctype="multipart/form-data" class="admin-form"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=$editing?(int)$editing['id']:0?>">
<label>اسم / عنوان البانر<input name="title" required value="<?=h($editing['title']??'')?>"></label><label>الوصف<input name="subtitle" value="<?=h($editing['subtitle']??'')?>"></label>
<?php if($editing):?><label>استبدال صورة البانر من الهاتف<input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"></label><label>الصورة الحالية<img class="upload-preview" src="../<?=h($editing['image_url'])?>" alt=""></label><?php else:?><label>اختيار صورة أو عدة صور من الهاتف<input type="file" name="images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple required></label><?php endif?>
<label>رابط الانتقال<input name="link_url" value="<?=h($editing['link_url']??'')?>"></label><label>الترتيب<input type="number" name="sort_order" value="<?=h($editing['sort_order']??0)?>"></label><label><input type="checkbox" name="is_active" <?=$editing?((int)$editing['is_active']?'checked':''):'checked'?>> منشور على الموقع</label>
<button class="gold"><?=$editing?'حفظ التعديلات':'إضافة الصور المحددة'?></button><?php if($editing):?><a class="gold" href="banners.php">إلغاء</a><?php endif?></form>
<div class="table-wrap"><table><tr><th>العنوان</th><th>الصورة</th><th>الترتيب</th><th>الحالة</th><th>إجراءات</th></tr><?php foreach($items as $b):?><tr><td><?=h($b['title'])?></td><td><img width="100" height="58" style="object-fit:cover" src="../<?=h($b['image_url'])?>" alt=""></td><td><?=$b['sort_order']?></td><td><?=$b['is_active']?'منشور':'مخفي'?></td><td><a href="banners.php?edit=<?=$b['id']?>">تعديل</a> <form method="post" class="inline" onsubmit="return confirm('هل تريد حذف هذا البانر وصورته؟');"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$b['id']?>"><button type="submit">حذف</button></form></td></tr><?php endforeach?></table></div></main></body></html>