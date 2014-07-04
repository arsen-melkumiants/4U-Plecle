<form method="post" class="order_form">
	<div>
		<h4 class="title">Заполните форму сделки</h4>
		<div class="row">
			<?php echo $order_form?>
		</div>
		
		<?php if (!empty($login_form)) {?>
		<h4 class="title">
			<a href="#registration_form" role="tab" data-toggle="tab">Ваши персональные данные</a>
			<a href="#login_form" role="tab" data-toggle="tab" class="right_link">Я уже зарегистрирован</a></span>
		</h4>
		<div id="myTabContent" class="tab-content row">
			<div class="tab-pane fade<?php echo $this->input->cookie('of_tab') != 'login_form' ? ' active in' : ''?>" id="registration_form">
				<?php echo $registration_form?>
			</div>
			<div class="tab-pane fade<?php echo $this->input->cookie('of_tab') == 'login_form' ? ' active in' : ''?>" id="login_form">
				<?php echo $login_form?>
			</div>
		</div>
		<?php }?>
		
		<h4 class="title">Ваш адрес</h4>
		<div class="row">
			<?php echo $address_form?>
		</div>
		<h4 class="title">Заметки для горничной</h4>
		<?php echo $commnet_form?>
		<small>Если для уборки  вашего дома нужны моющие средства, которые у вас отсутствуют, то укажите их пожалуйста, чтобы горничная взяла их с собой.</small>
		<br />
		<br />
		<br />
		<div class="row">
			<div class="col-sm-6 col-sm-offset-3">
				<button type="submit" class="btn btn-primary btn-lg btn-block">Оформить заявку</button>
			</div>
		</div>
		<br />
		<br />
		<br />
	</div>
</form>

<?php after_load('js', '/dist/js/jquery.cookie.js')?>
<script>
	window.onload = function () {
		$('a[data-toggle="tab"]').on('shown.bs.tab', function () {
			console.log($(this).attr('href').substring(1));
			$.cookie('of_tab', $(this).attr('href').substring(1));
		})
		<?php if (!empty($is_login)) {?>
			$('.order_form').submit();
		<?php }?>
	}
</script>
