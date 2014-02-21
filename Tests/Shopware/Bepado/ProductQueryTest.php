<?php

namespace Tests\Shopware\Bepado;

use Bepado\SDK\Struct\Verificator\Product;
use Shopware\Bepado\Components\Config;
use Shopware\Bepado\Components\ProductQuery;
use Shopware\Bepado\Components\ProductQuery\RemoteProductQuery;
use Shopware\Bepado\Components\ProductQuery\LocalProductQuery;

class ProductQueryTest extends BepadoTestHelper
{
    protected $productQuery;

    public function getProductQuery()
    {
        if (!$this->productQuery) {
            /** @var \Shopware\Bepado\Components\Config $configComponent */
            $configComponent = new Config(Shopware()->Models());

            $this->productQuery = new ProductQuery(
                new LocalProductQuery(Shopware()->Models(), $configComponent->getConfig('alternateDescriptionField')),
                new RemoteProductQuery(Shopware()->Models(), $configComponent->getConfig('alternateDescriptionField'))
            );
        }
        return $this->productQuery;
    }


    public function testGetLocal()
    {
        $result = $this->getProductQuery()->getLocal(array(2));
        /** @var \Bepado\SDK\Struct\Product $product */
        $product = $result[0];

        $this->assertInstanceOf('\Bepado\SDK\Struct\Product', $product);
        $this->assertEquals('Münsterländer Lagerkorn 32%', $product->title);
        $this->assertEquals(16.80, $product->price);
    }

    public function testGetRemoteShouldReturnEmptyArray()
    {
        $result = $this->getProductQuery()->getRemote(array(2));
        $this->assertEmpty($result);
    }

    public function testGetRemote()
    {
        $newProduct = $this->getProduct();
        $this->dispatchRpcCall('products', 'toShop', array(
            array(
                new \Bepado\SDK\Struct\Change\ToShop\InsertOrUpdate(array(
                    'product' => $newProduct,
                    'revision' => time(),
                ))
            )
        ));

        $sql = 'SELECT article_id FROM s_plugin_bepado_items WHERE source_id = ?';
        $id = Shopware()->Db()->fetchOne($sql, array($newProduct->sourceId));

        $this->assertNotEmpty($id);

        $result = $this->getProductQuery()->getRemote(array($id));
        /** @var \Bepado\SDK\Struct\Product $product */
        $product = $result[0];

        $this->assertInstanceOf('\Bepado\SDK\Struct\Product', $product);
        $this->assertEquals($newProduct->title, $product->title);
        $this->assertEquals($newProduct->price, $product->price);
        $this->assertEquals($newProduct->purchasePrice, $product->purchasePrice);

    }
}