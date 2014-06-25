<?php if ($this->ion_auth->user()->row()->is_seller) {?>
<ul>
	<li><a class="drop" href="<?php echo site_url('profile')?>"><?php echo lang('my_profile')?></a></li>
	<li><a class="drop" href="<?php echo site_url('profile/products')?>"><?php echo lang('my_sales')?></a></li>
	<li><a class="drop" href="<?php echo site_url('profile/finance')?>"><?php echo lang('my_finance')?></a></li>
	<li><a class="simple" href="<?php echo site_url('personal/logout')?>"><?php echo lang('logout')?></a></li>
</ul>
<?php } else {?>
<ul>
	<li><a class="drop" href="<?php echo site_url('profile')?>"><?php echo lang('my_profile')?></a></li>
	<li><a class="drop" href="<?php echo site_url('profile/orders')?>"><?php echo lang('my_orders')?></a></li>
	<li><a class="drop" href="<?php echo site_url('profile/finance')?>"><?php echo lang('my_finance')?></a></li>
	<li><a class="drop" href="<?php echo site_url('cart')?>"><?php echo lang('cart')?></a></li>
	<li><a class="simple" href="<?php echo site_url('personal/logout')?>"><?php echo lang('logout')?></a></li>
</ul>
<?php }?>
