<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ShopwarePlugins\Connect\Bootstrap;

use Shopware\CustomModels\Connect\Attribute;
use Shopware\Components\Model\ModelManager;
use Enlight_Components_Db_Adapter_Pdo_Mysql as Pdo;
use Shopware\Models\Attribute\Configuration;
use Shopware\Models\Order\Status;
use ShopwarePlugins\Connect\Components\ProductQuery\BaseProductQuery;
use ShopwarePlugins\Connect\Components\Utils\ConnectOrderUtil;

/**
 * Updates existing versions of the plugin
 *
 * Class Update
 * @package ShopwarePlugins\Connect\Bootstrap
 */
class Update
{
    /**
     * @var \Shopware_Plugins_Backend_SwagConnect_Bootstrap
     */
    protected $bootstrap;

    /**
     * @var Pdo
     */
    protected $db;

    /**
     * @var ModelManager
     */
    protected $modelManager;

    /**
     * @var string
     */
    protected $version;

    /**
     * Setup constructor.
     * @param \Shopware_Plugins_Backend_SwagConnect_Bootstrap $bootstrap
     * @param ModelManager $modelManager
     * @param Pdo $db
     * @param $version
     */
    public function __construct(
        \Shopware_Plugins_Backend_SwagConnect_Bootstrap $bootstrap,
        ModelManager $modelManager,
        Pdo $db,
        $version
    ) {
        $this->bootstrap = $bootstrap;
        $this->modelManager = $modelManager;
        $this->db = $db;
        $this->version = $version;
    }

    public function run()
    {
        // Force an SDK re-verify
        $this->reVerifySDK();

        $this->createExportedFlag();
        $this->removeRedirectMenu();
        $this->updateConnectAttribute();
        $this->addConnectDescriptionElement();
        $this->updateProductDescriptionSetting();
        $this->createUpdateAdditionalDescriptionColumn();
        $this->createDynamicStreamTable();
        $this->addOrderStatus();
        $this->fixExportDescriptionSettings();
        $this->fixMarketplaceUrl();
        $this->addIndexToChangeTable();
        $this->removeDuplicatedMenuItems();

        return true;
    }

    /**
     * Forces the SDK to re-verify the API key
     */
    public function reVerifySDK()
    {
        $this->db->query('
            UPDATE sw_connect_shop_config
            SET s_config = ?
            WHERE s_shop = "_last_update_"
            LIMIT 1; ',
            [time() - 8 * 60 * 60 * 24]
        );
    }

    private function createExportedFlag()
    {
        if (version_compare($this->version, '1.0.1', '<=')) {
            $this->db->query('
                ALTER TABLE `s_plugin_connect_items`
                ADD COLUMN `exported` TINYINT(1) DEFAULT 0
            ');

            $this->db->query('
                UPDATE `s_plugin_connect_items`
                SET `exported` = 1
                WHERE (`export_status` = ? OR `export_status` = ? OR `export_status` = ?) AND `shop_id` IS NULL',
                [Attribute::STATUS_INSERT, Attribute::STATUS_UPDATE, Attribute::STATUS_SYNCED]
            );
        }
    }

    private function removeRedirectMenu()
    {
        if (version_compare($this->version, '1.0.4', '<=')) {
            $connectItem = $this->bootstrap->Menu()->findOneBy(['label' => 'Open Connect', 'action' => '']);
            if ($connectItem) {
                $this->modelManager->remove($connectItem);
                $this->modelManager->flush();
            }
        }
    }

    private function updateConnectAttribute()
    {
        if (version_compare($this->version, '1.0.6', '<=')) {
            $result = $this->db->query("SELECT value FROM s_plugin_connect_config WHERE name = 'connectAttribute'");
            $row = $result->fetch();
            $attr = 19;
            if ($row) {
                $attr = $row['value'];
            }

            $this->db->query('
                    UPDATE `s_articles_attributes` 
                    SET `connect_reference` = `attr' . $attr . '` 
                    WHERE connect_reference IS NULL;
                ');

            $this->db->query("DELETE FROM s_plugin_connect_config WHERE name = 'connectAttribute'");
        }
    }

    private function addConnectDescriptionElement()
    {
        if (version_compare($this->version, '1.0.9', '<=')) {
            $tableName = $this->modelManager->getClassMetadata('Shopware\Models\Attribute\Article')->getTableName();
            $columnName = 'connect_product_description';

            $repo = $this->modelManager->getRepository('Shopware\Models\Attribute\Configuration');
            $element = $repo->findOneBy([
                'tableName' => $tableName,
                'columnName' => $columnName,
            ]);

            if (!$element) {
                $element = new Configuration();
                $element->setTableName($tableName);
                $element->setColumnName($columnName);
            }

            $element->setColumnType('html');
            $element->setTranslatable(true);
            $element->setLabel('Connect Beschreibung');
            $element->setDisplayInBackend(true);

            $this->modelManager->persist($element);
            $this->modelManager->flush();
        }
    }

    private function updateProductDescriptionSetting()
    {
        if (version_compare($this->version, '1.0.9', '<=')) {
            //migrates to the new export settings
            $result = $this->db->query("SELECT `value` FROM s_plugin_connect_config WHERE name = 'alternateDescriptionField'");
            $row = $result->fetch();

            if ($row) {
                $mapper = [
                    'a.description' => BaseProductQuery::SHORT_DESCRIPTION_FIELD,
                    'a.descriptionLong' => BaseProductQuery::LONG_DESCRIPTION_FIELD,
                    'attribute.connectProductDescription' => BaseProductQuery::CONNECT_DESCRIPTION_FIELD,
                ];

                if ($name = $mapper[$row['value']]) {
                    $result = $this->db->query("SELECT `id` FROM s_plugin_connect_config WHERE name = '$name'");
                    $row = $result->fetch();

                    $id = null;
                    if (isset($row['id'])) {
                        $id = $row['id'];
                    }

                    $this->db->query(
                        "REPLACE INTO `s_plugin_connect_config`
                        (`id`, `name`, `value`, `shopId`, `groupName`)
                        VALUES
                        (?, ?, 1, null, 'export')",
                        [$id, $name]
                    );
                }
            }

            $this->db->query("
                ALTER TABLE `s_plugin_connect_items`
                ADD `update_additional_description` VARCHAR(255) NULL DEFAULT 'inherit' AFTER `update_short_description`;
            ");
        }
    }

    private function createUpdateAdditionalDescriptionColumn()
    {
        // for some reason update_additional_description column is missing in 1.0.11
        if (version_compare($this->version, '1.0.11', '<=')) {
            try {
                $this->db->query("
                        ALTER TABLE `s_plugin_connect_items`
                        ADD `update_additional_description` VARCHAR(255) NULL DEFAULT 'inherit' AFTER `update_short_description`;
                    ");
            } catch (\Exception $e) {
                // ignore it if the column already exists
            }
        }
    }

    private function createDynamicStreamTable()
    {
        if (version_compare($this->version, '1.0.12', '<=')) {
            $query = "CREATE TABLE IF NOT EXISTS `s_plugin_connect_streams_relation` (
                `stream_id` int(11) unsigned NOT NULL,
                `article_id` int(11) unsigned NOT NULL,
                `deleted` int(1) NOT NULL DEFAULT '0',
                UNIQUE KEY `stream_id` (`stream_id`,`article_id`),
                CONSTRAINT s_plugin_connect_streams_selection_fk_stream_id FOREIGN KEY (stream_id) REFERENCES s_product_streams (id) ON DELETE CASCADE,
                CONSTRAINT s_plugin_connect_streams_selection_fk_article_id FOREIGN KEY (article_id) REFERENCES s_articles (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

            $this->db->exec($query);
        }
    }

    private function addOrderStatus()
    {
        if (version_compare($this->version, '1.0.12', '<=')) {
            $query = $this->modelManager->getRepository('Shopware\Models\Order\Status')->createQueryBuilder('s');
            $query->select('MAX(s.id)');
            $result = $query->getQuery()->getOneOrNullResult();

            if (count($result) > 0) {
                $currentId = (int) reset($result);
            } else {
                $currentId = 0;
            }

            $name = ConnectOrderUtil::ORDER_STATUS_ERROR;
            $group = Status::GROUP_STATE;

            $isExists = $this->db->query('
                SELECT `id` FROM `s_core_states`
                WHERE `name` = ? AND `group` = ?
                ', [$name, $group]
            )->fetch();

            if ($isExists) {
                return;
            }

            ++$currentId;
            $this->db->query('
                INSERT INTO `s_core_states`
                (`id`, `name`, `description`, `position`, `group`, `mail`)
                VALUES (?, ?, ?, ?, ?, ?)
                ', [$currentId, $name, 'SC error', $currentId, $group, 0]
            );
        }
    }

    /**
     * Replace longDescriptionField and shortDescription values,
     * because of wrong snippets in previous versions.
     *
     * ExtJs view show longDescription label, but the value was stored as shortDescription
     */
    private function fixExportDescriptionSettings()
    {
        if (version_compare($this->version, '1.0.12', '<=')) {
            $rows = $this->db->fetchPairs(
                'SELECT `name`, `value` FROM s_plugin_connect_config WHERE name = ? OR name = ?',
                ['longDescriptionField', 'shortDescriptionField']
            );

            if (!array_key_exists('longDescriptionField', $rows) || !array_key_exists('shortDescriptionField', $rows)) {
                return;
            }

            if (($rows['longDescriptionField'] == 1 && $rows['shortDescriptionField'] == 1)
                || ($rows['longDescriptionField'] == 0 && $rows['shortDescriptionField'] == 0)) {
                return;
            }

            $newValues = [
                'longDescriptionField' => $rows['shortDescriptionField'],
                'shortDescriptionField' => $rows['longDescriptionField'],
            ];

            $this->db->query('
                UPDATE `s_plugin_connect_config`
                SET `value` = ?
                WHERE `name` = ?',
                [$newValues['longDescriptionField'], 'longDescriptionField']
            );

            $this->db->query('
                UPDATE `s_plugin_connect_config`
                SET `value` = ?
                WHERE `name` = ?',
                [$newValues['shortDescriptionField'], 'shortDescriptionField']
            );
        }
    }

    private function fixMarketplaceUrl()
    {
        if (version_compare($this->version, '1.0.12', '<=')) {
            $repo = $this->modelManager->getRepository('Shopware\Models\Config\Form');
            /** @var \Shopware\Models\Config\Form $form */
            $form = $repo->findOneBy([
                'name' => 'SwagConnect',
            ]);

            if (!$form) {
                return;
            }

            /** @var \Shopware\Models\Config\Element $element */
            foreach ($form->getElements() as $element) {
                if ($element->getName() != 'connectDebugHost') {
                    continue;
                }

                if (strlen($element->getValue()) > 0 && strpos($element->getValue(), 'sn.') === false) {
                    $element->setValue('sn.' . $element->getValue());
                    $this->modelManager->persist($element);
                }

                $values = $element->getValues();
                if (count($values) > 0) {
                    /** @var \Shopware\Models\Config\Value $element */
                    $value = $values[0];
                    if (strlen($value->getValue()) > 0 && strpos($value->getValue(), 'sn.') === false) {
                        $value->setValue('sn.' . $value->getValue());
                        $this->modelManager->persist($value);
                    }
                }

                $this->modelManager->flush();
            }
        }
    }

    private function addIndexToChangeTable()
    {
        if (version_compare($this->version, '1.0.16', '<=')) {
            $this->db->query('
              ALTER TABLE `sw_connect_change`
              ADD INDEX `c_operation` (`c_operation`)
             ');
        }
    }

    /**
     * In some cases Connect main menu was duplicated
     * when shop is connected to SEM project. All not needed menu items must be removed.
     */
    private function removeDuplicatedMenuItems()
    {
        if (version_compare($this->version, '1.0.16', '<=')) {
            $mainMenuItems = $this->bootstrap->Menu()->findBy([
                'class' => Menu::CONNECT_CLASS,
                'parent' => null,
            ], ['id' => 'ASC']);

            foreach (array_slice($mainMenuItems, 1) as $menuItem) {
                foreach ($menuItem->getChildren() as $children) {
                    $this->modelManager->remove($children);
                }

                $this->modelManager->remove($menuItem);
            }
            $this->modelManager->flush();
        }
    }
}
