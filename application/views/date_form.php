<div class="form-group col-sm-6">
<label class="col-md-12 control-label"><?php echo $params['label']?></label>
	<div class="col-md-12">
		<div class="row">
			<div class="col-xs-4">
				<select name="day" class="form-control">
					<?php for($i = 1; $i <= 31; $i++) {
					$selected = !empty($params['value']) && date('d', $params['value']) == $i ? ' selected="selected"' : '';
					?>
					<option<?php echo $selected?> value="<?php echo $i?>"><?php echo sprintf('%02d', $i);?></option>
					<?php }?>
				</select>
			</div>
			<div class="col-xs-4">
				<select name="month" class="form-control">
					<?php for($i = 1; $i <= 12; $i++) {
					$selected = !empty($params['value']) && date('m', $params['value']) == $i ? ' selected="selected"' : '';
					?>
					<option<?php echo $selected?> value="<?php echo $i?>"><?php echo sprintf('%02d', $i);?></option>
					<?php }?>
				</select>
			</div>
			<div class="col-xs-4">
				<select name="year" class="form-control">
					<?php for($i = intval(date('Y')); $i >= 1920; $i--) {
					$selected = !empty($params['value']) && date('Y', $params['value']) == $i ? ' selected="selected"' : '';
					?>
					<option<?php echo $selected?> value="<?php echo $i?>"><?php echo $i?></option>
					<?php }?>
				</select>
			</div>
		</div>
	</div>
</div>
