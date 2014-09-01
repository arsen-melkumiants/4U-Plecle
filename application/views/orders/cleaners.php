<div class="users_block simple_list">
	<?php if (!empty($cleaners)) {?>
	<?php foreach ($cleaners as $item) {?>
	<div class="col-sm-3">
		<a data-toggle="modal" data-target="#ajaxModal" href="<?php echo site_url('personal/cleaner_profile/'.$item['id'])?>">
			<img src="<?php echo !empty($item['photo']) ? '/uploads/avatars/'.$item['photo'] : '/img/no_photo.jpg'?>" width="100" alt="<?php echo $item['first_name']?>" class="img-circle">
			<div class="name"><?php echo $item['first_name']?></div>
		</a>
	</div>
	<?php }} else {?>
	<h2>В данном районе горничных пока нет</h1>
	<h4>Оставьте Ваш запрос. Как только появится свободная горничная - Ваша завяка будет обработана</h3>
	<?php }?>
</div>
