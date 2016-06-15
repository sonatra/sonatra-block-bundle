<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Tests\Block\Extension\Core\Type;

use Sonatra\Bundle\BlockBundle\Block\Extension\Core\Type\BlockType;
use Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\DataTransformer\FixedDataTransformer;
use Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\Object\Foo;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockTypeTest extends BaseTypeTest
{
    protected function getTestedType()
    {
        return BlockType::class;
    }

    public function testCreateBlockInstances()
    {
        $this->assertInstanceOf('Sonatra\Bundle\BlockBundle\Block\Block', $this->factory->create(BlockType::class));
    }

    public function testDataClassMayBeNull()
    {
        $this->factory->createBuilder(BlockType::class, null, array(
            'data_class' => null,
        ));
    }

    public function testDataClassMayBeAbstractClass()
    {
        $this->factory->createBuilder(BlockType::class, null, array(
            'data_class' => 'Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\Object\AbstractFoo',
        ));
    }

    public function testDataClassMayBeInterface()
    {
        $this->factory->createBuilder(BlockType::class, null, array(
            'data_class' => 'Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\Object\FooInterface',
        ));
    }

    public function testEmptyDataCreateNewInstanceWithoutConstructorArguments()
    {
        $block = $this->factory->create(BlockType::class, null, array(
            'data_class' => 'Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\Object\Foo',
        ));

        $this->assertEquals(new Foo(), $block->getData());
        $this->assertEquals(new Foo(), $block->getNormData());
        $this->assertEquals(new Foo(), $block->getViewData());
    }

    /**
     * @expectedException \Sonatra\Bundle\BlockBundle\Block\Exception\InvalidConfigurationException
     */
    public function testEmptyDataCreateNewInstanceWithConstructorArguments()
    {
        $this->factory->create(BlockType::class, null, array(
            'data_class' => 'Sonatra\Bundle\BlockBundle\Tests\Block\Fixtures\Object\SimpleBlockTestCountable',
        ));
    }

    public function provideZeros()
    {
        return array(
            array(0, '0'),
            array('0', '0'),
            array('00000', '00000'),
        );
    }

    /**
     * @dataProvider provideZeros
     */
    public function testSetDataThroughParamsWithZero($data, $dataAsString)
    {
        $block = $this->factory->create(BlockType::class, null, array(
            'data' => $data,
            'compound' => false,
        ));
        $view = $block->createView();

        $this->assertFalse($block->isEmpty());

        $this->assertSame($dataAsString, $view->vars['value']);
        $this->assertSame($dataAsString, $block->getData());
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function testAttributesException()
    {
        $this->factory->create(BlockType::class, null, array('attr' => ''));
    }

    public function testNameCanBeEmptyString()
    {
        $block = $this->factory->createNamed('', BlockType::class);

        $this->assertEquals('', $block->getName());
    }

    public function testViewIsNotRenderedByDefault()
    {
        $view = $this->factory->createBuilder(BlockType::class)
            ->add('foo', BlockType::class)
            ->getBlock()
            ->createView();

        $this->assertFalse($view->isRendered());
    }

    public function testPropertyPath()
    {
        $block = $this->factory->create(BlockType::class, null, array(
            'property_path' => 'foo',
            'mapped' => true,
        ));

        $this->assertEquals(new PropertyPath('foo'), $block->getPropertyPath());
        $this->assertTrue($block->getConfig()->getMapped());
    }

    public function testPropertyPathNullImpliesDefault()
    {
        $block = $this->factory->createNamed('name', BlockType::class, null, array(
            'property_path' => null,
            'mapped' => true,
        ));

        $this->assertEquals(new PropertyPath('name'), $block->getPropertyPath());
        $this->assertTrue($block->getConfig()->getMapped());
    }

    public function testNotMapped()
    {
        $block = $this->factory->create(BlockType::class, null, array(
            'property_path' => 'foo',
            'mapped' => false,
        ));

        $this->assertEquals(new PropertyPath('foo'), $block->getPropertyPath());
        $this->assertTrue($block->getConfig()->getMapped());
    }

    public function testDataOptionSupersedesSetDataCalls()
    {
        $block = $this->factory->create(BlockType::class, null, array(
            'data' => 'default',
            'compound' => false,
        ));

        $block->setData('foobar');

        $this->assertSame('foobar', $block->getData());
    }

    public function testDataOptionSupersedesSetDataCallsIfNull()
    {
        $block = $this->factory->create(BlockType::class, null, array(
            'data' => null,
            'compound' => false,
        ));

        $block->setData('foobar');

        $this->assertSame('foobar', $block->getData());
    }

    public function testNormDataIsPassedToView()
    {
        $view = $this->factory->createBuilder(BlockType::class)
            ->addViewTransformer(new FixedDataTransformer(array(
                'foo' => 'bar',
            )))
            ->setData('foo')
            ->getBlock()
            ->createView();

        $this->assertSame('foo', $view->vars['data']);
        $this->assertSame('bar', $view->vars['value']);
    }

    public function testPassZeroLabelToView()
    {
        $view = $this->factory->create(BlockType::class, null, array(
            'label' => '0',
        ))
        ->createView();

        $this->assertSame('0', $view->vars['label']);
    }
}
