//{namespace name=backend/bepado/view/main}

//{block name='backend/bepado/view/main/mapping'}
Ext.define('Shopware.apps.Bepado.view.main.Mapping', {
    extend: 'Ext.container.Container',
    alias: 'widget.bepado-mapping',

    //border: false,
    layout: 'fit',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [{
                xtype: 'treepanel',
                rootVisible: false,
                root: {
                    id: 1,
                    expanded: true
                },
                store: 'main.Mapping',
                plugins: [{
                    ptype: 'cellediting',
                    pluginId: 'cellediting',
                    clicksToEdit: 1
                }],
                columns: [{
                    xtype: 'treecolumn',
                    flex: 1,
                    dataIndex: 'text',
                    text: '{s name=mapping/columns/category}Category{/s}'
                },{
                    text: '{s name=mapping/columns/mapping}Mapping{/s}',
                    flex: 1,
                    dataIndex: 'mapping',
                    editor: {
                        xtype: 'base-element-selecttree',
                        allowBlank: true,
                        store: 'main.Category'
                    }
                }, me.getActionColumn()],
                dockedItems: [ me.getButtons() ]
            }]
        });

        me.callParent(arguments);
    },

    getActionColumn: function() {
        var me = this;
        return {
            xtype: 'actioncolumn',
            width: 30,
            items: [{
                iconCls: 'sprite-minus-circle-frame',
                action: 'clear',
                tooltip: '{s name=mapping/options/clear}Clear mapping{/s}',
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    record.set('mapping', null);
                },
                getClass: function(value, meta, record) {
                    return record.get('mapping') ? 'x-grid-center-icon': 'x-hide-display';
                }
            }]
        };
    },

    getButtons: function() {
        var me = this;

        return {
            dock: 'bottom',
            xtype: 'toolbar',
            items: ['->', {
                text: '{s name=mapping/options/save}Save{/s}',
                cls: 'primary',
                action: 'save'
            }]
        };
    }
});
//{/block}