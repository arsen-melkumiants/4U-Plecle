<div class="main_form">
	<img src="img/top_bg.jpg" />
	<div>
		<h1>Закажите проверенную горничную за 60 секунд</h1>
		<h3>Потому, что есть споcобы лучше провести выходные!</h3>
		<div class="container">
			<div class="row">
				<div class="col-sm-offset-4 col-sm-4 form_body">
					<p>Онлайн платежи, фиксированная цена <?php echo PRICE_PER_HOUR?> рублей/час</p>
					<form class="form-horizontal" method="post" action="<?php echo site_url('make_order')?>">
						<div class="form-group">
							<div class="col-md-12">
								<label class="sr-only">Введите свой индекс</label>
								<input type="text" name="zip" class="form-control" placeholder="Введите свой индекс">
							</div>
						</div>
						<div class="form-group">
							<div class="col-md-12">
								<button class="btn btn-primary btn-block" modal="" value="Вход" type="submit">Найти работника</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
