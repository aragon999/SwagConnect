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
//{block name="backend/connect/store/config/local_product_attributes"}
Ext.define('Shopware.apps.Connect.store.config.LocalProductAttributes', {
    extend: 'Ext.data.Store',

    autoLoad: false,
    model: 'Shopware.apps.Connect.model.config.LocalProductAttributes',
    remoteSort: false,
    remoteFilter: false,
    proxy: {
        type: 'ajax',
        url: '{url controller="ConnectConfig" action="getProductAttributesMapping"}',
        api: {
            create: '{url controller="ConnectConfig" action="saveProductAttributesMapping"}',
            update: '{url controller="ConnectConfig" action="saveProductAttributesMapping"}',
            read: '{url controller="ConnectConfig" action="getProductAttributesMapping"}'
        },
        reader: {
            type: 'json',
            root: 'data'
        },
        writer: {
            type: 'json',
            root: 'data'
        }
    }
});
//{/block}




