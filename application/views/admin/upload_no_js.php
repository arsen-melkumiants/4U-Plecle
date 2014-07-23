<div class="row">
	<div class="col-md-4 col-md-offset-3" id="crop_image">
		<?php if (!empty($image_full_path)) {?>
		<img src="<?php echo $image_full_path?>"/>
		<?php }?>
	</div>
</div>
<?php echo $upload_form?>
<style>
	#crop_image {padding-bottom: 20px;}
	#crop_image img {max-height: 160px;}
</style>
