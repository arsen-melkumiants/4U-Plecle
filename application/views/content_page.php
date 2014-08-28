<div class="d_block prices">
	<img src="/img/top_bg2.jpg" />
	<?php if (strpos(mb_strtolower($title), 'цены') !== false || strpos(mb_strtolower($title), 'цена') !== false) {?>
	<div class="container">
		<h2 class="text-left"><span>Фиксированная цена</span></h2>
		<h1 class="text-center"><span><?php echo PRICE_PER_HOUR?> рублей / час</span></h1>
		<h2 class="text-right"><span>Онлайн оплата</span></h2>
	</div>
	<?php }?>
</div>
<?php $center_block = '<h1 class="text-center">'.$title.'</h1><hr />'.$center_block?>
<div class="container content_block">
	<div class="row">
		<?php if (($this->uri->segment(1) != 'make_order') && empty($this->ion_auth->user()->row()->is_cleaner)) {?>
		<div class="col-md-8">
			<?php echo $center_block?>
		</div>
		<div class="col-md-4">
			<div class="detail_block">
				<div class="title">Поиск работника</div>
				<div class="list">
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
		<?php } else {?>
		<div class="col-md-12">
			<?php echo $center_block?>
		</div>
		<?php }?>
	</div>
</div>
