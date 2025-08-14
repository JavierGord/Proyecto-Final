Ext.define('SGC.view.ClienteForm', {
  extend: 'Ext.window.Window',
  title: 'Nuevo Cliente',
  width: 400,
  layout: 'fit',
  modal: true,
  items: [
    {
      xtype: 'form',
      bodyPadding: 10,
      defaults: {
        anchor: '100%',
        allowBlank: false
      },
      items: [
        { xtype: 'textfield', name: 'tipo', fieldLabel: 'Tipo' },
        { xtype: 'textfield', name: 'nombres', fieldLabel: 'Nombres' },
        { xtype: 'textfield', name: 'apellidos', fieldLabel: 'Apellidos' },
        { xtype: 'textfield', name: 'razonSocial', fieldLabel: 'Razón Social' },
        { xtype: 'textfield', name: 'email', fieldLabel: 'Email', vtype: 'email' },
        { xtype: 'textfield', name: 'telefono', fieldLabel: 'Teléfono' }
      ],
      buttons: [
        {
          text: 'Guardar',
          formBind: true,
          handler: function (btn) {
            const form = btn.up('form').getForm();
            if (form.isValid()) {
              form.submit({
                url: 'http://localhost:8000/api/clientes',
                method: 'POST',
                success: function () {
                  Ext.Msg.alert('Éxito', 'Cliente guardado correctamente');
                  btn.up('window').close();
                  Ext.getStore('clientes').load();
                },
                failure: function () {
                  Ext.Msg.alert('Error', 'No se pudo guardar el cliente');
                }
              });
            }
          }
        },
        {
          text: 'Cancelar',
          handler: function (btn) {
            btn.up('window').close();
          }
        }
      ]
    }
  ]
});
