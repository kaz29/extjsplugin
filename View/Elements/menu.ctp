<div id="menu-area" style="display:none;">
<div id="basic">
	<ul>
		<?php 
			config('ext_direct');
			$ext_direct_models = Configure::read('ext_direct_models');
			foreach($ext_direct_models as $name => $prop):
    	  if (isset($prop['hidden']) && $prop['hidden'] !== false)
    	    continue ;
		?>
		<li>
		    <?php if($name==='-'):?>
		      <br />
		    <?php else:?>
  				<?php 
  				  $tableize = Inflector::tableize($name);
  				?>
  				
  				
  				<?php if(isset($prop['link']) && $prop['link'] === true):?>
      			<?php 
              if(isset($prop['controller']) && isset($prop['action'])):
      				  echo $this->Html->link(__($name), array('controller'=>$prop['controller'],'action'=>$prop['action']));
              else:
                echo $this->Html->link(__(Inflector::classify($name),true), array('controller'=>Inflector::tableize($name)));
              endif;
      			?>
  				<?php else:?>
		        <a href="#!/<?php echo $tableize;?>" rel="<?php echo $tableize;?>" onClick="Ext.app.<?php echo $tableize;?>.load();"><?php echo __(Inflector::classify($name));?></a>
		      <?php endif;?>
    		<?php endif;?>
		</li>
		<?php	endforeach; ?>
	</ul>
</div>
<div id="test">
</div>
</div>
