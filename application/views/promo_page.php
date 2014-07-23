<?php if (!empty($partners)) {?>
<div class="main_block">
	<div class="container">
		<div class="row">
			<div class="col-md-12 partners_block">
				<div class="title">Возможно вы слышали о нас от:</div>
				<div class="inner">
					<?php foreach ($partners as $item) {?>
					<a href="<?php echo $item['link']?>"><img src="/uploads/partners/<?php echo $item['image']?>" alt="<?php echo $item['name']?>"/></a>
					<?php }?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php }?>
<div class="top_promo_block">
	<div class="container">
		<div class="row">
			<div class="small_title">Не требуется распоряжений</div>
			<h3>Найдите горничную, оплатите не отходя от компьютера, отдыхайте...</h3>
		</div>
		<div class="row">
			<div class="col-sm-4">
				<img src="img/promo_map.jpg" />
				<h4>Найдите рядом горничную</h4>
				<p>Введите свой почтовый индекс и мы найдем для вас ближайших, предварительно проверенных, работников, который знают толк в своём деле. Чистый дом - то, что нужно именно вам!</p>
			</div>
			<div class="col-sm-4">
				<img src="img/promo_devices.jpg" />
				<h4>Следите за работой</h4>
				<p>Следите за своим заказом онлайн. Можете это сделать с компьютера, телефона или со своего планшета. Вам больше не придется забывать, что нужно оставить деньги за работу на столе.</p>
			</div>
			<div class="col-sm-4">
				<img src="img/promo_tv.jpg" />
				<h4>Занимайтесь своими делами</h4>
				<p>Вот и всё! Мы хотели добавить третий шаг, но его просто нет. Теперь вам просто нужно выяснить как провести свободное время, когда Plecle.com следит за чистотой вашего дома.</p>
			</div>
		</div>
	</div>
</div>
<div class="middle_promo_block">
<h2>Оплатить онлайн просто. Цена всего <?php echo PRICE_PER_HOUR?> рублей/час</h2>
</div>
<div class="container bottom_promo_block">
	<div class="small_title">Идеальная чистота</div>
	<h3>Проверенные горничные, гарантировано!</h3>
	<hr />
	<p>Мы работаем с людьми, которых встретили и проверили лично, у которых есть богатый опыт и хорошая репутация. У нас только те работники, которые полностью отдаются своей работе и всегда приведут ваш дом в идеальный порядок.</p>
	<p>Также, мы рады подобрать для вас только самые лучшие и эффективные моющие средства, поэтому когда вы заказываете уборку на Plecle.com, ваш дом гарантировано будет идеально чистым и свежим. </p>
	<div class="row promo_list">
		<div class="col-sm-4">
			<div class="promo_sign"></div>
			<p>Местные профессиональные	работники</p>
		</div>
		<div class="col-sm-4">
			<div class="promo_sign"></div>
			<p>Только проверенные лица</p>
		</div>
		<div class="col-sm-4">
			<div class="promo_sign"></div>
			<p>100% гарантированный	возврат денег</p>
		</div>
	</div>
	<hr />
	<div class="small_title">Только профессиональные работники</div>
	<h3>Счастливые клиенты, чистые дома!</h3>
</div>
