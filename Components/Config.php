<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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

namespace Shopware\Bepado\Components;

use Shopware\Components\Model\ModelManager;
use Symfony\Component\Debug\Debug;

/**
 * @category  Shopware
 * @package   Shopware\Plugins\SwagBepado
 */
class Config
{
    /**
     * @var ModelManager
     */
    private $manager;

    /**
     * @var \Shopware\CustomModels\Bepado\ConfigRepository
     */
    private $repository;

    /** @var  \Shopware\Models\Shop\Shop */
    private $shopRepository;

    /**
     * @param ModelManager $manager
     */
    public function __construct(ModelManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param $name
     * @param null $default
     * @param null $shopId
     * @return null
     */
    public function getConfig($name, $default=null, $shopId=null)
    {
        if (is_null($shopId)) {
            return $this->getMainConfig($name, $default);
        }

        $query = $this->getConfigRepository()->getConfigsQuery($name, $shopId);
        $model = $query->getOneOrNullResult();

        if ($model) {
            return $model->getValue();
        }

        $shop = $this->getShopRepository()->find($shopId);
        if (!$shop) {
            return $this->getMainConfig($name, $default);
        }

        $mainShop = $shop->getMain();
        if ($mainShop) {
            $mainShopId = $mainShop->getId();
            $query = $this->getConfigRepository()->getConfigsQuery($name, $mainShopId);
            $model = $query->getOneOrNullResult();

            if ($model) {
                return $model->getValue();
            }
        }

        return $this->getMainConfig($name, $default);
    }

    /**
     * @param $name
     * @param null $default
     * @return null
     */
    private function getMainConfig($name, $default=null)
    {
        $query = $this->getConfigRepository()->getConfigsQuery($name);

        $model = $query->getOneOrNullResult();
        if ($model) {
            return $model->getValue();
        }

        return $default;
    }

    /**
     * @param null $name
     * @param null $shopId
     * @param null $groupName
     * @return array
     */
    public function getConfigs($name = null, $shopId = null, $groupName = null)
    {
        $query = $this->getConfigRepository()->getConfigsQuery($name, $shopId, $groupName);

        return $query->getResult();
    }

    /**
     * @param $name
     * @param $value
     * @param null $shopId
     * @param null $groupName
     */
    public function setConfig($name, $value, $shopId = null, $groupName = null)
    {
        $model = $this->getConfigRepository()->findOneBy(array('name' => $name));

        if (!$model) {
            $model = new \Shopware\CustomModels\Bepado\Config();
            $this->manager->persist($model);
        }

        $model->setName($name);
        $model->setValue($value);
        $model->setShopId($shopId);
        $model->setGroupName($groupName);

        $this->manager->flush();
    }

    /**
     * @return \Shopware\Components\Model\ModelRepository|\Shopware\CustomModels\Bepado\ConfigRepository
     */
    private function getConfigRepository()
    {
        if (!$this->repository) {
            $this->repository = $this->manager->getRepository('Shopware\CustomModels\Bepado\Config');
        }

        return $this->repository;
    }

    /**
     * @return \Shopware\Components\Model\ModelRepository|\Shopware\Models\Shop\Shop
     */
    private function getShopRepository()
    {
        if (!$this->shopRepository) {
            $this->shopRepository = $this->manager->getRepository('Shopware\Models\Shop\Shop');
        }

        return $this->shopRepository;
    }
} 