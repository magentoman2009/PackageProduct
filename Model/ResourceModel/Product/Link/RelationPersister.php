<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Beckin\PackageProduct\Model\ResourceModel\Product\Link;

use Magento\Catalog\Model\ProductLink\LinkFactory;
use Magento\Catalog\Model\ResourceModel\Product\Link;
use Magento\Catalog\Model\ResourceModel\Product\Relation;

class RelationPersister
{
    /**
     * @var Relation
     */
    private $relationProcessor;

    /**
     * @var LinkFactory
     */
    private $linkFactory;

    /**
     * RelationPersister constructor.
     *
     * @param Relation $relationProcessor
     * @param LinkFactory $linkFactory
     */
    public function __construct(Relation $relationProcessor, LinkFactory $linkFactory)
    {
        $this->relationProcessor = $relationProcessor;
        $this->linkFactory = $linkFactory;
    }

    /**
     * Save Package products to product relation table
     *
     * @param Link $subject
     * @param \Closure $proceed
     * @param int $parentId
     * @param array $data
     * @param int $typeId
     * @return Link
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSaveProductLinks(Link $subject, \Closure $proceed, $parentId, $data, $typeId)
    {
        $result = $proceed($parentId, $data, $typeId);
        if ($typeId == \Beckin\PackageProduct\Model\ResourceModel\Product\Link::LINK_TYPE_PACKAGE) {
            foreach ($data as $linkData) {
                $this->relationProcessor->addRelation(
                    $parentId,
                    $linkData['product_id']
                );
            }
        }
        return $result;
    }

    /**
     * Remove Package products from product relation table
     *
     * @param Link $subject
     * @param \Closure $proceed
     * @param int $linkId
     * @return Link
     */
    public function aroundDeleteProductLink(Link $subject, \Closure $proceed, $linkId)
    {
        /** @var \Magento\Catalog\Model\ProductLink\Link $link */
        $link = $this->linkFactory->create();
        $subject->load($link, $linkId, $subject->getIdFieldName());
        $result = $proceed($linkId);
        if ($link->getLinkTypeId() == \Beckin\PackageProduct\Model\ResourceModel\Product\Link::LINK_TYPE_PACKAGE) {
            $this->relationProcessor->removeRelations(
                $link->getProductId(),
                $link->getLinkedProductId()
            );
        }
        return $result;
    }
}
