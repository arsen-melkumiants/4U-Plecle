<div class="cleaner_info">
	<div class="row">
		<div class="col-sm-4">
			<img src="<?php echo !empty($cleaner_info['photo']) ? '/uploads/avatars/'.$cleaner_info['photo'] : '/img/no_photo.jpg'?>" width="100" alt="<?php echo $cleaner_info['first_name']?>" class="img-circle">
		</div>
		<div class="col-sm-8">
			<h2><?php echo $cleaner_info['first_name'].' '.$cleaner_info['last_name']?></h2>
		</div>
	</div>
</div>
