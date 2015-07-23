<?php

namespace Tests\Shopware\Bepado;

use Bepado\SDK\Struct\Product;

class SDKTest extends BepadoTestHelper
{
    public function testHandleProductUpdates()
    {
        // pseudo verify SDK
        $conn = Shopware()->Db();
        $conn->delete('bepado_shop_config', array());
        $conn->insert('bepado_shop_config', array('s_shop' => '_self_', 's_config' => -1));
        $conn->insert('bepado_shop_config', array('s_shop' => '_last_update_', 's_config' => time()));
        $conn->insert('bepado_shop_config', array('s_shop' => '_categories_', 's_config' => serialize(array('/bücher' => 'Bücher'))));

        $offerValidUntil = time() + 1 * 365 * 24 * 60 * 60; // One year
        $purchasePrice = 6.99;
        $this->dispatchRpcCall('products', 'toShop', array(
            array(
                new \Bepado\SDK\Struct\Change\ToShop\InsertOrUpdate(array(
                    'product' => new \Bepado\SDK\Struct\Product(array(
                        'shopId' => 3,
                        'revisionId' => time(),
                        'sourceId' => 'ABCDEFGH' . time(),
                        'ean' => '1234',
                        'url' => 'http://shopware.de',
                        'title' => 'Bepado Test-Produkt',
                        'shortDescription' => 'Ein Produkt aus Bepado',
                        'longDescription' => 'Ein Produkt aus Bepado',
                        'vendor' => 'Bepado',
                        'price' => 9.99,
                        'purchasePrice' => $purchasePrice,
                        'purchasePriceHash' => hash_hmac(
                            'sha256',
                            sprintf('%.3F %d', $purchasePrice, $offerValidUntil), '54642546-0001-48ee-b4d0-4f54af66d822'
                        ),
                        'offerValidUntil' => $offerValidUntil,
                        'availability' => 100,
                        'images' => array('http://lorempixel.com/400/200'),
                        'categories' => array('/bücher'),
                    )),
                    'revision' => time(),
                ))
            )
        ));
    }

    public function testExportProductWithoutPurchasePrice()
    {
        $article = $this->getLocalArticle();
        $prices = $article->getMainDetail()->getPrices();
        $prices[0]->setBasePrice(null);
        Shopware()->Models()->persist($prices[0]);
        Shopware()->Models()->flush();

        $this->getBepadoExport()->export(array($article->getId()));


        /** @var \Shopware\CustomModels\Bepado\Attribute $model */
        $model = Shopware()->Models()->getRepository('Shopware\CustomModels\Bepado\Attribute')->findOneBy(array('sourceId' => $article->getId()));
        $message = $model->getExportMessage();

        $this->assertContains('purchasePrice', $message);

    }

    public function testExportProductWithPurchasePrice()
    {
        // Assign a category mapping
//        $this->changeCategoryBepadoMappingForCategoryTo(14, '/bücher');

        $article = $this->getLocalArticle();
        // Insert the product
        $this->getBepadoExport()->export(array($article->getId()));

        /** @var \Shopware\CustomModels\Bepado\Attribute $model */
        $model = Shopware()->Models()->getRepository('Shopware\CustomModels\Bepado\Attribute')->findOneBy(array('articleId' => 3));
        $message = $model->getExportMessage();

        $this->assertNull($message);
    }

}
