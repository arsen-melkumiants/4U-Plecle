<?php if (!empty($user_id)) {?>
<a data-toggle="modal" data-target="#ajaxModal" href="<?php echo site_url('personal/cleaner_profile/'.$user_id)?>">
	<img src="<?php echo !empty($photo) ? '/uploads/avatars/'.$photo : '/img/no_photo.jpg'?>" width="55" class="img-circle">
</a>
<?php } else {?>
	<img src="/img/no_photo.jpg" width="55" class="img-circle">
<?php }?>
