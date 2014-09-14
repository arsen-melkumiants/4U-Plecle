<div class="users_block">
	<div class="container">
		<?php if (!empty($cleaners)) {?>
		<h1>Список горничных в Вашем районе</h1>
		<h3>Кто первый согласиться на сделку, того и заказ!</h3>
		<div class="row">
			<?php foreach ($cleaners as $item) {?>
			<div class="col-sm-2">
				<a data-toggle="modal" data-target="#ajaxModal" href="<?php echo site_url('personal/profile/'.$item['id'])?>">
					<img src="<?php echo !empty($item['photo']) ? '/uploads/avatars/'.$item['photo'] : '/img/no_photo.jpg'?>" width="100" alt="<?php echo $item['first_name']?>" class="img-circle">
					<div class="name"><?php echo $item['first_name']?></div>
				</a>
			</div>
			<?php }} else {?>
			<h1>В данный момент горничные отсутствуют</h1>
			<h3>Оставьте Ваш запрос. Как только появится свободная горничная - Ваша завяка будет обработана</h3>
			<?php }?>
		</div>
	</div>
</div>
