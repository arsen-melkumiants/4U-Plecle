<div class="d_block prices">
	<img src="/img/top_bg2.jpg" />
	<?php if (strpos(mb_strtolower($title), 'цены') !== false || strpos(mb_strtolower($title), 'цена') !== false) {?>
	<div class="container">
		<h2 class="text-left"><span>Фиксированная цена</span></h2>
		<h1 class="text-center"><span><?php echo PRICE_PER_HOUR?> рублей / час</span></h1>
		<h2 class="text-right"><span>Онлайн оплата</span></h2>
	</div>
	<?php }?>
</div>
<div class="container content_block">
	<h1 class="text-center"><?php echo $title?></h1>
	<hr />
	<?php echo $center_block?>
</div>
