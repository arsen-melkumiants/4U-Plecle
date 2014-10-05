<style>
	.modal-body {padding: 20px;}
	@media (min-width: 768px) {
		.modal-dialog {
			width: 750px;
			margin: 30px auto;
		}
	}
</style>
<form action="<?php echo current_url()?>" method="post">
	<div class="row">
		<div class="col-xs-6">
			<p><b>Вы можете выбрать дату и время, когда у Вас не получится поработать. В календаре это время будет отмечено как <font color="#888">"Занята"</font>. В таком случае Вы не сможете брать заказ на это время!</b></p>
			<?php echo $event_form?>
		</div>
		<div class="col-xs-6">
			<p><b>Ниже вы можете указать свои часы работы.<br />В нерабочее время невозможно взять заказ или принять предложение от клиента<br /><br /></b></p>
			<?php echo $days_off_form?>
		</div>
		<div class="col-sm-6 col-sm-offset-3">
			<button type="submit" class="btn btn-primary btn-lg btn-block">Сохранить</button>
		</div>
		<div class="col-sm-12 blue_text">
			<br />
			<div class="set_icon"></div> кликнув по этой иконке, вы всегда сможете настроить свои часы работы
		</div>
	</div>
</form>
