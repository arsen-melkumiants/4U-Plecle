<div class="main_block">
	<div class="cleaner_block">
		<div class="container">
			<div class="row">
				<div class="col-sm-12">
					<div class="big_status">Станьте горничной на Plecle.com</div>
					<?php if (defined('CLEANER_SALARY')) {?>
					<h2>И зарабатывайте по <?php echo CLEANER_SALARY?> рублей в час</h2>
					<?php }?>
					<div class="add_title">Появилась пыль? Нужно навести порядок?</div>
				</div>
			</div>
		</div>
	</div>
	<div class="container">
	<?php echo get_alerts();?>
		<div class="row">
			<form method="post">
				<div class="col-md-8">
					<h4 class="title">Поставьте соответствующие галочки</h4>
					<div class="form-group">
						<div class="col-sm-12">
							<?php foreach($options as $key => $info) { ?>
							<div class="checkbox"><label><input type="checkbox" name="options[<?php echo $key?>]" value="1" <?php echo !empty($result_options[$key]) ? 'checked="checked"' : '' ?>> <?php echo $info?></label></div>
							<?php }?>
						</div>
					</div>
					<h4 class="title">Ваши персональные данные</h4>
					<div class="row">
						<?php echo $user_info_form?>
					</div>
					<h4 class="title">Ваш адрес</h4>
					<div class="row">
						<?php echo $address_form?>
					</div>
					<h4 class="title">Правила сайта</h4>
					<?php echo $confirm?>
					<br />
					<br />
					<div class="row">
						<div class="col-sm-6 col-sm-offset-3">
							<button type="submit" class="btn btn-primary btn-lg btn-block">Зарегистрироваться</button>
						</div>
					</div>
					<br />
					<br />
				</div>
			</form>
			<div class="col-md-4">
				<div class="detail_block">
					<div class="title">Как это работает</div>
					<div class="list">
						<ol>
							<li>Клиенты ищут ближайших работников</li>
							<li>Plecle.com отсылает вам детали заказа</li>
							<li>Вы соглашаетесь на сделку</li>
							<li>Если вы первый - сделка ваша!</li>
							<li>Вам выплачивают гонорар после работы</li>
						</ol>
					</div>
				</div>
				<div class="detail_block">
					<div class="title">Сколько я заработаю?</div>
					<div class="list">
						<ul>
							<li>Клиенты ищут ближайших работников</li>
							<?php if (defined('CLEANER_SALARY')) {?>
							<li>Вы получаете <?php echo CLEANER_SALARY?> рублей за час работы</li>
							<?php }?>
							<li>Мы выплачиваем вам на банковский счет</li>
						</ul>
					</div>
				</div>
				<div class="detail_block">
					<div class="title">Почему Plecle.com?</div>
					<div class="list">
						<ul>
							<li>Вы сами выбираете заказы</li>
							<li>Вы зарабатываете деньги почасово</li>
							<li>Вам не нужно выходить с дома для поиска</li>
							<li>Вы следиете за заказами онлайн</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
