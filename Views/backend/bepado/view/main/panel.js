//{namespace name=backend/bepado/view/main}

//{block name="backend/bepado/view/main/panel"}
Ext.define('Shopware.apps.Bepado.view.main.Panel', {
    extend: 'Ext.container.Container',
    alias: 'widget.bepado-panel',

    border: false,
    layout: 'card',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [{
                xtype: 'bepado-home-page',
                itemId: 'home'
            }, {
                xtype: 'bepado-changed-products',
                itemId: 'changed'
            }, {
                xtype: 'bepado-config',
                itemId: 'config'
            }, {
                xtype: 'bepado-config-general',
                itemId: 'config-general'
            }, {
                xtype: 'bepado-config-import',
                itemId: 'config-import'
            }, {
                xtype: 'bepado-config-export',
                itemId: 'config-export'
            }, {
                xtype: 'bepado-prices',
                itemId: 'prices'
            }, {
                xtype: 'bepado-mapping',
                itemId: 'mapping'
            }, {
                xtype: 'bepado-export',
                itemId: 'export'
            }, {
                xtype: 'bepado-import',
                itemId: 'import'
            }, {
                xtype: 'bepado-log',
                itemId: 'log'
            }]
        });

        me.callParent(arguments);
    }
});
//{/block}