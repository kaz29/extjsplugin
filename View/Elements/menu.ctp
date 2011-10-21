<div id="menu-area" style="display:none;">
<div id="basic">
	<ul>
		<?php 
			config('ext_direct');
			$ext_direct_models = Configure::read('ext_direct_models');
			foreach($ext_direct_models as $name => $value):
		?>
		<li>
				<?php echo $this->Html->link(
					__(Inflector::classify($name),true), 
					array(
						'controller'=>Inflector::tableize($name),
					)
				);?>
		</li>
		<?php	endforeach; ?>
	</ul>
</div>
</div>
