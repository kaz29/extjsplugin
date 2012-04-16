Ext.ns("Ext.app"); 

Ext.app = {
	vars:null,
	init: function(maintitle){
	  // CakePHP の Flashメッセージが設定されている場合に独自形式のFlashに置き換え
	  // CSS で flashMessage を display:noneにしておくこと
	  var flashEl = Ext.get('flashMessage');
	  if (flashEl && flashEl.dom) {
	    Ext.app.flash(flashEl.dom.innerHTML, flashEl.dom.className);
	  }

		Ext.app.items.main.title = maintitle;
		Ext.create('Ext.Viewport', {
	    layout: {
	      type: 'border',
	      padding: '0 0 0 0'
	    },
	    defaults: {
	      split: true
	    },
	    items: [
				Ext.app.items.header,
				Ext.app.items.menu,
				Ext.app.items.main,
				Ext.app.items.status
			]
	  });
	
		Ext.get('menu-area').show();
	},
	items:{
		header:{
      xtype: 'box',
      id: 'header',
      region: 'north',
      html: '<h1><?php echo __('Unknown Application'); ?></h1>',
      height: 30,
	    split: false,
      margins: '0 0 5 0',
    },
		menu:{
      region: 'west',
      layout: 'accordion',
      id: 'menu-region',
      collapsible: true,
      title: 'Menu',
      border: true,
      width: 200,
			autoHeight: true,
      margins: '0 0 2 5',
		  defaults: {           
        bodyStyle: 'padding:5px',
				collapsed: true,
				border: false
      },
			items:[{
				id:'basic-panel',
	      contentEl: 'basic',
				title: 'Functions'
			}]
		},
		main:{
      region: 'center',
      layout: 'fit',
      id: 'main-region',
      margins: '0 3 2 0',
      border: true,
      title: 'Center'
		},
		status:{
      region: 'south',
      layout: 'fit',
      id: 'footer-region',
      margins: '0 3 1 5',
      border: true,
      split: false,
			bbar: Ext.create('Ext.ux.StatusBar', {
        id: 'basic-statusbar',

        // defaults to use when the status is cleared:
        defaultText: 'Ready',
    
        // values to set initially:
        text: 'Ready',
        iconCls: 'x-status-valid',

        // any standard Toolbar items:
        items: [
        '-',
        '<?php echo __('Unknown Application'); ?>'
        ]
      })
	  }
	},
	status:{
		loading: function(message) {
      var sb = Ext.getCmp('basic-statusbar');
      // once completed
      sb.showBusy(message);
		},
		ready: function(message){
			message = (message!=undefined)?message:'Ready';
      var sb = Ext.getCmp('basic-statusbar');
      sb.setStatus({
        text: message,
        iconCls: 'x-status-valid'
      });
		},
		error: function(message){
      var sb = Ext.getCmp('basic-statusbar');
      sb.setStatus({
        text: message,
        iconCls: 'x-status-error'
      });
		}
	},
	loading:{
		show: function(message){
			var loading = Ext.get( 'loading' );
			var mask = Ext.get( 'loading-mask' );
			var msgEl = Ext.get('loading-message');

			msgEl.update(message);

			mask.setOpacity( .0 );
			loading.setOpacity( .0 );
			mask.show();
			loading.show();
			this.task = new Ext.util.DelayedTask(function(){
				mask.setOpacity( .3 );
				loading.setOpacity( 1 );
			});

			this.task.delay(500) ;		
		},
		hide: function() {
			this.task.cancel();

			var loading = Ext.get( 'loading' );
			var mask = Ext.get( 'loading-mask' );

			mask.hide();
			loading.hide();
		}
	},
	flash: function(message, cls, time) {
	  var tt = (time == undefined)?5000:time;
	  var cc = (cls == undefined)?'flash notice':'flash '+cls;

	  var flashPanel = Ext.create('Ext.Component', {
	    renderTo: 'flash',
	    width:    '90%',
	    html:     message,
	    cls:       cc,
	    id:       'flash-message'
	  });
  
	  this.task = new Ext.util.DelayedTask(function(){
	    Ext.get("flash-message").fadeOut({
	      endOpacity: 0,
	      easing: 'easeOut',
	      duration: 500,
	      remove: true,
	      useDisplay: false
	    });
	  });

	  this.task.delay(tt) ;    
	}
};