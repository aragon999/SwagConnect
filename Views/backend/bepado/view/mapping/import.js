//{namespace name=backend/bepado/view/main}

//{block name='backend/bepado/view/main/mapping/import'}
Ext.define('Shopware.apps.Bepado.view.mapping.Import', {
    extend: 'Ext.container.Container',
    alias: 'widget.bepado-mapping-import',

    //border: false,
    layout: 'border',

    snippets: {
        emptyMappingMessage: '{s name=mapping/message/please_assign_category}Bitte Kategorie zuordnen{/s}'
    },
    
    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
            {
                xtype: 'treepanel',
                region: 'center',
                rootVisible: false,
                root: {
                    id: 1,
                    expanded: true
                },
                store: 'mapping.Import',
                plugins: [{
                    ptype: 'cellediting',
                    pluginId: 'cellediting',
                    clicksToEdit: 1
                }],
                columns: [{
                    text: '{s name=mapping/columns/bepado-category}bepado Category{/s}',
                    flex: 1,
                    dataIndex: 'mapping',
                    editor: {
                        xtype: 'base-element-selecttree',
                        allowBlank: true,
                        store: 'mapping.BepadoCategoriesImport'
                    },
                    renderer: function (value) {
                        if (!value) {
                            return me.snippets.emptyMappingMessage;
                        }

                        return value;
                    }
                },{
                    xtype: 'treecolumn',
                    flex: 1,
                    dataIndex: 'text',
                    text: '{s name=mapping/columns/shopware-category}Shopware Category{/s}'
                }, me.getActionColumn()],
                dockedItems: [ me.getButtons() ]
            }]
        });

        me.addEvents(
            /**
             * Fired if the user clicks the "applyToChildren" action button.
             * Will apply the current mapping to all empty child categories
             */
            'applyToChildren'
        );

        me.callParent(arguments);
    },

    getActionColumn: function() {
        var me = this;
        return {
            xtype: 'actioncolumn',
            width: 110,
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
            }, {
                iconCls: 'sprite-folder-tree',
                action: 'importCategories',
                tooltip: '{s name=mapping/options/importCategories}Import categories from bepado{/s}',
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    me.fireEvent('importCategories', record);
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