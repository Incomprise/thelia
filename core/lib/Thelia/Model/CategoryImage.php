<?php

namespace Thelia\Model;

use Propel\Runtime\ActiveQuery\ModelCriteria;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Files\FileModelParentInterface;
use Thelia\Form\BaseForm;
use Thelia\Form\CategoryImageModification;
use Thelia\Model\Base\CategoryImage as BaseCategoryImage;
use Propel\Runtime\Connection\ConnectionInterface;
use Thelia\Model\Base\CategoryImageQuery;
use Thelia\Model\Breadcrumb\BreadcrumbInterface;
use Thelia\Model\Breadcrumb\CatalogBreadcrumbTrait;
use Thelia\Files\FileModelInterface;
use Thelia\Model\Tools\ModelEventDispatcherTrait;
use Thelia\Model\Tools\PositionManagementTrait;

class CategoryImage extends BaseCategoryImage implements BreadcrumbInterface, FileModelInterface
{
    use ModelEventDispatcherTrait;
    use PositionManagementTrait;
    use CatalogBreadcrumbTrait;

    /**
     * Calculate next position relative to our parent
     *
     * @param CategoryImageQuery $query
     */
    protected function addCriteriaToPositionQuery($query)
    {
        $query->filterByCategory($this->getCategory());
    }

    /**
     * {@inheritDoc}
     */
    public function preInsert(ConnectionInterface $con = null)
    {
        $this->setPosition($this->getNextPosition());

        return true;
    }

    /**
     * @inheritdoc
     */
    public function setParentId($parentId)
    {
        $this->setCategoryId($parentId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getParentId()
    {
        return $this->getCategoryId();
    }

    public function preDelete(ConnectionInterface $con = null)
    {
        $this->reorderBeforeDelete(
            array(
                "category_id" => $this->getCategoryId(),
            )
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getBreadcrumb(Router $router, ContainerInterface $container, $tab, $locale)
    {
        return $this->getCategoryBreadcrumb($router, $container, $tab, $locale);
    }

    /**
     * @return FileModelParentInterface the parent file model
     */
    public function getParentFileModel()
    {
        return new Category();
    }

    /**
     * Get the ID of the form used to change this object information
     *
     * @return BaseForm the form
     */
    public function getUpdateFormId()
    {
        return 'thelia.admin.category.image.modification';
    }

    /**
     * Get the form instance used to change this object information
     *
     * @param \Thelia\Core\HttpFoundation\Request $request
     *
     * @return BaseForm the form
     */
    public function getUpdateFormInstance(Request $request)
    {
        return new CategoryImageModification($request);
    }

    /**
     * @return string the path to the upload directory where files are stored, without final slash
     */
    public function getUploadDir()
    {
        return THELIA_LOCAL_DIR . 'media'.DS.'images'.DS.'category';
    }

    /**
     * @param int $objectId the ID of the object
     *
     * @return string the URL to redirect to after update from the back-office
     */
    public function getRedirectionUrl($objectId)
    {
        return '/admin/categories/update?category_id=' . $objectId . '?current_tab=image';
    }

    /**
     * Get the Query instance for this object
     *
     * @return ModelCriteria
     */
    public function getQueryInstance()
    {
        return CategoryImageQuery::create();
    }
}
