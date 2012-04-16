<div id="menu-area" style="display:none;">
<div id="basic">
	<ul>
		<?php 
			config('ext_direct');
			$ext_direct_models = Configure::read('ext_direct_models');
			foreach($ext_direct_models as $name => $params):
		?>
		<li>
		    <?php if($name==='-'):?>
		      <br />
		    <?php else:?>
  		    <?php if(isset($params['controller']) && isset($params['action'])):?>
    				<?php echo $this->Html->link(
    					__($name), 
    					array(
    						'controller'=>Inflector::tableize($params['controller']),
    						'action'=>$params['action'],
    					)
    				);?>
  		    <?php else:?>
    				<?php echo $this->Html->link(
    					__(Inflector::classify($name),true), 
    					array(
    						'controller'=>Inflector::tableize($name),
    					)
    				);?>
    			<?php endif;?>
    		<?php endif;?>
		</li>
		<?php	endforeach; ?>
	</ul>
</div>
</div>
