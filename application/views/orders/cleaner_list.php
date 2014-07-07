<div class="users_block">
	<div class="container">
		<?php if (!empty($cleaners)) {?>
		<h1>Список горничных в Вашем районе</h1>
		<h3>Кто первый согласиться на сделку, того и заказ!</h3>
		<div class="row">
			<?php foreach ($cleaners as $item) {?>
			<div class="col-sm-2">
				<img src="/img/no_photo.jpg" width="100" alt="<?php echo $item['first_name']?>" class="img-circle">
				<div class="name"><?php echo $item['first_name']?></div>
			</div>
		<?php }} else {?>
		<h1>В данный момент горничные отсутствуют</h1>
		<h3>Оставьте Ваш запрос. Как только появится свободная горничная - Ваша завяка будет обработана</h3>
		<?php }?>
		</div>
	</div>
</div>
