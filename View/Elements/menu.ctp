<div id="menu-area" style="display:none;">
<div id="basic">
	<ul>
		<?php 
			config('ext_direct');
			$ext_direct_models = Configure::read('ext_direct_models');
			foreach($ext_direct_models as $name => $params):
    	  if (isset($params['hidden']) && $params['hidden'] !== false)
    	    continue ;
		?>
		<li>
		    <?php if($name==='-'):?>
		      <br />
		    <?php else:?>
  				<?php 
  				  $tableize = Inflector::tableize($name);
  				?>
  				
  				
  				<?php if(isset($params['link']) && $params['link'] === true):?>
      			<?php 
              if(isset($params['controller']) && isset($params['action'])):
      				  echo $this->Html->link(__($name), array('controller'=>$params['controller'],'action'=>$params['action']));
              else:
                echo $this->Html->link(__(Inflector::classify($name),true), array('controller'=>Inflector::tableize($name)));
              endif;
      			?>
  				<?php else:?>
	          <?php if (isset($params['noconvert']) && $params['noconvert'] === true):?>
		          <a href="#!/<?php echo strtolower($name);?>" rel="<?php echo strtolower($name);?>" onClick="Ext.app.<?php echo strtolower($name);?>.load();"><?php echo __(Inflector::classify($name));?></a>
	          <?php else:?>
		          <a href="#!/<?php echo $tableize;?>" rel="<?php echo $tableize;?>" onClick="Ext.app.<?php echo $tableize;?>.load();"><?php echo __(Inflector::classify($name));?></a>
		        <?php endif;?>
		      <?php endif;?>
    		<?php endif;?>
		</li>
		<?php	endforeach; ?>
	</ul>
</div>
<div id="test">
</div>
</div>
