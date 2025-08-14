Ext.define('SGC.controller.ClienteController', {
    extend: 'Ext.app.Controller',

    crearCliente: function (cliente) {
        Ext.Ajax.request({
            url: 'http://localhost:8000/clientes',
            method: 'POST',
            jsonData: cliente,
            success: function (response) {
                const res = Ext.decode(response.responseText);
                if (res.success) {
                    Ext.Msg.alert('Éxito', res.message);
                    // Recargar el store del grid
                    const grid = Ext.ComponentQuery.query('#gridClientes')[0];
                    if (grid) {
                        grid.getStore().reload();
                    }
                } else {
                    Ext.Msg.alert('Error', res.message);
                }
            },
            failure: function (response) {
                Ext.Msg.alert('Error', 'Error en la petición: ' + response.statusText);
            }
        });
    }
});
