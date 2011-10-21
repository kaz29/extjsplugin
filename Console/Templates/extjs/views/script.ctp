<?php echo "<?php echo \$this->Js->ns('{$modelClass}');?>"; ?>

<?php echo "<?php echo \$this->Js->getmodel('{$modelClass}');?>;"; ?>

Ext.app.<?php echo Inflector::tableize($modelClass); ?> = {
	store:null,
	grid:null,
	init: function() {
		<?php echo "<?php echo \$this->Js->addProvidor('{$modelClass}');?>";?>

		Ext.app.<?php echo Inflector::tableize($modelClass); ?>.store = <?php echo "<?php echo \$this->Js->store('{$modelClass}');?>;";?>
		Ext.app.<?php echo Inflector::tableize($modelClass); ?>.grid = Ext.create('Ext.grid.Panel', {
			store: Ext.app.<?php echo Inflector::tableize($modelClass); ?>.store,
			columns: <?php echo "<?php echo \$this->Js->columns('{$modelClass}');?>";?>,
			title: false,
			border: false,
			viewConfig: {
				stripeRows: true,
				listeners: {
					itemcontextmenu: function(view, rec, node, index, e) {
						e.stopEvent();
						Ext.app.<?php echo Inflector::tableize($modelClass); ?>.menu.showAt(e.getXY());
						return false;
					},
					itemdblclick: function(view, rec, node, index, e) {
						e.stopEvent();
						Ext.app.<?php echo Inflector::tableize($modelClass); ?>.edit();
						return false;
					}
				}
			},
			dockedItems: [{
				id: 'toolbar',
				itemId: 'toolbar',
				xtype: 'toolbar',
				items: Ext.app.<?php echo Inflector::tableize($modelClass); ?>.toolbarItems
			},{
				xtype: 'pagingtoolbar',
				store: Ext.app.<?php echo Inflector::tableize($modelClass); ?>.store,
				dock: 'bottom',
				displayInfo: true
			}],
			listeners:{
				selectionchange: function(sm, selections) {
					Ext.app.<?php echo Inflector::tableize($modelClass); ?>.updateButtonStatus(sm, selections) ;
				}
			}
		});

		var main_region = Ext.getCmp('main-region');
		main_region.add(Ext.app.<?php echo Inflector::tableize($modelClass); ?>.grid);
	},
	toolbarItems:[Ext.create('Ext.Action', {
		text: <?php echo "'<?php echo __('New');?>'";?>,
		disabled: false,
		handler: function() {
			Ext.app.<?php echo Inflector::tableize($modelClass); ?>.add();
		}
	}),Ext.create('Ext.Action', {
		text: <?php echo "'<?php echo __('Edit');?>'";?>,
		disabled: true,
		handler: function() {
			Ext.app.<?php echo Inflector::tableize($modelClass); ?>.edit();
		}
	}),Ext.create('Ext.Action', {
		text: <?php echo "'<?php echo __('Delete');?>'";?>,
		disabled: true,
		handler: function() {
			Ext.app.<?php echo Inflector::tableize($modelClass); ?>.delete();
		}
	})],
	updateButtonStatus: function(sm, selections) {
		var len = Ext.app.<?php echo Inflector::tableize($modelClass); ?>.toolbarItems.length,
				i;
				
		for(i=1; i < len; i++) {
			if ( selections.length == 1 ) {
				Ext.app.<?php echo Inflector::tableize($modelClass); ?>.toolbarItems[i].enable();
				Ext.app.<?php echo Inflector::tableize($modelClass); ?>.menu.items.getAt(i-1).enable();
			} else {
				Ext.app.<?php echo Inflector::tableize($modelClass); ?>.toolbarItems[i].disable();
				Ext.app.<?php echo Inflector::tableize($modelClass); ?>.menu.items.getAt(i-1).disable();
			}
		}
	},
	menu: Ext.create('Ext.menu.Menu', {
		items: [Ext.create('Ext.Action', {
			text: <?php echo "'<?php echo __('Edit');?>'";?>,
			disabled: true,
			handler: function(widget, event) {
				var selections = Ext.app.<?php echo Inflector::tableize($modelClass); ?>.grid.getSelectionModel().getSelection();
				if (selections.length == 1) {
					Ext.app.<?php echo Inflector::tableize($modelClass); ?>.edit();
				} else {
					Ext.Msg.alert(<?php echo "'<?php echo __('Error');?>'";?>, <?php echo "'<?php echo __('Please select the ".Inflector::tableize($modelClass).".');?>'";?>);			
				}
			}
		}),Ext.create('Ext.Action', {
			text: <?php echo "'<?php echo __('Delete');?>'";?>,
			disabled: true,
			handler: function(widget, event) {
				var selections = Ext.app.<?php echo Inflector::tableize($modelClass); ?>.grid.getSelectionModel().getSelection();
				if (selections.length == 1) {
					Ext.app.<?php echo Inflector::tableize($modelClass); ?>.delete();
				} else {
					Ext.Msg.alert(<?php echo "'<?php echo __('Error');?>'";?>, <?php echo "'<?php echo __('Please select the ".Inflector::tableize($modelClass).".');?>'";?>);			
				}
			}
		})]
	}),
	add: function () {
		var win = Ext.widget('window', {
			title: <?php echo "'<?php echo __('Create {$modelClass}');?>'";?>,
			closeAction: 'destroy',
			width: 600,
			layout: 'fit',
			resizable: false,
			modal: true,
			border: false,
			items:[<?php echo "<?php echo \$this->Js->form('{$modelClass}', array('api'=>array('submit'=>\"{$modelClass}.add\")));?>";?>]
		});
		
		win.show();
	},
	edit: function () {
		var selections = Ext.app.<?php echo Inflector::tableize($modelClass); ?>.grid.getSelectionModel().getSelection();
		if ( selections.length != 1 ) {
			Ext.Msg.alert(<?php echo "'<?php echo __('Error');?>'";?>, <?php echo "'<?php echo __('Please select the ".Inflector::tableize($modelClass).".');?>'";?>);			
			return ;
		}

		var win = Ext.widget('window', {
			title: <?php echo "'<?php echo __(\"Edit {$modelClass}\");?>'";?>,
			closeAction: 'destroy',
			width: 600,
			layout: 'fit',
			resizable: false,
			modal: true,
			border: false,
			items:[<?php echo "<?php echo \$this->Js->form('{$modelClass}', array('api'=>array('load'=>\"{$modelClass}.view\",'submit'=>\"{$modelClass}.edit\")));?>";?>]
		});
		
		var model = selections[0],
				form = win.down('form');

		Ext.app.status.loading();
		Ext.app.loading.show(<?php echo "'<?php echo __('Loading...');?>'";?>);
		form.getForm().load({
			params: {
				id: model.get('id'),
				escape:false
			},
			success: function(){
				Ext.app.status.ready();
				Ext.app.loading.hide();
			},
			failure: function(form, action) {
				Ext.app.status.error(action.result.message);
				Ext.app.loading.hide();
			},
			timeout: function(){
				Ext.app.status.error(<?php echo "'<?php echo __('Timeout.');?>'";?>);
				Ext.app.loading.hide();
			}

		});
		win.show();
	},
	delete:function() {
		var selections = Ext.app.<?php echo Inflector::tableize($modelClass); ?>.grid.getSelectionModel().getSelection();
		if (selections.length != 1) {
			Ext.Msg.alert(<?php echo "'<?php echo __('Error');?>'";?>, <?php echo "'<?php echo __('Please select the ".Inflector::tableize($modelClass).".');?>'";?>);
			return ;
		}
		
		var model = selections[0];
		Ext.Msg.confirm(<?php echo "'<?php echo __('Confirm');?>'";?>, <?php echo "'<?php echo __('Do you want to delete the ".Inflector::tableize($modelClass).".');?>'";?>, function(btn) {
			if (btn == "yes") {
				Ext.app.loading.show(<?php echo "'<?php echo __('Deleting');?>'";?>);
				<?php echo $modelClass; ?>.del(model.get('id'), function(provider, response) {
					Ext.app.loading.hide();
					if (response.result.success) {
						Ext.app.flash(response.result.message, 'notice');
						Ext.app.<?php echo Inflector::tableize($modelClass); ?>.store.load();
					} else {
						Ext.app.flash(response.result.message, 'error');
					}
				});
			}
		}, window);
	}
};