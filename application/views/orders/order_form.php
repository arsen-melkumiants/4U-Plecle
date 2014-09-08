<form method="post" class="order_form">
	<div>
		<h4 class="title">Заполните форму сделки</h4>
		<div class="row">
			<?php echo $order_form?>
		</div>

		<?php if (!empty($login_form)) {?>
		<h4 class="title">
			<a href="#registration_form" role="tab" data-toggle="tab">Ваши персональные данные</a>
			<a data-toggle="modal" data-target="#ajaxModal" href="<?php echo site_url('personal/login')?>" class="right_link">Я уже зарегистрирован</a>
			<?php /*<a href="#login_form" role="tab" data-toggle="tab" class="right_link">Я уже зарегистрирован</a>*/?>
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

<?php after_load('js', '/dist/js/moment-with-langs.min.js')?>
<?php after_load('js', '/dist/js/bootstrap-datetimepicker.min.js')?>
<?php after_load('js', '/dist/js/bootstrap-datetimepicker.ru.js')?>
<?php after_load('css', '/dist/css/bootstrap-datetimepicker.min.css')?>

<?php after_load('js', '/dist/js/jquery.cookie.js')?>
<script>
var price_per_hour = Number(<?php echo PRICE_PER_HOUR?>);
var detergent_price = Number(<?php echo DETERGENT_PRICE?>);
var collect_price = function() {
	var duration = Number($('[name="duration"]').val());
	var cleaning_price = duration * price_per_hour;
	var need_detergents = $('[name="need_detergents"]').prop("checked") ? duration * detergent_price : 0;
	$('.cleaning_price').text(cleaning_price);
	$('.detergent_price').text(need_detergents);
	$('.total_price').text(cleaning_price + need_detergents);
};

var init_order_picker = function() {
	$('.date_time').datetimepicker({
		language       : 'ru',
		useCurrent     : false,
		minuteStepping : 30,
		minDate        : moment().day(1),
	}).on('dp.change',function (e) {
		var min_date = moment().day(1);
		var max_date = moment().day(3);
		$('.date_time').closest('.form-group').find('.text-warning').remove();
		if (e.date > min_date && e.date < max_date) {
			$('.date_time').closest('.form-group').append('<div class="text-warning col-md-6">Мы не гарантируем, что найдем работника в указанную дату. Рекомендуем выбрать более позднюю дату</div>');
		}
	});
}

window.onload = function () {
	$('[name="duration"]').on('change', function() {
		collect_price();
	});

	$('[name="need_detergents"]').on('change', function() {
		collect_price();
	});

	init_order_picker();

	$('input').on('keyup', function() {
		var input = $(this);
		setTimeout(function() {
			$('input[name="' + input.attr('name') + '"]').val(input.val());
		}, 100);
	});

	$('input').on('change', function() {
		var input = $(this);
		$('input[name="' + input.attr('name') + '"]').val(input.val());
	});

	$('a[data-toggle="tab"]').on('shown.bs.tab', function () {
		$.cookie('of_tab', $(this).attr('href').substring(1));
	})
	<?php if (!empty($is_login)) {?>
		$('.order_form').submit();
	<?php }?>
}
</script>
