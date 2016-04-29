//{namespace name=backend/connect/view/main}

//{block name="backend/connect/view/export/stream/panel"}
Ext.define('Shopware.apps.Connect.view.export.stream.Panel', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.connect-export-stream',

    border: false,
    layout: 'border',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [{
                xtype: 'connect-export-stream-list',
                iconMapping: me.iconMapping,
                region: 'center'
            }],
            dockedItems: [
                {
                    xtype: 'toolbar',
                    dock: 'bottom',
                    ui: 'shopware-ui',
                    cls: 'shopware-toolbar',
                    items: me.getFormButtons()
                }
            ]
        });

        me.callParent(arguments);
    },

    /**
     * Returns form buttons, export and remove
     * @returns Array
     */
    getFormButtons: function () {
        var items = ['->'];
        items.push({
            text:'{s name=export/options/delete}Löschen{/s}',
            action:'remove'
        });
        items.push({
            cls: 'primary',
            text:'{s name=export/options/Export}Export{/s}',
            action:'add'
        });

        return items;
    }
});
//{/block}