<?php if (!empty($products)) {?>
<div class="custom_block">
	<div class="title">
		<h2><?php echo $name?></h2>
	</div>
	<div class="slide_content">
		<?php
		$count = count($products) - 1;
		$i = 1;
		foreach ($products as $key => $item) {
		if ($i == 1) {?>
		<div class="slide_item">
			<?php } ?>
			<div class="item">
				<h4 class="name" title="<?php echo $item['name']?>"><a href="<?php echo product_url($item['id'], $item['name'])?>"><?php echo $item['name']?></a></h4>
				<a href="<?php echo product_url($item['id'], $item['name'])?>">
					<div class="image"<?php echo !empty($item['file_name']) ? ' style="border:0;background:none;"' : '' ?>>
						<?php echo !empty($item['file_name']) ? '<img src="/uploads/gallery/'.$item['folder'].'small_thumb/'.$item['file_name'].'" />' : '';?>
						<?php /*
						<div class="bg"></div>
						<div class="bg_text"><?php echo lang('read_more')?></div>
						*/ ?>
					</div>
				</a>
				<div class="action">
					<div class="price"><i class="c_icon_label"></i><?php echo floatval($item['price']).' '.$item['symbol']?></div>
					<button class="orange_btn add_to_cart" 
						data-name="<?php echo $item['name']?>" 
						data-id="<?php echo $item['id']?>" 
						data-href="<?php echo product_url($item['id'], $item['name'])?>"
						><?php echo lang('buy')?></button>
				</div>
			</div>
			<?php if ($i == 6 || $count == $key) {?>
		</div>
		<?php } ?>
		<?php $i = $i < 6 ? $i + 1 : 1;}?>
	</div>
	<div class="clear"></div>
</div>
<?php } ?>
<?php after_load('js', '/js/slider/bxslider.min.js');?>
<?php after_load('css', '/js/slider/bxslider.css');?>
<script>
window.onload = function() {
	$('.slide_content').bxSlider({
		adaptiveHeight: false,
		controls:true,
		pager:false,
		/*auto:true,
		pause:10000,
		autoStart:true,
		autoHover:true,*/
	});


	/*if(typeof load_slider === 'undefined' || !load_slider) {
		var load_slider = true;
			$('.slide_content');
		});
	}*/
}
</script>
