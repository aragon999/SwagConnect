<?php
/**
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\ShopwarePlugins\Connect;

use Shopware\Connect\Struct\Verificator\Product;
use ShopwarePlugins\Connect\Components\Config;
use ShopwarePlugins\Connect\Components\Marketplace\MarketplaceGateway;
use ShopwarePlugins\Connect\Components\ProductQuery;
use ShopwarePlugins\Connect\Components\ProductQuery\RemoteProductQuery;
use ShopwarePlugins\Connect\Components\ProductQuery\LocalProductQuery;

class ProductQueryTest extends ConnectTestHelper
{
    protected $productQuery;

    protected $productTranslator;

    protected $localMediaService;

    protected $contextService;

    public function setUp()
    {
        parent::setUp();

        if (method_exists('Shopware\Models\Article\Detail', 'setPurchasePrice')) {
            $purchasePriceField = 'detailPurchasePrice';
        } else {
            $purchasePriceField = 'basePrice';
        }

        Shopware()->Db()->executeQuery(
            "DELETE FROM s_plugin_connect_config WHERE `name` = 'priceFieldForPurchasePriceExport'"
        );
        Shopware()->Db()->executeQuery(
            'INSERT IGNORE INTO s_plugin_connect_config (`name`, `value`, `groupName`)
                  VALUES (?, ?, ?)
                  ON DUPLICATE KEY UPDATE
                  `value` = VALUES(`value`)
              ',
            [
                'priceFieldForPurchasePriceExport',
                $purchasePriceField,
                'export'
            ]);
    }

    public function getProductQuery()
    {
        if (!$this->productQuery) {
            $this->productTranslator = $this->getMockBuilder('\\ShopwarePlugins\\Connect\\Components\\Translations\\ProductTranslator')
                ->disableOriginalConstructor()
                ->getMock();

            $this->productTranslator->expects($this->any())
                ->method('translate')
                ->willReturn([]);

            $this->productTranslator->expects($this->any())
                ->method('translateConfiguratorGroup')
                ->willReturn([]);

            $this->productTranslator->expects($this->any())
                ->method('translateConfiguratorOption')
                ->willReturn([]);

            /** @var \ShopwarePlugins\Connect\Components\Config $configComponent */
            $configComponent = new Config(Shopware()->Models());

            $this->localMediaService = $this->getMockBuilder('\\ShopwarePlugins\\Connect\\Components\\MediaService\\LocalMediaService')
                ->disableOriginalConstructor()
                ->getMock();

            $this->localMediaService->expects($this->any())
                ->method('getProductMediaList')
                ->willReturn([]);

            $this->localMediaService->expects($this->any())
                ->method('getVariantMediaList')
                ->willReturn([]);

            $this->contextService = $this->getMockBuilder('\\Shopware\\Bundle\\StoreFrontBundle\\Service\\Core\\ContextService')
                ->disableOriginalConstructor()
                ->getMock();

            $productContext = $this->getMockBuilder('\\Shopware\\Bundle\\StoreFrontBundle\\Struct\\ProductContext')
                ->disableOriginalConstructor()
                ->getMock();
            $this->contextService->expects($this->any())
                ->method('createShopContext')
                ->willReturn($productContext);

            $this->productQuery = new ProductQuery(
                new LocalProductQuery(
                    Shopware()->Models(),
                    $this->getProductBaseUrl(),
                    $configComponent,
                    new MarketplaceGateway(Shopware()->Models()),
                    $this->productTranslator,
                    $this->contextService,
                    $this->localMediaService,
                    Shopware()->Container()->get('events')
                ),
                new RemoteProductQuery(
                    Shopware()->Models()
                )
            );
        }

        return $this->productQuery;
    }

    public function getShopProduct($id)
    {
        return Shopware()->Models()->getRepository('Shopware\Models\Article\Article')->find($id);
    }

    public function testGetLocal()
    {
        $article = $this->getShopProduct(3);
        /** @var \Shopware\Models\Article\Detail $detail */
        $detail = $article->getMainDetail();
        $detail->setMinPurchase(2);

        Shopware()->Models()->persist($detail);
        Shopware()->Models()->flush($detail);

        $result = $this->getProductQuery()->getLocal([3]);
        /** @var \Shopware\Connect\Struct\Product $product */
        $product = $result[0];

        $this->assertInstanceOf('\Shopware\Connect\Struct\Product', $product);
        $this->assertEquals('Münsterländer Aperitif 16%', $product->title);
        $this->assertEquals(12.56, round($product->price, 2));
        $this->assertEquals(2, $product->minPurchaseQuantity);
    }

    public function testGetRemoteShouldReturnEmptyArray()
    {
        $result = $this->getProductQuery()->getRemote([2], 2);
        $this->assertEmpty($result);
    }

    public function testGetRemote()
    {
        $newProduct = $this->getProduct();
        $newProduct->minPurchaseQuantity = 4;

        $this->dispatchRpcCall('products', 'toShop', [
            [
                new \Shopware\Connect\Struct\Change\ToShop\InsertOrUpdate([
                    'product' => $newProduct,
                    'revision' => time(),
                ])
            ]
        ]);

        $result = $this->getProductQuery()->getRemote([$newProduct->sourceId], $newProduct->shopId);
        /** @var \Shopware\Connect\Struct\Product $product */
        $product = $result[0];


        $this->assertInstanceOf('\Shopware\Connect\Struct\Product', $product);
        $this->assertEquals($newProduct->title, $product->title);
        $this->assertEquals($newProduct->price, $product->price);
        $this->assertEquals($newProduct->purchasePrice, $product->purchasePrice);
        $this->assertEquals($newProduct->purchasePriceHash, $product->purchasePriceHash);
        $this->assertEquals($newProduct->offerValidUntil, $product->offerValidUntil);
        $this->assertEquals($newProduct->minPurchaseQuantity, $product->minPurchaseQuantity);
    }

    public function getProductBaseUrl()
    {
        if (!Shopware()->Front()->Router()) {
            return null;
        }

        return Shopware()->Front()->Router()->assemble([
            'module' => 'frontend',
            'controller' => 'connect_product_gateway',
            'action' => 'product',
            'id' => '',
            'fullPath' => true
        ]);
    }

    public function testGetConnectProduct()
    {
        $result = $this->getProductQuery()->getLocal([3]);
        /** @var \Shopware\Connect\Struct\Product $product */
        $product = $result[0];

        $this->assertEquals('Münsterländer Aperitif 16%', $product->title);
        $this->assertEquals('l', $product->attributes['unit']);
        $this->assertEquals('0.7000', $product->attributes['quantity']);
        $this->assertEquals('1.000', $product->attributes['ref_quantity']);


        $model = Shopware()->Models()->getRepository('Shopware\Models\Article\Article')->find(11);
        $connectAttribute = $this->getHelper()->getOrCreateConnectAttributeByModel($model);
        $result = $this->getProductQuery()->getLocal([11]);
        /** @var \Shopware\Connect\Struct\Product $product */
        $product = $result[0];

        $this->assertEquals('Münsterländer Aperitif Präsent Box', $product->title);
        $this->assertArrayNotHasKey('unit', $product->attributes);
        $this->assertNull($product->attributes['quantity']);
        $this->assertNull($product->attributes['ref_quantity']);
    }
}
