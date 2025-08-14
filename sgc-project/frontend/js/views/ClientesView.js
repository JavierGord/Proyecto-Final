Ext.define('SGC.view.ClientesView', {
  extend: 'Ext.grid.Panel',
  xtype: 'clientesview',
  title: 'Gestión de Clientes',
  store: {
    type: 'clientes'
  },
  columns: [
    { text: 'ID', dataIndex: 'id', width: 50 },
    { text: 'Tipo', dataIndex: 'tipo' },
    { text: 'Nombres', dataIndex: 'nombres', flex: 1 },
    { text: 'Apellidos', dataIndex: 'apellidos', flex: 1 },
    { text: 'Razón Social', dataIndex: 'razonSocial', flex: 1 },
    { text: 'Email', dataIndex: 'email' },
    { text: 'Teléfono', dataIndex: 'telefono' }
  ],
  tbar: [
    {
      text: 'Nuevo Cliente',
      handler: function () {
        Ext.create('SGC.view.ClienteForm').show();
      }
    }
  ]
});
