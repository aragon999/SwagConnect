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

namespace Shopware\Bepado;
use Bepado\SDK\ProductFromShop as ProductFromShopBase,
    Bepado\SDK\Struct\Order,
    Bepado\SDK\Struct\Product,
    Shopware\Models\Order as OrderModel,
    Shopware\Components\Model\ModelManager,
    Doctrine\ORM\Query;

/**
 * @category  Shopware
 * @package   Shopware\Plugins\SwagBepado
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 * @author    Heiner Lohaus
 */
class ProductFromShop implements ProductFromShopBase
{
    /**
     * @var ModelManager
     */
    private $manager;

    /**
     * @param ModelManager $manager
     */
    public function __construct(ModelManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param Product $product
     * @return string
     */
    private function getNumberByProduct(Product $product)
    {
        return 'BP-' . $product->shopId . '-' . $product->sourceId;
    }

    /**
     * Get product data
     *
     * Get product data for all the product IDs specified in the given string
     * array.
     *
     * @param string[] $ids
     * @return Product[]
     */
    public function getProducts(array $ids)
    {
        Shopware()->Log()->err('getProducts:' . print_r($ids, true));
        $repository = Shopware()->Models()->getRepository(
            'Shopware\Models\Article\Article'
        );
        $builder = $repository->createQueryBuilder('a');
        $builder->join('a.mainDetail', 'd');
        $builder->join('a.supplier', 's');
        $builder->join('d.prices', 'p', 'with', "p.from = 1 AND p.customerGroupKey = 'EK'");
        $builder->join('a.tax', 't');
        $builder->select(array(
            'a.id as sourceId',
            'd.ean',
            'a.name as title',
            'a.description as shortDescription',
            'a.descriptionLong as longDescription',
            's.name as vendor',
            't.tax / 100 as vat',
            'p.basePrice as price',
            'p.price * (100 + t.tax) / 100 as purchasePrice',
            //'"EUR" as currency',
            'd.shippingFree as freeDelivery',
            'd.releaseDate as deliveryDate',
            'd.inStock as availability',
            //'images = array()',
        ));
        $builder->where('a.id = :id');
        $query = $builder->getQuery();
        $products = array();
        foreach($ids as $id) {
            $product = $query->execute(array('id' => $id));
            $productData = $product[0];
            $productData['price'] = round($productData['price'], 2);
            $productData['vat'] = round($productData['vat'], 2);
            if(isset($productData['deliveryDate'])) {
                $productData['deliveryDate'] = $productData['deliveryDate']->getTimestamp();
            }
            if(empty($productData['price'])) {
                $productData['price'] = $productData['purchasePrice'];
            }
            $productData['categories'] = array(
                '/auto_motorrad'
            );
            $productData['attributes'] = array(
                //Product::ATTRIBUTE_WEIGHT => '',
                //Product::ATTRIBUTE_BASE_VOLUME => '',
                //Product::ATTRIBUTE_BASE_WEIGHT => '',
                //Product::ATTRIBUTE_DIMENSION => '',
                //Product::ATTRIBUTE_VOLUME => '',
            );
            $products[] = new Product($productData);
        }
        return $products;
    }

    /**
     * Get all IDs of all exported products
     *
     * @return string[]
     */
    public function getExportedProductIDs()
    {
        $repository = Shopware()->Models()->getRepository(
            'Shopware\Models\Article\Article'
        );
        $builder = $repository->createQueryBuilder('a');
        $builder->select(array(
            'a.id as sourceId'
        ));
        $query = $builder->getQuery();
        $ids = $query->getArrayResult();
        $ids = array_map(function($id) {
            return $id['sourceId'];
        }, $ids);
        return $ids;
    }

    /**
     * Reserve a product in shop for purchase
     *
     * @param Order $order
     * @return void
     * @throws \Exception Abort reservation by throwing an exception here.
     */
    public function reserve(Order $order)
    {

    }

    /**
     * Buy products mentioned in order
     *
     * Should return the internal order ID.
     *
     * @param Order $order
     * @return string
     *
     * @throws \Exception Abort buy by throwing an exception,
     *                    but only in very important cases.
     *                    Do validation in {@see reserve} instead.
     */
    public function buy(Order $order)
    {
        $model = new OrderModel\Order();
        $model->fromArray(array(
            'number' => 'BP-' . $order->reservationId,
            'invoiceShipping' => $order->shippingCosts,
            'invoiceShippingNet' => $order->shippingCosts
        ));
        $this->manager->persist($model);
        $this->manager->flush($model);
        $items = array();
        foreach($order->products as $product) {
            $item = new OrderModel\Detail();
            $item->fromArray(array(
                'articleId' => $product->product->sourceId,
                'quantity' => $product->count,
                'orderId' => $model->getId(),
                'number' => $model->getNumber(),
                'articleNumber' => $this->getNumberByProduct($product->product),
                'articleName' => $product->product->title,
                'price' => $product->product->purchasePrice,
                'taxRate' => $product->product->vat * 100
            ));
            $items[] = $item;
        }
        $model->setDetails($items);
        $this->manager->flush($model);
        return $model->getId();
    }
}