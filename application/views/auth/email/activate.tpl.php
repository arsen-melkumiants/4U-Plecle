<html>
	<body>
		<?php if (empty($_COOKIE['user_lang']) || $_COOKIE['user_lang'] == 'ru') {?>
		<h4>Активация аккаунта <?php echo $identity;?></h4>
		<p>Чтоб активировать аккаунт перейдите пожалуйста по ссылке <?php echo anchor('personal/activate/'.$id.'/'.$activation, 'Активировать аккаунт');?>.</p>
		<p>После перехода по ссылке вы сможете авторизоваться</p>
		<br />
		<p>При невозможности перейти по ссылке высше - скопируйте ссылку <?php echo base_url('personal/activate/'. $id .'/'. $activation)?> в адресную строку браузера самостоятельно.</p>
		<br />
		<p>С уважением, Администрация сайта <a href="<?php echo base_url()?>"><?php echo SITE_NAME?></a></p>
		<br />
		<br />
		<br />
		<br />
		<br />
		<?php }?>
	</body>
</html>
