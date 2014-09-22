<h4 class="title">Диалог с горничной</h4>
<div class="message_items" id="message_items">
<?php if (!empty($order_messages)) {
	foreach ($order_messages as $item) {?>
		<div class="item <?php echo !empty($item['owner']) ? 'from_me' : 'to_me'?>">
		<div class="inner">
			<div class="small_photo">
				<img src="<?php echo !empty($item['photo']) ? '/uploads/avatars/'.$item['photo'] : '/img/no_photo.jpg'?>" width="55" class="img-circle">
			</div>
			<div class="text"><?php echo $item['text']?></div>
			<div class="date"><?php echo date('d.m.Y, H:i', $item['add_date'])?></div>
		</div>
		<div class="clear"></div>
	</div>
<?php }}?>
</div>
<div class="message_form">
	<?php echo $message_form?>
</div>
<script>
	var objDiv = document.getElementById('message_items');
	objDiv.scrollTop = objDiv.scrollHeight;
</script>
