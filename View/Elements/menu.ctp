<div id="menu-area" style="display:none;">
<div id="basic">
	<ul>
		<?php 
			config('ext_direct');
			$ext_direct_models = Configure::read('ext_direct_models');
			foreach($ext_direct_models as $name => $prop):
		?>
		<li>
		    <?php if($name==='-'):?>
		      <br />
		    <?php else:?>
  				<?php $tableize = Inflector::tableize($name);?>
		      <a href="#!/<?php echo $tableize;?>" rel="<?php echo $tableize;?>" onClick="Ext.app.<?php echo $tableize;?>.load();"><?php echo __(Inflector::classify($name));?></a>
    		<?php endif;?>
		</li>
		<?php	endforeach; ?>
	</ul>
</div>
<div id="test">
</div>
</div>
