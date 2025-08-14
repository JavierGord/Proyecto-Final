Ext.application({
    name: 'SGC',

    appFolder: 'js', // Carpeta base del frontend

    controllers: [
        'ClienteController'
    ],

    launch: function () {
        Ext.create('Ext.container.Viewport', {
            layout: 'fit',
            items: [
                {
                    xtype: 'panel',
                    title: 'Gestión de Clientes',
                    layout: 'fit',
                    items: [
                        {
                            xtype: 'grid',
                            title: 'Clientes',
                            itemId: 'gridClientes',
                            store: {
                                fields: ['id', 'nombre', 'cedula', 'correo'],
                                autoLoad: true,
                                proxy: {
                                    type: 'ajax',
                                    url: 'http://localhost:8000/clientes',
                                    reader: {
                                        type: 'json'
                                    }
                                }
                            },
                            columns: [
                                { text: 'ID', dataIndex: 'id', width: 50 },
                                { text: 'Nombre', dataIndex: 'nombre', flex: 1 },
                                { text: 'Cédula', dataIndex: 'cedula', flex: 1 },
                                { text: 'Correo', dataIndex: 'correo', flex: 1 }
                            ],
                            tbar: [
                                {
                                    text: 'Agregar Cliente',
                                    handler: function () {
                                        const win = Ext.create('Ext.window.Window', {
                                            title: 'Nuevo Cliente',
                                            modal: true,
                                            layout: 'fit',
                                            items: {
                                                xtype: 'form',
                                                bodyPadding: 10,
                                                defaults: {
                                                    xtype: 'textfield',
                                                    anchor: '100%',
                                                    allowBlank: false
                                                },
                                                items: [
                                                    { fieldLabel: 'Nombre', name: 'nombre' },
                                                    { fieldLabel: 'Cédula', name: 'cedula' },
                                                    { fieldLabel: 'Correo', name: 'correo', vtype: 'email' }
                                                ],
                                                buttons: [
                                                    {
                                                        text: 'Guardar',
                                                        formBind: true,
                                                        handler: function (btn) {
                                                            const form = btn.up('form').getForm();
                                                            if (form.isValid()) {
                                                                const values = form.getValues();
                                                                Ext.getController('ClienteController').crearCliente(values);
                                                                win.close();
                                                            }
                                                        }
                                                    }
                                                ]
                                            }
                                        });
                                        win.show();
                                    }
                                }
                            ]
                        }
                    ]
                }
            ]
        });
    }
});
