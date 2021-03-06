<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Block\Product;

/**
 * Test class for \Magento\Catalog\Block\Product\List.
 *
 * @magentoDataFixture Magento/Catalog/_files/product_simple.php
 */
class ListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\ListProduct
     */
    protected $_block;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\State')
            ->setAreaCode('frontend');
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Catalog\Block\Product\ListProduct'
        );
    }

    public function testGetLayer()
    {
        $this->assertInstanceOf('Magento\Catalog\Model\Layer', $this->_block->getLayer());
    }

    public function testGetLoadedProductCollection()
    {
        $this->_block->setShowRootCategory(true);
        $collection = $this->_block->getLoadedProductCollection();
        $this->assertInstanceOf('Magento\Catalog\Model\Resource\Product\Collection', $collection);
        /* Check that root category was defined for Layer as current */
        $this->assertEquals(2, $this->_block->getLayer()->getCurrentCategory()->getId());
    }

    /**
     * @covers \Magento\Catalog\Block\Product\ListProduct::getToolbarBlock
     * @covers \Magento\Catalog\Block\Product\ListProduct::getMode
     * @covers \Magento\Catalog\Block\Product\ListProduct::getToolbarHtml
     * @covers \Magento\Catalog\Block\Product\ListProduct::toHtml
     */
    public function testToolbarCoverage()
    {
        /** @var $parent \Magento\Catalog\Block\Product\ListProduct */
        $parent = $this->_getLayout()->createBlock('Magento\Catalog\Block\Product\ListProduct', 'parent');

        /* Prepare toolbar block */
        $toolbar = $parent->getToolbarBlock();
        $this->assertInstanceOf('Magento\Catalog\Block\Product\ProductList\Toolbar', $toolbar, 'Default Toolbar');

        $parent->setChild('toolbar', $toolbar);
        /* In order to initialize toolbar collection block toHtml should be called before toolbar toHtml */
        $this->assertEmpty($parent->toHtml(), 'Block HTML'); /* Template not specified */
        $this->assertEquals('grid', $parent->getMode(), 'Default Mode'); /* default mode */
        $this->assertNotEmpty($parent->getToolbarHtml(), 'Toolbar HTML'); /* toolbar for one simple product */
    }

    public function testGetAdditionalHtmlEmpty()
    {
        $this->_block->setLayout($this->_getLayout());
        $this->assertEmpty($this->_block->getAdditionalHtml());
    }

    public function testGetAdditionalHtml()
    {
        $layout = $this->_getLayout();
        /** @var $parent \Magento\Catalog\Block\Product\ListProduct */
        $parent = $layout->createBlock('Magento\Catalog\Block\Product\ListProduct');
        $childBlock = $layout->createBlock(
            'Magento\Framework\View\Element\Text',
            'test',
            array('data' => array('text' => 'test'))
        );
        $layout->setChild($parent->getNameInLayout(), $childBlock->getNameInLayout(), 'additional');
        $this->assertEquals('test', $parent->getAdditionalHtml());
    }

    public function testSetCollection()
    {
        $this->_block->setCollection('test');
        $this->assertEquals('test', $this->_block->getLoadedProductCollection());
    }

    public function testGetPriceBlockTemplate()
    {
        $this->assertNull($this->_block->getPriceBlockTemplate());
        $this->_block->setData('price_block_template', 'test');
        $this->assertEquals('test', $this->_block->getPriceBlockTemplate());
    }

    public function testPrepareSortableFieldsByCategory()
    {
        /** @var $category \Magento\Catalog\Model\Category */
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Category'
        );
        $category->setDefaultSortBy('name');
        $this->_block->prepareSortableFieldsByCategory($category);
        $this->assertEquals('name', $this->_block->getSortBy());
    }

    protected function _getLayout()
    {
        return \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
    }
}
