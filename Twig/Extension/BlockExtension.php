<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\Twig\Extension;

use Sonatra\Bundle\BlockBundle\Block\Block;
use Sonatra\Bundle\BlockBundle\Block\BlockFactoryInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockView;
use Sonatra\Bundle\BlockBundle\Block\BlockRegistryInterface;
use Sonatra\Bundle\BlockBundle\Twig\TokenParser\BlockThemeTokenParser;
use Sonatra\Bundle\BlockBundle\Twig\TokenParser\SuperblockTokenParser;
use Sonatra\Bundle\BlockBundle\Twig\Block\TwigRendererInterface;
use Sonatra\Bundle\BlockBundle\Block\BlockInterface;
use Sonatra\Bundle\BlockBundle\Block\Util\BlockUtil;

/**
 * BlockExtension extends Twig with block capabilities.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockExtension extends \Twig_Extension
{
    /**
     * This property is public so that it can be accessed directly from compiled
     * templates without having to call a getter, which slightly decreases performance.
     * @var \Sonatra\Bundle\BlockBundle\Block\BlockRendererInterface
     */
    public $renderer;

    /**
     * @var BlockFactoryInterface
     */
    protected $factory;

    /**
     * @var BlockRegistryInterface
     */
    protected $registry;

    /**
     * @var array
     */
    protected $types;

    /**
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * Constructor.
     *
     * @param TwigRendererInterface $renderer
     */
    public function __construct(TwigRendererInterface $renderer, BlockFactoryInterface $factory, BlockRegistryInterface $registry)
    {
        $this->renderer = $renderer;
        $this->factory = $factory;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
        $this->renderer->setEnvironment($environment);
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        $tokens = array(
            // {% block_theme form "SomeBundle::widgets.twig" %}
            new BlockThemeTokenParser(),
            // {% sblock 'checkbox', {data: true, label: "My checkbox" with {my_var: "the twig variable"} :%}
            new SuperblockTokenParser(),
        );

        foreach ($this->getTypes() as $type) {
            $tokens[] = new SuperblockTokenParser('sblock_'.$type);
        }

        return $tokens;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $functions = array(
            new \Twig_SimpleFunction('block_widget',      'compile', array('node_class' => 'Sonatra\Bundle\BlockBundle\Twig\Node\SearchAndRenderBlockNode', 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('block_component',   'compile', array('node_class' => 'Sonatra\Bundle\BlockBundle\Twig\Node\SearchAndRenderBlockNode', 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('block_label',       'compile', array('node_class' => 'Sonatra\Bundle\BlockBundle\Twig\Node\SearchAndRenderBlockNode', 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('block_row',         'compile', array('node_class' => 'Sonatra\Bundle\BlockBundle\Twig\Node\SearchAndRenderBlockNode', 'is_safe' => array('html'))),
            new \Twig_SimpleFunction('sblock',            array($this, 'createAndRenderSuperblock'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('block_twig_render', array($this, 'renderTwigBlock'), array('is_safe' => array('html'))),
        );

        foreach ($this->getTypes() as $type) {
            $closure = function(array $options = array(), array $variables = array()) use ($type) {
                return '';
            };

            $functions[] = new \Twig_SimpleFunction('sblock_'.$type, $closure, array('is_safe' => array('html')));
        }

        return $functions;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('block_humanize', array($this->renderer, 'humanize')),
            new \Twig_SimpleFilter('raw_closure', array($this, 'rawClosure')),
            new \Twig_SimpleFilter('block_formatter', array($this, 'formatter'), array('is_safe' => array('html'))),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sonatra_block';
    }

    /**
     * Get block factory.
     *
     * @return BlockFactoryInterface
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * Create and render a superblock.
     *
     * @param string|BlockTypeInterface|BlockInterface|BlockView $type
     * @param array                                              $options
     * @param array                                              $variables The twig variables
     *
     * @return string The html
     */
    public function createAndRenderSuperblock($type, array $options = array(), array $variables = array())
    {
        $view = $type instanceof BlockView ? $type : $this->createNamed($type, $options)->createView();

        return $this->renderer->searchAndRenderBlock($view, 'widget', $variables);
    }

    /**
     * Create block named with the 'block_name' options.
     *
     * @param string|BlockTypeInterface|BlockInterface $type
     * @param array                                    $options
     *
     * @return \Sonatra\Bundle\BlockBundle\Block\BlockBuilderInterface
     */
    public function createNamed($type, array $options = array())
    {
        if ($type instanceof BlockInterface) {
            return $type;
        }

        $name = $this->getBlockName($options);

        return $this->factory->createNamed($name, $type, null, $options);
    }

    /**
     * Render the block of twig resource.
     *
     * @param string $resource
     * @param string $blockName
     * @param array  $options
     *
     * @return string
     */
    public function renderTwigBlock($resource, $blockName, array $options = array())
    {
        if (null !== $this->environment) {
            $template = $this->environment->loadTemplate($resource);

            return $template->renderBlock($blockName, $options);
        }

        return '';
    }

    /**
     * Render closure value.
     *
     * @param string|\Closure $value
     * @param BlockView       $view
     *
     * @return string
     */
    public function rawClosure($value, BlockView $view)
    {
        if ($value instanceof \Closure) {
            $value = $value($view);
        }

        return $value;
    }

    /**
     * Format the value.
     *
     * @param mixed  $value     The value to format
     * @param string $type      The formatter type
     * @param array  $options   The block options
     * @param array  $variables The template variables
     *
     * @return string
     */
    public function formatter($value, $type, array $options = array(), array $variables = array())
    {
        $options = array_merge($options, array('data' => $value));
        $name = $this->getBlockName($options);
        $block = $this->factory->createNamed($name, $type, null, $options);

        return $block->getViewData();
    }

    /**
     * Get block types.
     *
     * @return array
     */
    protected function getTypes()
    {
        if (null === $this->types) {
            $this->types = array();
            $refl = new \ReflectionClass($this->registry);
            $prop = $refl->getProperty('extensions');
            $prop->setAccessible(true);
            $extensions = $prop->getValue($this->registry);

            foreach ($extensions as $extension) {
                $refl = new \ReflectionClass($extension);
                $prop = $refl->getProperty('typeServiceIds');
                $prop->setAccessible(true);
                $types = array_keys($prop->getValue($extension));
                $this->types = array_merge($this->types, $types);
            }
        }

        return $this->types;
    }

    /**
     * Get the block name.
     *
     * @param array $options
     *
     * @return string|null
     */
    protected function getBlockName(array $options = array())
    {
        return isset($options['block_name']) ? $options['block_name'] : (isset($options['id']) ? $options['id'] : BlockUtil::createUniqueName());
    }
}
