<div class="cleaner_info">
	<div class="row">
		<div class="col-sm-4">
			<img src="<?php echo !empty($cleaner_info['photo']) ? '/uploads/avatars/'.$cleaner_info['photo'] : '/img/no_photo.jpg'?>" width="100" alt="<?php echo $cleaner_info['first_name']?>" class="img-circle">
		</div>
		<div class="col-sm-8 text-left">
			<h2><?php echo $cleaner_info['first_name'].' '.$cleaner_info['last_name']?></h2>
			<p>Отзывов: <?php echo $marks['total'].' (<span class="text-success">'.$marks['success'].'</span> / <span class="text-danger">'.$marks['fail'].'</span>)'?></p>
			<?php if ($this->ion_auth->logged_in() && !$is_favorite) {?>
			<form action="<?php echo site_url('personal/cleaner_profile/'.$cleaner_info['id'])?>" method="post">
				<button name="add_favorite" type="submit">Добавить в избранное</button>
			</form>
			<?php }?>
		</div>
	</div>
	<div class="reviews">
		<h4 class="title">Отзывы</h4>
		<ul>
			<li>
			<div class="name">Alex</div>
			<div class="text">Test Test Test</div>
			</li>
			<li>
			<div class="name">Alex</div>
			<div class="text">Test Test Test</div>
			</li>
			<li>
			<div class="name">Alex</div>
			<div class="text">Test Test Test</div>
			</li>
		</ul>
	</div>
	<br>
	<br>
	<button class="btn btn-primary btn-block" data-dismiss="modal">Закрыть</button>
</div>
