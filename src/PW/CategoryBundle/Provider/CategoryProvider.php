<?php

namespace PW\CategoryBundle\Provider;

use PW\CategoryBundle\Document\Category,
    Symfony\Component\DependencyInjection\ContainerAware;

/**
 * CategoryProvider
 */
class CategoryProvider extends ContainerAware
{
    /**
     * @param string $type category type to return - item or user
     * @return cursor
     */
    public function getCategories($type = null)
    {
        $dm         = $this->container->get('doctrine_mongodb.odm.document_manager');
        $categories = $dm->getRepository('PWCategoryBundle:Category')
            ->findByType($type)
            ->getQuery()
            ->execute();

        return $categories;
    }
}
