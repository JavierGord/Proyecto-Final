Ext.define('SGC.store.ClienteStore', {
  extend: 'Ext.data.Store',
  alias: 'store.clientes',
  model: 'SGC.model.Cliente',
  autoLoad: true,
  storeId: 'clientes',
  proxy: {
    type: 'ajax',
    url: 'http://localhost:8000/api/clientes',
    reader: {
      type: 'json',
      rootProperty: 'data'
    }
  }
});
