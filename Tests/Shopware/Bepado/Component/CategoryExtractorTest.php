<?php


class CategoryExtractorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Shopware\Bepado\Components\CategoryExtractor
     */
    private $categoryExtractor;

    public function setUp()
    {
        $attributeRepository = $this->getMockBuilder('\\Shopware\\CustomModels\\Bepado\\AttributeRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $attribute1 = new \Shopware\CustomModels\Bepado\Attribute();
        $attribute1->setCategory(array('/Ski' => 'Ski'));

        $attribute2 = new \Shopware\CustomModels\Bepado\Attribute();
        $attribute2->setCategory(array(
            '/Kleidung' => 'Kleidung',
            '/Kleidung/Hosen' => 'Hosen',
            '/Kleidung/Hosentraeger' => 'Hosentraeger',
        ));

        $attribute3 = new \Shopware\CustomModels\Bepado\Attribute();
        $attribute3->setCategory(array(
            '/Kleidung/Hosentraeger' => 'Hosentraeger',
            '/Kleidung/Nahrung & Getraenke' => 'Nahrung & Getraenke',
            '/Kleidung/Nahrung & Getraenke/Alkoholische Getränke' => 'Alkoholische Getränke',
        ));

        $attributeRepository->expects($this->once())
            ->method('findRemoteArticleAttributes')
            ->willReturn(array(
                $attribute1,
                $attribute2,
                $attribute3,
            ));

        $this->categoryExtractor = new \Shopware\Bepado\Components\CategoryExtractor(
            $attributeRepository,
            new \Shopware\Bepado\Components\CategoryResolver\AutoCategoryResolver(
                Shopware()->Models(),
                Shopware()->Models()->getRepository('Shopware\Models\Category\Category'),
                Shopware()->Models()->getRepository('Shopware\CustomModels\Bepado\RemoteCategory')
            )
        );
    }

    public function testExtractImportedCategories()
    {
        $expected = array(
            array(
                'name' => 'Ski',
                'id' => '/Ski',
                'leaf' => true,
                'children' => array(),
            ),
            array(
                'name' => 'Kleidung',
                'id' => '/Kleidung',
                'leaf' => false,
                'children' => array(
                    array(
                        'name' => 'Hosen',
                        'id' => '/Kleidung/Hosen',
                        'leaf' => true,
                        'children' => array(),
                    ),
                    array(
                        'name' => 'Hosentraeger',
                        'id' => '/Kleidung/Hosentraeger',
                        'leaf' => true,
                        'children' => array(),
                    ),
                    array(
                        'name' => 'Nahrung & Getraenke',
                        'id' => '/Kleidung/Nahrung & Getraenke',
                        'leaf' => false,
                        'children' => array(
                            array(
                                'name' => 'Alkoholische Getränke',
                                'id' => '/Kleidung/Nahrung & Getraenke/Alkoholische Getränke',
                                'leaf' => true,
                                'children' => array(),
                            ),
                        ),
                    )
                ),
            ),
        );

        $result = $this->categoryExtractor->extractImportedCategories();
        $this->assertTrue(is_array($result), 'Extracted categories must be array');
        $this->assertEquals($expected, $result);
    }
}
 