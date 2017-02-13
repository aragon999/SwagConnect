//{namespace name=backend/connect/view/main}
//{block name="backend/Property/view/main/group_grid" append}
Ext.define('Shopware.apps.Property.view.main.GroupGridConnect', {
    override: 'Shopware.apps.Property.view.main.GroupGrid',

    getColumns: function() {
        var me = this,
            newColumns = [],
            columns = me.callOverridden();

        columns.forEach(function(element) {
            if (columns.indexOf(element) == columns.length - 1) {
                newColumns.push(
                    {
                        width: 25,
                        renderer: me.connectColumnRenderer
                    }
                )
            }
            newColumns.push(element)
        });

        return newColumns;
    },

    connectColumnRenderer: function(value, metaData, record) {
        var result;
        var style = 'style="width: 16px; height: 16px; display: inline-block; position: absolute; margin-top: -1px;"';

        if (record.raw['connect']) {
            result = '<div  title="' + marketplaceName + '" class="connect-icon" ' + style + '>&nbsp;</div>';
        }

        return result;
    }

});
//{/block}