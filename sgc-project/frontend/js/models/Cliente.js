Ext.define('SGC.model.Cliente', {
  extend: 'Ext.data.Model',
  fields: [
    { name: 'id', type: 'int' },
    'tipo',
    'nombres',
    'apellidos',
    'razonSocial',
    'email',
    'telefono'
  ]
});
