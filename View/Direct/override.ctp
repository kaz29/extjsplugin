Ext.direct.RemotingProvider.override({
  constructor : function(config){
		var me = this;

		me.callOverridden(arguments);
			// CSRF tid を初期化
	  this.csrftid = null;
		if (Ext.app.csrftid != undefined) {
		  this.csrftid = Ext.app.csrftid;
		}
	},
	onData: function(options, success, response){
    var me = this;
    if (success) {
      var events = me.createEvents(response),
					i=0,
					len;
      for (len = events.length; i < len; ++i) {
        var event = events[i];
        
        if (event.result != undefined && event.result.redirecturl != undefined) {
          location.href = event.result.redirecturl;
          return;
        }
				if ( event.csrftid != undefined ) {
					this.csrftid = event.csrftid;
				} else {
				  event.csrftid = null;
				}
			}
		}
    me.callOverridden(arguments);
  },
	getCallData : function(transaction) {
		var me = this;

		calldata = me.callOverridden(arguments);
		calldata.csrftid = this.csrftid;
		
		return calldata;
	},
	configureFormRequest : function(action, method, form, callback, scope){
    var me = this,
        transaction = Ext.create('Ext.direct.Transaction', {
            provider: me,
            action: action,
            method: method.name,
            args: [form, callback, scope],
            callback: scope && Ext.isFunction(callback) ? Ext.Function.bind(callback, scope) : callback,
            isForm: true
        }),
        isUpload,
        params;

    if (me.fireEvent('beforecall', me, transaction, method) !== false) {
        Ext.direct.Manager.addTransaction(transaction);
        isUpload = String(form.getAttribute("enctype")).toLowerCase() == 'multipart/form-data';
        
        params = {
            extTID: transaction.id,
            extAction: action,
            extMethod: method.name,
            extType: 'rpc',
            extUpload: String(isUpload),
            extCSRFTID: this.csrftid
        };
        
        Ext.apply(transaction, {
            form: Ext.getDom(form),
            isUpload: isUpload,
            params: callback && Ext.isObject(callback.params) ? Ext.apply(params, callback.params) : params
        });
        me.fireEvent('call', me, transaction, method);
        me.sendFormRequest(transaction);
    }
  }
});

Ext.ns('Ext.app');
/*
 *  Convert CakePHP list array JavaScript array format.
 */
Ext.app.cakeListToArray = function(data, unselect) {
  var r = [];
	if ( unselect != undefined ) {
    r.push({id:0, name:unselect});
	}
  for (var d in data){
    r.push({id:d, name:data[d]});
  }
  
  return r;
};
