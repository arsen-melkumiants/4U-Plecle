<div class="user_info">
	<div class="row">
		<div class="col-sm-4">
			<img src="<?php echo !empty($user_info['photo']) ? '/uploads/avatars/'.$user_info['photo'] : '/img/no_photo.jpg'?>" width="100" alt="<?php echo $user_info['first_name']?>" class="img-circle">
		</div>
		<div class="col-sm-8 text-left">
			<h2><?php echo $user_info['first_name'].' '.$user_info['last_name']?></h2>
			<?php /*<p>Рейтинг: <?php echo $marks['rating']?></p>*/?>
			<?php if (!empty($marks)) {?>
				<p>Отзывов: <?php echo $marks['total'].' (<span class="text-success">'.$marks['success'].'</span> / <span class="text-danger">'.$marks['fail'].'</span>)'?></p>
			<?php }?>

			<?php if ($this->ion_auth->logged_in() && !$is_favorite) {?>
				<form action="<?php echo site_url('personal/profile/'.$user_info['id'])?>" method="post">
					<button name="add_favorite" type="submit">Добавить в избранное</button>
				</form>
			<?php }?>
		</div>
	</div>
	<?php if (isset($reviews)) {?>
	<div class="reviews">
		<h4 class="title">Отзывы</h4>
		<?php if (!empty($reviews)) {?>
		<ul>
			<?php foreach($reviews as $item) {?>
			<li>
			<div class="name"><?php echo $item['first_name']?></div>
			<div class="text"><?php echo $item['review']?></div>
			</li>
			<?php }?>
		</ul>
		<?php } else {?>
		Отзывов пока нет
		<?php }?>
	</div>
	<?php }?>
	<br>
	<br>
	<button class="btn btn-primary btn-block" data-dismiss="modal">Закрыть</button>
</div>
