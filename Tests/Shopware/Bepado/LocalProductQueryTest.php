<?php

namespace Tests\Shopware\Bepado;

use Bepado\SDK\Struct\Product;
use Bepado\SDK\Struct\Translation;
use Shopware\Bepado\Components\Config;
use Shopware\Bepado\Components\Gateway\ProductTranslationsGateway\PdoProductTranslationsGateway;
use Shopware\Bepado\Components\Marketplace\MarketplaceGateway;
use Shopware\Bepado\Components\ProductQuery;
use Shopware\Bepado\Components\ProductQuery\LocalProductQuery;
use Shopware\Bepado\Components\Translations\ProductTranslator;

class LocalProductQueryTest extends BepadoTestHelper
{
    protected $localProductQuery;

    protected $productTranslator;

    public function setUp()
    {
        $this->productTranslator = $this->getMockBuilder('\\Shopware\\Bepado\\Components\\Translations\\ProductTranslator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->productTranslator->expects($this->any())
            ->method('translate')
            ->willReturn(array(
                'en' => new Translation(
                    array(
                        'title' => 'Glas -Teetasse 0,25l EN',
                        'shortDescription' => 'Bepado local product short description EN',
                        'longDescription' => 'Bepado local product long description EN',
                        'url' => $this->getProductBaseUrl() . '22&shId=2'
                    )
                ),
                'nl' => new Translation(
                    array(
                        'title' => 'Glas -Teetasse 0,25l NL',
                        'shortDescription' => 'Bepado local product short description NL',
                        'longDescription' => 'Bepado local product long description NL',
                        'url' => $this->getProductBaseUrl() . '22&shId=176'
                    )
                ),
            ));
    }

    public function getLocalProductQuery()
    {
        if (!$this->localProductQuery) {
            /** @var \Shopware\Bepado\Components\Config $configComponent */
            $configComponent = new Config(Shopware()->Models());

            $this->localProductQuery = new LocalProductQuery(
                Shopware()->Models(),
                $configComponent->getConfig('alternateDescriptionField'),
                $this->getProductBaseUrl(),
                $configComponent,
                new MarketplaceGateway(Shopware()->Models()),
                $this->productTranslator
            );
        }
        return $this->localProductQuery;
    }

    public function getProductBaseUrl()
    {
        if (!Shopware()->Front()->Router()) {
            return null;
        }

        return Shopware()->Front()->Router()->assemble(array(
            'module' => 'frontend',
            'controller' => 'bepado_product_gateway',
            'action' => 'product',
            'id' => '',
            'fullPath' => true
        ));
    }

    public function testGetUrlForProduct()
    {
        $expectedUrl = $this->getProductBaseUrl() . '1091';
        $this->assertEquals($expectedUrl, $this->getLocalProductQuery()->getUrlForProduct(1091));
    }

    public function testGetUrlForProductWithShopId()
    {
        $expectedUrl = $this->getProductBaseUrl() . '1091&shId=3';
        $this->assertEquals($expectedUrl, $this->getLocalProductQuery()->getUrlForProduct(1091, 3));
    }

    public function testGetBepadoProduct()
    {
        $row = array (
            'sourceId' => '22',
            'ean' => NULL,
            'title' => 'Glas -Teetasse 0,25l',
            'shortDescription' => 'Almus Emitto Bos sicut hae Amplitudo rixa ortus retribuo Vicarius an nam capitagium medius.',
            'vendor' => 'Teapavilion',
            'vat' => '0.190000',
            'availability' => 3445,
            'price' => 10.924369747899,
            'purchasePrice' => 0,
            'longDescription' => '<p>Reficio congratulor simplex Ile familia mire hae Prosequor in pro St quae Muto,, St Texo aer Cornu ferox lex inconsiderate propitius, animus ops nos haero vietus Subdo qui Gemo ipse somniculosus. Non Apertio ops, per Repere torpeo penintentiarius Synagoga res mala caelestis praestigiator. Ineo via consectatio Gemitus sui domus ludio is vulgariter, hic ut legens nox Falx nos cui vaco insudo tero, tollo valde emo. deprecativus fio redigo probabiliter pacificus sem Nequequam, suppliciter dis Te summisse Consuesco cur Desolo sis insolesco expeditus pes Curo aut Crocotula Trimodus. Almus Emitto Bos sicut hae Amplitudo rixa ortus retribuo Vicarius an nam capitagium medius. Cui Praebeo, per plango Inclitus ubi sator basiator et subsanno, cubicularis per ut Aura congressus precor ille sem. aro quid ius Praedatio vitupero Tractare nos premo procurator. Ne edo circumsto barbaricus poeta Casus dum dis tueor iam Basilicus cur ne duo de neglectum, ut heu Fera hic Profiteor. Ius Perpetuus stilla co.</p>',
            'fixedPrice' => NULL,
            'deliveryWorkDays' => '',
            'shipping' => NULL,
        );

        $expectedProduct = new Product($row);
        $expectedProduct->url = $this->getProductBaseUrl() . '22';
        $expectedProduct->attributes = array(
            'quantity' => NULL,
            'ref_quantity' => NULL,
        );
        $expectedProduct->translations = array(
            'en' => new Translation(
                array(
                    'title' => 'Glas -Teetasse 0,25l EN',
                    'shortDescription' => 'Bepado local product short description EN',
                    'longDescription' => 'Bepado local product long description EN',
                    'url' => $this->getProductBaseUrl() . '22&shId=2'
                )
            ),
            'nl' => new Translation(
                array(
                    'title' => 'Glas -Teetasse 0,25l NL',
                    'shortDescription' => 'Bepado local product short description NL',
                    'longDescription' => 'Bepado local product long description NL',
                    'url' => $this->getProductBaseUrl() . '22&shId=176'
                )
            ),
        );

        $this->assertEquals($expectedProduct, $this->getLocalProductQuery()->getBepadoProduct($row));
    }
}