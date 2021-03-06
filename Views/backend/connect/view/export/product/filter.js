//{namespace name=backend/connect/view/main}

//{block name="backend/connect/view/export/product/filter"}
Ext.define('Shopware.apps.Connect.view.export.product.Filter', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.connect-export-filter',

    width: 200,
    layout: 'accordion',
    border: false,
    animate: Ext.isChrome,

    initComponent: function() {
        var me = this;

        me.statusFilter = me.getStatusFilter();
        me.categoryFilter = me.getCategoryFilter();
        me.supplierFilter = me.getSupplierFilter();
        me.searchFilter = me.getSearchFilter();

        Ext.applyIf(me, {
            items: [
                me.categoryFilter,
                me.statusFilter,
                me.searchFilter,
                me.supplierFilter
            ]
        });

        me.callParent(arguments);
    },

    getCategoryFilter: function() {
        var me = this;
        return {
            xtype: 'panel',
            title: '{s name=export/filter/category_title}Category filter{/s}',
            layout: { type: 'hbox', align: 'stretch' },
            items: [{
                xtype: 'treepanel',
                id: 'export-category-filter',
                rootVisible: false,
                root: {
                    id: 1,
                    expanded: true
                },
                store: 'base.CategoryTree',
                flex: 1,
                dockedItems: [
                    me.createTreeBottomBar()
                ]
            }]
        }
    },

    createTreeBottomBar: function () {
        return { xtype: 'toolbar',
            dock: 'bottom',
            items: [{
                    xtype: 'button',
                    text: '{s name=export/filter/clear_category_filter}Clear category filter{/s}',
                    action: 'category-clear-filter'
            }]
        }
    },

    getStatusFilter: function() {
        return {
            xtype: 'form',
            title: '{s name=export/filter/status_title}Status filter{/s}',
            bodyPadding: 5,
            items: [{
                xtype: 'fieldcontainer',
                defaultType: 'radiofield',
                items: [{
                    boxLabel  : '{s name=export/filter/status_show_all}Alle anzeigen{/s}',
                    name      : 'exportStatus',
                    inputValue: '',
                    checked   : true
                }, {
                    boxLabel  : '{s name=export/filter/status_online}Online{/s}',
                    name      : 'exportStatus',
                    inputValue: 'synced'
                }, {
                    boxLabel  : '{s name=export/filter/status_error}Error{/s}',
                    name      : 'exportStatus',
                    inputValue: 'error'
                }, {
                    boxLabel  : '{s name=export/filter/status_insert}Inserting{/s}',
                    name      : 'exportStatus',
                    inputValue: 'insert'
                }, {
                    boxLabel  : '{s name=export/filter/status_update}Updating{/s}',
                    name      : 'exportStatus',
                    inputValue: 'update'
                }, {
                    boxLabel  : '{s name=export/filter/status_delete}Delete{/s}',
                    name      : 'exportStatus',
                    inputValue: 'delete'
                },  {
                    boxLabel  : '{s name=export/filter/status_inactive}Inactive{/s}',
                    name      : 'exportStatus',
                    inputValue: 'inactive'
                }]
            }]
        }
    },

    getSupplierFilter: function() {
        return {
            xtype: 'form',
            title: '{s name=export/filter/supplier_title}Supplier filter{/s}',
            height: 65,
            bodyPadding: 5,
            items: [{
                xtype: 'combo',
                name: 'supplierId',
                displayField: 'name',
                valueField: 'id',
                anchor: '100%',
                allowBlank: true,
                pageSize: 25,
                store: 'base.Supplier'
            }]
        }
    },

    getSearchFilter: function() {
        return {
            xtype: 'form',
            title: '{s name=export/filter/search_title}Search{/s}',
            height: 65,
            bodyPadding: 5,
            items: [{
                xtype:'textfield',
                name:'searchfield',
                anchor: '100%',
                cls:'searchfield',
                emptyText:'{s name=export/filter/search_empty}Search...{/s}',
                enableKeyEvents:true,
                checkChangeBuffer:500
            }]
        }
    }
});
//{/block}