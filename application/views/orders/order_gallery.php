<h4 class="title">Фото</h4>
<div class="gallery_block" id="images">
	<?php if (!empty($gallery_items)) {
	foreach ($gallery_items as $item) {?>
	<div class="item">
		<a href="<?php echo base_url('/uploads/order_gallery/'.$item['order_id'].'/'.$item['file_name'])?>">
			<img src="<?php echo base_url('/uploads/order_gallery/'.$item['order_id'].'/thumb/'.$item['file_name'])?>" />
		</a>
	</div>
	<?php }}?>

	<?php if ($is_owner) {?>
	<div class="item add"></div>
	<?php }?>

	<div class="clear"></div>

	<?php if ($is_owner) {?>
	<form action="" method="post" enctype="multipart/form-data">
		<input type="file" name="userfile"/>
	</form>
	<?php }?>
</div>
	<?php if ($is_owner) {?>
	<br />
	<p class="text-danger">Внимание!Фото будут показаны только работнику участвующему в сделке</p>
	<?php }?>
<script>
	window.onload = function() {
		$('.gallery_block .add').on('click', function() {
			$(this).parent().find('input[type="file"]').click();
		});

		$('.gallery_block input[type="file"]').on('change', function() {
			$(this).parent().submit();
		});
	};
</script>
<?php after_load('js', '/js/upload/vendor/jquery.blueimp-gallery.min.js');?>
<?php after_load('css', '/js/upload/blueimp-gallery.min.css');?>
<!-- The blueimp Gallery widget -->
<div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls" data-filter=":even">
	<div class="slides"></div>
	<h3 class="title"></h3>
	<a class="prev">‹</a>
	<a class="next">›</a>
	<a class="close">×</a>
	<a class="play-pause"></a>
	<ol class="indicator"></ol>
</div>
<script>
	document.getElementById('images').onclick = function (event) {
		event = event || window.event;
		var target = event.target || event.srcElement,
		link = target.src ? target.parentNode : target,
		options = {index: link, event: event},
		links = this.getElementsByTagName('a');
		blueimp.Gallery(links, options);
	};
</script>
