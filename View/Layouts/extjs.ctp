<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php __('Unknown Application:'); ?>
		<?php echo $title_for_layout; ?>
	</title>
	<?php
		echo $this->Html->meta('icon');

		echo $this->Html->css('/extjs/css/common');
		echo $this->Html->css('/extjs/css/loading');
		echo $this->Html->css('/extjs/css/flash');
		echo $this->Html->css('/resources/css/ext-all');
		echo $this->Html->css('/resources/js/ux/statusbar/css/statusbar');
		echo $this->Html->css('/resources/js/ux/css/CheckHeader');
		echo $this->Html->script('/resources/js/bootstrap');
		echo $this->Html->script('/resources/js/ux/statusbar/StatusBar');
		echo $this->Html->script('/extjs/direct/init');
		echo $scripts_for_layout;
	?>
</head>
<body>
<div class="container">
	<!--maincontents-->
	<div id="content">
		<?php echo $this->element('Extjs.menu'); ?>
		<div id='flash'></div>
		<?php echo $this->Session->flash(); ?>
		<?php echo $content_for_layout; ?>
	</div>
	<!--/maincontents-->
	<?php echo $this->element('Extjs.loading'); ?>
</div>
</body>
</html>