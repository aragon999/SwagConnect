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
 * Shopware SwagConnect Plugin
 *
 * @category Shopware
 * @package Shopware\Plugins\SwagConnect
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
//{namespace name=backend/connect/view/main}
//{block name="backend/connect/view/config/import/form"}
Ext.define('Shopware.apps.Connect.view.config.import.Form', {
    extend: 'Ext.form.Panel',
    alias: 'widget.connect-config-import-form',

    border: false,
    layout: 'anchor',
    autoScroll: true,
    region: 'center',
    bodyPadding: 10,

    /**
     * Contains the field set defaults.
     */
    defaults: {
        labelWidth: 170,
        anchor: '100%'
    },

    snippets: {
        save: '{s name=config/save}Save{/s}',
        cancel: '{s name=config/cancel}Cancel{/s}',
        importPicturesLabel: '{s name=config/import/pictures_label}Load product images during first import{/s}',
        importPicturesHelp: '{s name=config/import/pictures_help}Der Import von Bildern kann den Import verlangsamen. Wenn Sie viele Produkte importieren möchten, sollten Sie diese Option nicht aktivieren und die Bilder über den CronJob oder »Geänderte Produkte« importieren.{/s}',
        overwritePropertiesLabel: '{s name=config/import/overwrite_properties}Folgende Felder beim Produkt-Aktualisierung überschreiben{/s}',
        overwritePropertiesHelp: '{s name=config/import/overwrite_properties_help}Die hier ausgewählten Felder werden automatisch überschrieben, wenn der Quellshop diese ändert. Sie können auf Artikel-Ebene Ausnahmen definieren.{/s}',
        overwriteProductName: '{s name=config/import/overwrite_product_name}Product name{/s}',
        overwriteProductPrice: '{s name=config/import/overwrite_product_price}Price{/s}',
        overwriteProductImages: '{s name=config/import/overwrite_product_images}Image{/s}',
        overwriteProductShortDescription: '{s name=config/import/overwrite_product_short_description}Short description{/s}',
        overwriteProductLongDescription: '{s name=config/import/overwrite_product_long_description}Long description{/s}',
        articleImagesLimitImportLabel: '{s name=config/import/pictures_limit_label}Number of products per image import pass{/s}',
        productImportSettingsTitle: '{s name=config/import/product_import_settings_title}Produktbeschreibungen{/s}',
        productImportImageSettingsTitle: '{s name=config/import/image_settings_title}Produktbilder{/s}',
        defaultCategoryHelp: '{s name=config/import/default_import_category_help}Hier geben Sie an, in welche Shop Kategorie Ihre Produkte importiert werden, wenn kein „Kategorie-Mapping“ vorgenommen wurde.{/s}',
        defaultCategory: '{s name=config/import/default_import_category}Default import category{/s}'
    },

    initComponent: function() {
        var me = this;

        me.items = me.createElements();
        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar',
            items: me.getFormButtons()
        }];

        me.importConfigStore = Ext.create('Shopware.apps.Connect.store.config.Import').load({
            callback: function() {
                me.populateForm();
            }
        });

        me.callParent(arguments);
    },

    /**
     * Creates form elements
     * @return Array
     */
    createElements: function() {

        var me = this,
            configFieldset = me.getConfigFieldset(),
            elements = [];

        elements.push(configFieldset);

        return elements;
    },

    /**
     * Returns form buttons, save and cancel
     * @returns Array
     */
    getFormButtons: function() {
        var me = this,
            buttons = ['->'];

        var saveButton = Ext.create('Ext.button.Button', {
            text: me.snippets.save,
            action:'save-import-config',
            cls:'primary'
        });

        var cancelButton = Ext.create('Ext.button.Button', {
            text: me.snippets.cancel,
            handler: function(btn) {
                btn.up('window').close();
            }
        });

        buttons.push(cancelButton);
        buttons.push(saveButton);

        return buttons;
    },

    /**
     * Creates the field set
     * @return Ext.form.FieldSet
     */
    getConfigFieldset: function() {
        var me = this,
            items = [],
            elements = me.createElements();

        items.push(elements);

        var fieldset = Ext.create('Ext.form.FieldSet', {
            layout: 'column',
            border: false,
            items: items
        });

        return fieldset;
    },

    /**
     * Creates the field set items
     * @return Array
     */
    createElements: function () {
        var me = this;

        var categoriesStore = Ext.create('Shopware.apps.Base.store.CategoryTree');
        categoriesStore.load();

        me.imageLimitImportField = Ext.create('Ext.form.field.Number', {
            name: 'articleImagesLimitImport',
            fieldLabel: me.snippets.articleImagesLimitImportLabel,
            labelWidth: me.defaults.labelWidth
        });

        var container = Ext.create('Ext.container.Container', {
            padding: '0 20 0 0',
            flex: 1,
            layout: 'vbox',
            border: false,
            items: [
                {
                    xtype: 'container',
                    html: '<h1 class="shopware-connect-color" style="font-size: large">' + me.snippets.productImportSettingsTitle  + '</h1>',
                    width: 400,
                    height: 30
                },
                {
                    xtype      : 'fieldcontainer',
                    fieldLabel : me.snippets.overwritePropertiesLabel,
                    defaultType: 'checkboxfield',
                    labelWidth: me.defaults.labelWidth,
                    items: [
                        {
                            boxLabel  : me.snippets.overwriteProductName,
                            name      : 'overwriteProductName',
                            inputValue: 1,
                            helpText: me.snippets.overwritePropertiesHelp,
                            uncheckedValue: 0
                        }, {
                            boxLabel  : me.snippets.overwriteProductPrice,
                            name      : 'overwriteProductPrice',
                            inputValue: 1,
                            uncheckedValue: 0
                        }, {
                            boxLabel  : me.snippets.overwriteProductImages,
                            name      : 'overwriteProductImage',
                            inputValue: 1,
                            uncheckedValue: 0
                        }, {
                            boxLabel  : me.snippets.overwriteProductShortDescription,
                            name      : 'overwriteProductShortDescription',
                            inputValue: 1,
                            uncheckedValue: 0
                        }, {
                            boxLabel  : me.snippets.overwriteProductLongDescription,
                            name      : 'overwriteProductLongDescription',
                            inputValue: 1,
                            uncheckedValue: 0
                        }
                    ]
                },
                {
                    xtype: 'container',
                    html: '<h1 class="shopware-connect-color" style="font-size: large">' + me.snippets.productImportImageSettingsTitle  + '</h1>',
                    width: 400,
                    height: 30
                },
                {
                    xtype      : 'fieldcontainer',
                    defaultType: 'checkboxfield',
                    margin: '0 0 0 50',
                    labelWidth: me.defaults.labelWidth,
                    items: [
                        {
                            xtype: 'checkbox',
                            name: 'importImagesOnFirstImport',
                            fieldLabel: me.snippets.importPicturesLabel,
                            helpText: me.snippets.importPicturesHelp,
                            inputValue: 1,
                            uncheckedValue: 0,
                            labelWidth: me.defaults.labelWidth,
                            listeners:{
                                change: function(checkbox, newValue, oldValue, opts){
                                    me.enableImageImportLimit(checkbox);
                                },
                                beforeRender: function(checkbox, opts) {
                                    me.enableImageImportLimit(checkbox);
                                }
                            }
                        }, me.imageLimitImportField
                    ]
                }

                ]
        });

        return [ container ];
    },

    /**
     * Populate import config form
     */
    populateForm: function() {
        var me = this,
            record = me.importConfigStore.getAt(0);

        if (!record) {
            record = Ext.create('Shopware.apps.Connect.model.config.Import');
        }
        me.loadRecord(record);
    },

    /**
     * Enable / disable number of products which are proceed
     * by images cron at the same time.
     * It depends on import images on first import of products
     *
     * @param checkbox
     */
    enableImageImportLimit: function(checkbox) {
        var me = this;

        me.imageLimitImportField.setDisabled(checkbox.getValue());
    }
});
//{/block}
