<div class="top">♢ متجر موثوق وآمن　　🚚 التوصيل متوفر لجميع المناطق　　☎ 053 123 4567</div>
<header>
<a class="logo" href="index.php"><?php if(setting('store_logo')):?><img src="<?=h(setting('store_logo'))?>" alt="" style="max-width:54px;max-height:45px;object-fit:contain;vertical-align:middle"><?php endif?> <?=h(setting('store_name','الفخامة للشيشة والتبغ'))?> <small>Al Fakhamah</small></a>
<nav><a href="index.php">الرئيسية</a><a href="index.php#cats">الأقسام</a><a href="index.php#products">المنتجات</a></nav>
<button type="button" id="searchToggle" class="search-toggle" aria-label="فتح البحث">⌕</button>
<a href="login.php">حسابي</a><a class="cart" href="cart.php">🛒 السلة (<span id="cartCount"><?=cart_count()?></span>)</a>
</header>
<div id="searchPanel" class="search-panel" hidden><input id="productSearch" type="search" placeholder="ابحث عن منتج..." autocomplete="off"><button type="button" id="closeSearch" aria-label="إغلاق البحث">×</button></div>
<input type="hidden" id="cartCsrf" value="<?=h(csrf_token())?>">