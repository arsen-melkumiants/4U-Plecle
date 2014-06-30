<div class="form-group col-sm-6">
<label class="col-md-12 control-label"><?php echo $params['label']?></label>
	<div class="col-md-12">
		<div class="row">
			<div class="col-xs-4">
				<select name="day" class="form-control">
					<?php for($i = 1; $i <= 31; $i++) {?>
					<option value="<?php echo $i?>"><?php echo $i?></option>
					<?php }?>
				</select>
			</div>
			<div class="col-xs-4">
				<select name="mounth" class="form-control">
					<?php for($i = 1; $i <= 12; $i++) {?>
					<option value="<?php echo $i?>"><?php echo $i?></option>
					<?php }?>
				</select>
			</div>
			<div class="col-xs-4">
				<select name="year" class="form-control">
					<?php for($i = intval(date('Y')); $i >= 1920; $i--) {?>
					<option value="<?php echo $i?>"><?php echo $i?></option>
					<?php }?>
				</select>
			</div>
		</div>
	</div>
</div>
