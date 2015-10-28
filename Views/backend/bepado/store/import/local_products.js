//{block name="backend/bepado/store/import/local_products"}
Ext.define('Shopware.apps.Bepado.store.import.LocalProducts', {
    extend : 'Ext.data.Store',

    autoLoad: false,
    pageSize: 5,
    fields: [
        { name: 'Article_id', type: 'integer' },
        { name: 'Detail_number',  type: 'string' },
        { name: 'Article_name',  type: 'string' },
        { name: 'Supplier_name',  type: 'string' },
        { name: 'Article_active',  type: 'boolean' },
        { name: 'Price_basePrice',  type: 'string' },
        { name: 'Tax_name',  type: 'string' }
    ],
    proxy : {
        type : 'ajax',
        api : {
            read : '{url controller=Import action=loadBothArticleTypes}'
        },
        reader : {
            type : 'json',
            root: 'data',
            totalProperty:'total'
        }
    }
});
//{/block}