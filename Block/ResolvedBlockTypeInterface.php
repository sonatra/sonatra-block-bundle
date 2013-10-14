<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Block;

/**
 * A wrapper for a block type and its extensions.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface ResolvedBlockTypeInterface
{
    /**
     * Returns the name of the type.
     *
     * @return string The type name.
     */
    public function getName();

    /**
     * Returns the parent type.
     *
     * @return ResolvedBlockTypeInterface The parent type or null.
     */
    public function getParent();

    /**
     * Returns the wrapped block type.
     *
     * @return BlockTypeInterface The wrapped block type.
     */
    public function getInnerType();

    /**
     * Returns the extensions of the wrapped block type.
     *
     * @return BlockTypeExtensionInterface[] An array of {@link BlockTypeExtensionInterface} instances.
     */
    public function getTypeExtensions();

    /**
     * Creates a new block builder for this type.
     *
     * @param BlockFactoryInterface $factory The block factory.
     * @param string                $name    The name for the builder.
     * @param array                 $options The builder options.
     *
     * @return BlockBuilderInterface The created block builder.
     */
    public function createBuilder(BlockFactoryInterface $factory, $name, array $options = array());

    /**
     * Creates a new block view for a block of this type.
     *
     * @param BlockInterface $block  The block to create a block view for.
     * @param BlockView      $parent The parent block view or null.
     *
     * @return BlockView The created block view.
     */
    public function createView(BlockInterface $block, BlockView $parent = null);

    /**
     * Configures a block builder for the type hierarchy.
     *
     * @param BlockBuilderInterface $builder The builder to configure.
     * @param array                 $options The options used for the configuration.
     */
    public function buildBlock(BlockBuilderInterface $builder, array $options);

    /**
     * Action when the block adds a child.
     *
     * @param BlockInterface $child   The child block
     * @param BlockInterface $block   The block
     * @param array          $options The options
     */
    public function addChild(BlockInterface $child, BlockInterface $block, array $options);

    /**
     * Action when the block removes a child.
     *
     * @param BlockInterface $child   The child block
     * @param BlockInterface $block   The block
     * @param array          $options The options
     */
    public function removeChild(BlockInterface $child, BlockInterface $block, array $options);

    /**
     * Configures a block view for the type hierarchy.
     *
     * It is called before the children of the block are built.
     *
     * @param BlockView      $view    The block view to configure.
     * @param BlockInterface $block   The block corresponding to the block view.
     * @param array          $options The options used for the configuration.
     */
    public function buildView(BlockView $view, BlockInterface $block, array $options);

    /**
     * Finishes a block view for the type hierarchy.
     *
     * It is called after the children of the block view have been built.
     *
     * @param BlockView      $view    The block view to configure.
     * @param BlockInterface $block   The block corresponding to the block view.
     * @param array          $options The options used for the configuration.
     */
    public function finishView(BlockView $view, BlockInterface $block, array $options);

    /**
     * Returns the configured options resolver used for this type.
     *
     * @return \Symfony\Component\OptionsResolver\OptionsResolverInterface The options resolver.
     */
    public function getOptionsResolver();
}
