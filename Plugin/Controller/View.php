<?php
namespace Itbsllc\ThemeFix\Plugin\Controller;

use Magento\Framework\Exception\NoSuchEntityException;

class View
{
    /**
     * @param \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * @param \Magento\Catalog\Model\CategoryRepository
     */
    private $categoryRepository;

    /**
     * @param \Magento\Store\Model\StoreManager
     */
    private $storeManager;

    /**
     * @param \Magento\Catalog\Model\Design
     */
    private $_catalogDesign;

    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Catalog\Model\Design $_catalogDesign
    )
    {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->_catalogDesign = $_catalogDesign;
    }
    public function beforeExecute(
        \Magento\Catalog\Controller\Product\View $subject
    ) {
        $productId = (int) $subject->getRequest()->getParam('id');
        $categoryId = (int) $subject->getRequest()->getParam('category', false);

        $product = $this->initProduct($productId, $categoryId);
        if($product) {
            $settings = $this->_catalogDesign->getDesignSettings($product);

            if ($settings->getCustomDesign()) {
                $this->_catalogDesign->applyCustomDesign($settings->getCustomDesign());
            }
        }
    }

    /**
     * Get the product with the given ID, and optionally set the category, if given
     * @param $productId
     * @param $categoryId
     * @return \Magento\Catalog\Model\Product|ProductInterface|bool
     */
    protected function initProduct($productId, $categoryId)
    {

        try {
            $product = $this->productRepository->getById($productId, false, $this->storeManager->getStore()->getId());
        } catch (NoSuchEntityException $e) {
            return false;
        }

        try {
            $category = $this->categoryRepository->get($categoryId);
            $product->setCategory($category);
        } catch (NoSuchEntityException $e) {
            // Do nothing
        }

        return $product;
    }
}
