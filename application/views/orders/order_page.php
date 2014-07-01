<div class="container">
	<div class="row">
		<?php if (!empty($right_info)) {?>
		<div class="col-md-8"><?php echo $center_block?></div>
		<div class="col-md-4">
			<div class="detail_block">
				<div class="title"><?php echo $right_info['title']?></div>
				<div class="list">
					<table class="table">
						<tbody>
							<?php foreach ($right_info['info_array'] as $key => $value) {
							if ($value === false) {
								continue;
							}?>
							<tr><td><?php echo $key?></td><td><?php echo $value?></td></tr>
							<?php }?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php } else {?>
		<div class="col-md-12"><?php echo $center_block?></div>
		<?php }?>
	</div>
</div>
