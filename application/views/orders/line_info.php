<div class="small_photo">
	<?php if (!empty($user_id)) {?>
	<a data-toggle="modal" data-target="#ajaxModal" href="<?php echo site_url('personal/profile/'.$user_id)?>">
		<img src="<?php echo !empty($photo) ? '/uploads/avatars/'.$photo : '/img/no_photo.jpg'?>" width="55" class="img-circle">
	</a>
	<?php } else {?>
	<img src="/img/no_photo.jpg" width="55" class="img-circle">
	<?php }?>

	<?php if(!empty($unread)) {?>
	<div class="count"><?php echo $unread?></div>
	<?php }?>
</div>
<div class="info">
	Уборка <?php echo date('d.m.Y с H:i', $start_date).' до '.date('H:i', $start_date + ($duration * 3600)) ?><br />
	<small><a data-toggle="modal" data-target="#ajaxModal" href="<?php echo site_url('orders/contact/'.$id)?>"><?php echo $address?></a>, <span class="price"><?php echo floatval($is_cleaner ? $total_cleaner_price : $total_price)?> рублей</span></small>
</div>
