<div class="row">
	<div class="col-md-4 col-md-offset-3" id="crop_image">
		<?php if (!empty($user_info['photo'])) {?>
		<img src="/uploads/avatars/<?php echo $user_info['photo']?>"/>
		<?php }?>
	</div>
</div>
<?php echo $upload_form?>
<?php after_load('css', '/dist/crop/imgareaselect-default.css')?>
<?php after_load('css', '/dist/crop/imgareaselect-animated.css')?>
<?php after_load('js', '/dist/crop/jquery.imgareaselect.pack.js')?>
<script>
	window.onload=function() {
		$('input[type="file"]').on('change', function(input) {
			if (input.target.files && input.target.files[0]) {
				var reader = new FileReader();
				var image_div = $('#crop_image');

				reader.onload = function (e) {
					if (image_div.find('img').length == 0) {
						image_div.html('<img src=""/>');
					}
					$('#crop_image img').attr('src', e.target.result);
					$('#crop_image img').imgAreaSelect({
						aspectRatio : '1:1',
						handles     : true,
						x1          : 20,
						y1          : 20,
						x2          : 150,
						y2          : 150,
						onInit : function (img, selection) {
							$('input[name="x1"]').val(selection.x1);
							$('input[name="y1"]').val(selection.y1);
							$('input[name="re_width"]').val($('#crop_image img').width());
							$('input[name="re_height"]').val($('#crop_image img').height());
							$('input[name="width"]').val(selection.width);
							$('input[name="height"]').val(selection.height);
						},
						onSelectEnd : function (img, selection) {
							$('input[name="x1"]').val(selection.x1);
							$('input[name="y1"]').val(selection.y1);
							$('input[name="re_width"]').val($('#crop_image img').width());
							$('input[name="re_height"]').val($('#crop_image img').height());
							$('input[name="width"]').val(selection.width);
							$('input[name="height"]').val(selection.height);
						}
					});
				};

				reader.readAsDataURL(input.target.files[0]);
			}
		});
	};
</script>

<style>
	#crop_image {padding-bottom: 20px;}
	#crop_image img {max-width: 200px;max-height: 200px;}
</style>
