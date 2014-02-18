<?php

namespace Shopware\Bepado\Components\ProductQuery;

use Bepado\SDK\Struct\Product;
use Shopware\Components\Model\ModelManager;

abstract class BaseProductQuery
{

    protected $attributeMapping = array(
        'weight' => Product::ATTRIBUTE_WEIGHT,
        'unit' => Product::ATTRIBUTE_UNIT,
        'referenceUnit' => 'ref_quantity',
        'purchaseUnit' => 'quantity'
    );


    protected $manager;

    protected $productDescriptionField;

    public function __construct(ModelManager $manager, $productDescriptionField)
    {
        $this->manager = $manager;
        $this->productDescriptionField = $productDescriptionField;

    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    abstract function getProductQuery();

    /**
     * @param $rows
     * @return array
     */
    abstract function getBepadoProducts($rows);

    public function get(array $ids)
    {
        $builder = $this->getProductQuery();
        $builder->where('a.id IN (:id)');
        $builder->setParameter('id', $ids);
        $query = $builder->getQuery();

        return $this->getBepadoProducts($query->getArrayResult());
    }

    /**
     * @param $id
     * @return string[]
     */
    protected function getImagesById($id)
    {
        $builder = $this->manager->createQueryBuilder();
        $builder->select(array('i.path', 'i.extension', 'i.main', 'i.position'))
            ->from('Shopware\Models\Article\Image', 'i')
            ->where('i.articleId = :articleId')
            ->andWhere('i.parentId IS NULL')
            ->setParameter('articleId', $id)
            ->orderBy('i.main', 'ASC')
            ->addOrderBy('i.position', 'ASC');

        $query = $builder->getQuery();
        $query->setHydrationMode($query::HYDRATE_OBJECT);

        $images = $query->getArrayResult();

        $imagePath = $this->getImagePath();
        $images = array_map(function($image) use ($imagePath) {
            return "{$imagePath}{$image['path']}.{$image['extension']}";
        }, $images);


        return $images;
    }

    /**
     * Returns URL for the shopware image directory
     *
     * @return string
     */
    protected function getImagePath()
    {
        $request = Shopware()->Front()->Request();

        if (!$request) {
            return '';
        }

        $imagePath = $request->getScheme() . '://'
            . $request->getHttpHost() . $request->getBasePath();
        $imagePath .= '/media/image/';

        return $imagePath;
    }

    /**
     * Prepares some common fields for local and remote products
     *
     * @param $row
     * @return mixed
     */
    public function prepareCommonAttributes($row)
    {
        if(isset($row['deliveryDate'])) {
            /** @var \DateTime $time */
            $time = $row['deliveryDate'];
            $row['deliveryDate'] = $time->getTimestamp();
        }

        // Fix categories
        if(is_string($row['categories'])) {
            $row['categories'] = unserialize($row['categories']);
        }

        // Fix prices
        foreach(array('price', 'purchasePrice', 'vat') as $name) {
            $row[$name] = round($row[$name], 2);
        }


        // Fix attributes
        $row['attributes'] = array();
        foreach ($this->attributeMapping as $swField => $bepadoField) {
            $row['attributes'][$bepadoField] = $row[$swField];
            unset ($row[$swField]);
        }

        // Fix dimensions
        $row = $this->prepareProductDimensions($row);


        return $row;
    }

    public function getRouter()
    {
        $front = Shopware()->Front();
        if (!$front->Router()) {
            return false;
        }
        return $front->Router();
    }

    /**
     * @param $row
     * @return mixed
     */
    public function prepareProductDimensions($row)
    {
        if (!empty($row['width']) && !empty($row['height']) && !empty($row['length'])) {
            $dimension = array(
                $row['length'], $row['width'], $row['height']
            );
            $row['attributes'][Product::ATTRIBUTE_DIMENSION] = implode('x', $dimension);
        }
        unset($row['width'], $row['height'], $row['length']);
        return $row;
    }


}
