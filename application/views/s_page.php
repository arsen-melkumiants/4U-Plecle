<div class="container">
	<div class="row">
		<?php echo get_alerts();?>
	</div>
	<?php if(!empty($title)) {?>
	<h1 class="text-center"><?php echo $title?></h1>
	<hr />
	<?php }?>
	<?php echo !empty($center_block) ? $center_block : false;?>
</div>
