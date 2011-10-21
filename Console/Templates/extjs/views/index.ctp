<?php echo "<?php \$this->Html->script(Router::url(array('action'=>'script')), array('inline' => false)); ?>"; ?>

<script>
Ext.onReady(function() {
  Ext.app.init('<?php echo $pluralHumanName;?>');

	Ext.getCmp('basic-panel').expand();
	Ext.app.<?php echo Inflector::tableize($modelClass); ?>.init();
});
</script>
