/**
 * Shopware 4
 * Copyright © shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */
/**
 * Shopware SwagBepado Plugin
 *
 * @category Shopware
 * @package Shopware\Plugins\SwagBepado
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
//{namespace name=backend/bepado/view/main}
//{block name="backend/bepado/view/shipping_groups/add_rule"}
Ext.define('Shopware.apps.Bepado.view.config.shippingGroups.AddRule', {
    extend: 'Ext.window.Window',
    alias: 'widget.bepado-shipping-add-rule',

    layout: 'fit',
    width: 500,
    height: 300,
    modal: true,
    title: '{s name=config/shipping_groups/add_rule}Add rule{/s}',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [ me.getForm(),
                me.getButtons()]
        });

        me.callParent(arguments);
    },

    /**
     * Returns generated shipping rule form
     */
    getForm: function() {
        var me = this;
        var countryStore = Ext.create('Shopware.apps.Base.store.Country').load();


        return {
            xtype: 'form',
            url: '{url controller=ShippingGroups action=createShippingRule}',
            layout: 'anchor',
            bodyPadding: 10,
            defaults: {
                anchor: '100%'
            },
            items: [
                {
                    xtype: 'combobox',
                    name: 'groupId',
                    store: 'shippingGroup.Groups',
                    displayField: 'groupName',
                    valueField: 'id',
                    fieldLabel: '{s name=config/shipping_groups/shipping_group}Shipping group{/s}',
                    allowBlank: false
                }, {
                    xtype: 'combobox',
                    name: 'country',
                    store: countryStore,
                    displayField: 'name',
                    valueField: 'iso',
                    fieldLabel: '{s name=config/shipping_groups/country_header}Country{/s}',
                    allowBlank: false
                }, {
                    xtype: 'numberfield',
                    name: 'deliveryDays',
                    allowBlank: false,
                    fieldLabel: '{s name=config/shipping_groups/delivery_time}Delivery time in days{/s}',
                    maxValue: 99,
                    minValue: 1,
                    step: 1
                }, {
                    xtype: 'numberfield',
                    name: 'price',
                    fieldLabel: '{s name=config/shipping_groups/price}Price{/s}',
                    allowBlank: false,
                    submitLocaleSeparator: false,
                    minValue: 0
                }, {
                    xtype: 'textfield',
                    name: 'zipPrefix',
                    fieldLabel: '{s name=config/shipping_groups/zip_prefix}Zip prefix{/s}',
                    allowBlank: true
                }
            ]
            ,
            buttons: [ me.getButtons() ]
        };
    },

    /**
     * Creates save bottom buttons
     * @returns string
     */
    getButtons: function() {
        var me = this;
        return {
            text: '{s name=config/shipping_groups/save}Save{/s}',
            cls: 'primary',
            formBind: true,
            disabled: true,
            handler: function() {
                //cannot catch the event in controller
                //when Articles overview is open
                //todo@sb: find better solution
                var form = this.up('form').getForm();
                if (form.isValid()) {
                    var grid = Ext.getCmp('bepado-shipping-groups-list');
                    form.submit({
                        success: function(form, action) {
                            Shopware.Notification.createGrowlMessage('{s name=success}Success{/s}','{s name=config/shipping_groups/created_rule}Rule has been created.{/s}');
                            me.close();
                            grid.getStore().load();
                        },
                        failure: function(form, action) {
                            Shopware.Notification.createGrowlMessage('{s name=error}Error{/s}','{s name=config/shipping_groups/duplicated_group}Rule could not be created.{/s}');
                        }
                    });
                }
            }
        };
    }
});
//{/block}