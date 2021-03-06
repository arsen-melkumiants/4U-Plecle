		<script src="/dist/js/jquery-1.10.2.min.js"></script>
		<script src="/dist/js/bootstrap.min.js"></script>
		<?php echo after_load('css');?>
		<?php echo after_load('js');?>
		<!-- The XDomainRequest Transport is included for cross-domain file deletion for IE 8 and IE 9 -->
		<!--[if (gte IE 8)&(lt IE 10)]>
		<script src="/js/upload/cors/jquery.xdr-transport.js"></script>
		<![endif]-->

		<script>
		var link_clicked = false;
		var show_order = function(url) {
			if (link_clicked) {
				link_clicked = false;
				return link_clicked;
			}

			window.location = url;
			return true;
		};

		$(function(){
			$('tr a').on('click', function() {
				link_clicked  = true;
			});


			$('a').tooltip();

			if (typeof $().selectpicker === 'function') {
				$('.selectpicker').selectpicker();
			}

			$(document).on('click', '.modal-body form button[type="submit"]', function() {
				var form = $('.modal-dialog').find('form');
				var action = form.attr('action');
				//var fields = $(":input").serializeArray();
				var fields = $(this).closest('form').serializeArray();
				fields.push({ name: this.name, value: this.value });
				if (this.name == 'cancel'){
					$('#ajaxModal').modal('hide');
					return false;
				}
				$.post(action, fields, function(data) {
					data = $.trim(data);
					if(data == 'refresh') {
						window.location.reload(true);
					} else if(data == 'close') {
						$('#ajaxModal').modal('hide');
					} else {
						$('#ajaxModal .modal-content').html(data);
					}
				});
				return false;
			});
			$(document).bind('hidden.bs.modal', function () {
				$('#ajaxModal').removeData('bs.modal')
			});

			$(document).on('loaded.bs.modal', function (e) {
				var result = $.trim(e.target.innerText);
				if(result == 'refresh') {
					window.location.reload(true);
				} else if(result == 'close') {
					$('#ajaxModal').hide().modal('hide');
				}
				if (typeof $().selectpicker === 'function') {
					$('.selectpicker').selectpicker('render');
				}
			});
		});
		</script>

		<?php if ($_SERVER['REQUEST_URI'] != '/') {?>
		<div class="footer_block">
			<div class="container">
				<hr>
				<div class="row">
					<div class="col-sm-3">© 2014 Plecle.com</div>
					<div class="col-sm-9">
						<div class="menu">
							<?php echo !empty($main_menu) ? $main_menu : '';?>
							<div class="clear"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php } else {?>
		<div class="footer_big_block">
			<div class="container">
				<div class="row">
					<div class="col-md-4">
						<div class="menu">
							<div class="title">Услуги</div>
							<?php echo $services_menu ?>
						</div>
					</div>
					<div class="col-md-4">
						<div class="menu">
							<div class="title">Районы</div>
							<ul>
								<?php if (!empty($regions)) {
								foreach ($regions as $item) {?>
								<li><a href="<?php echo site_url('region/'.$item['id'])?>"><?php echo $item['name']?></a></li>
								<?php }}?>
							</ul>
						</div>
					</div>
					<div class="col-md-4">
						<div class="menu">
							<div class="title">Справка</div>
							<?php echo $help_menu ?>
						</div>
					</div>
				</div>
				<hr />
				<div class="contact_block">Напишите нам info@plecle.com или позвоните нам +7(495)767-34-33. © 2014 Plecle.com</div>
			</div>
		</div>
		<?php }?>

		<div class="modal fade" id="ajaxModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content"></div>
			</div>
		</div>
	</body>
</html>
