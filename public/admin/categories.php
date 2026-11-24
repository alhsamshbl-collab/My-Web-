<?php
require __DIR__.'/../includes/bootstrap.php';
require_permission('categories');
$msg=''; $error='';
function category_remove_image(?string $path):void {
    if(!$path || !str_starts_with($path,'uploads/')) return;
    $file=realpath(__DIR__.'/../'.$path);
    $base=realpath(__DIR__.'/../uploads');
    if($file && $base && str_starts_with($file,$base.DIRECTORY_SEPARATOR) && is_file($file)) @unlink($file);
}
$editId=(int)($_GET['edit']??0);
$editing=null;
if($editId){$q=db()->prepare('SELECT * FROM categories WHERE id=?');$q->execute([$editId]);$editing=$q->fetch();if(!$editing){$editId=0;}}
if($_SERVER['REQUEST_METHOD']==='POST'){
 csrf_check();
 try{
  $action=$_POST['action']??'save';
  if($action==='delete'){
   $id=(int)($_POST['id']??0);$q=db()->prepare('SELECT image FROM categories WHERE id=?');$q->execute([$id]);$old=$q->fetchColumn();
   if(!$id) throw new RuntimeException('القسم غير محدد.');
   db()->prepare('DELETE FROM categories WHERE id=?')->execute([$id]);category_remove_image($old?:null);
   $msg='تم حذف القسم. المنتجات المرتبطة به بقيت محفوظة دون قسم.';
  }else{
   $id=(int)($_POST['id']??0);$name=trim($_POST['name']??'');$slug=trim($_POST['slug']??'');$sort=(int)($_POST['sort_order']??0);
   if($name===''||!preg_match('/^[a-z0-9-]+$/',$slug)) throw new RuntimeException('أدخل اسم القسم ورابطاً لاتينياً صحيحاً.');
   $q=db()->prepare('SELECT image FROM categories WHERE id=?');$q->execute([$id]);$old=$id?$q->fetchColumn():null;
   $image=$old?:null;
   if(!empty($_FILES['image']['name'])){$image=upload_store_image('image');if($old)category_remove_image($old);}
   elseif(isset($_POST['remove_image']) && $old){category_remove_image($old);$image=null;}
   if($id) db()->prepare('UPDATE categories SET name=?,slug=?,image=?,sort_order=? WHERE id=?')->execute([$name,$slug,$image,$sort,$id]);
   else db()->prepare('INSERT INTO categories(name,slug,image,sort_order) VALUES(?,?,?,?)')->execute([$name,$slug,$image,$sort]);
   $msg=$id?'تم تعديل القسم بنجاح.':'تمت إضافة القسم بنجاح.';
  }
  $editId=0;$editing=null;
 }catch(Throwable $e){$error=$e->getMessage();}
}
if($editId && !$editing){$q=db()->prepare('SELECT * FROM categories WHERE id=?');$q->execute([$editId]);$editing=$q->fetch();}
$cats=db()->query('SELECT * FROM categories ORDER BY sort_order,id')->fetchAll();
?><!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>إدارة الأقسام</title><link rel="stylesheet" href="../assets/css/store.css"></head><body><main class="inner"><a href="index.php">← لوحة التحكم</a><h1>إدارة الأقسام</h1>
<?php if($msg):?><p class="notice"><?=h($msg)?></p><?php endif?><?php if($error):?><p class="notice"><?=h($error)?></p><?php endif?>
<h2><?=$editing?'تعديل القسم':'إضافة قسم جديد'?></h2>
<form method="post" enctype="multipart/form-data" class="admin-form"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=$editing?(int)$editing['id']:0?>">
<label>اسم القسم<input name="name" required value="<?=h($editing['name']??'')?>"></label>
<label>الرابط اللاتيني (Slug)<input name="slug" required pattern="[a-z0-9-]+" value="<?=h($editing['slug']??'')?>"></label>
<label>صورة القسم من الهاتف<input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"></label>
<?php if(!empty($editing['image'])):?><label>الصورة الحالية<img class="upload-preview" src="../<?=h($editing['image'])?>" alt=""><input type="checkbox" name="remove_image" value="1"> حذف الصورة الحالية</label><?php endif?>
<label>ترتيب العرض<input type="number" name="sort_order" value="<?=h($editing['sort_order']??0)?>"></label>
<button class="gold"><?=$editing?'حفظ التعديلات':'إضافة القسم'?></button><?php if($editing):?><a class="gold" href="categories.php">إلغاء</a><?php endif?></form>
<div class="table-wrap"><table><tr><th>القسم</th><th>الصورة</th><th>Slug</th><th>الترتيب</th><th>إجراءات</th></tr><?php foreach($cats as $c):?><tr><td><?=h($c['name'])?></td><td><?php if($c['image']):?><img width="55" height="55" style="object-fit:cover" src="../<?=h($c['image'])?>" alt=""><?php else:?>—<?php endif?></td><td><?=h($c['slug'])?></td><td><?=$c['sort_order']?></td><td><a href="categories.php?edit=<?=$c['id']?>">تعديل</a> <form method="post" class="inline" onsubmit="return confirm('هل تريد حذف هذا القسم؟ ستبقى المنتجات دون قسم.');"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$c['id']?>"><button type="submit">حذف</button></form></td></tr><?php endforeach?></table></div></main></body></html>