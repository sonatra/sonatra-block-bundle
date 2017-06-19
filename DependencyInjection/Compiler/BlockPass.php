<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\BlockBundle\DependencyInjection\Compiler;

use Sonatra\Component\Block\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Adds all services with the tags "sonatra_block.type" and "sonatra_block.type_guesser" as
 * arguments of the "sonatra_block.extension" service.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BlockPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('sonatra_block.extension')) {
            return;
        }

        $definition = $container->getDefinition('sonatra_block.extension');

        // Builds an array with service IDs as keys and tag aliases as values
        $types = array();

        foreach ($container->findTaggedServiceIds('sonatra_block.type') as $serviceId => $tag) {
            $serviceDefinition = $this->getPublicRequireDefinition($container, $serviceId, 'types');

            // Support type access by FQCN
            $types[$serviceDefinition->getClass()] = $serviceId;
        }

        $definition->replaceArgument(0, $types);

        $typeExtensions = array();

        foreach ($container->findTaggedServiceIds('sonatra_block.type_extension') as $serviceId => $tag) {
            $this->getPublicRequireDefinition($container, $serviceId, 'type extensions');

            if (isset($tag[0]['extended_type'])) {
                $extendedType = $tag[0]['extended_type'];
            } else {
                throw new InvalidArgumentException(sprintf('Tagged block type extension must have the extended type configured using the extended_type/extended-type attribute, none was configured for the "%s" service.', $serviceId));
            }

            $typeExtensions[$extendedType][] = $serviceId;
        }

        $definition->replaceArgument(1, $typeExtensions);

        // Find all services annotated with "sonatra_block.type_guesser"
        $guessers = array_keys($container->findTaggedServiceIds('sonatra_block.type_guesser'));
        foreach ($guessers as $serviceId) {
            $this->getPublicRequireDefinition($container, $serviceId, 'type guessers');
        }

        $definition->replaceArgument(2, $guessers);
    }

    /**
     * Get the service definition and validate if the service is public.
     *
     * @param ContainerBuilder $container The container builder
     * @param string           $id        The service id
     * @param string           $type      The block type
     *
     * @return Definition
     *
     * @throws InvalidArgumentException When the service is not public
     */
    private function getPublicRequireDefinition(ContainerBuilder $container, $id, $type)
    {
        $serviceDefinition = $container->getDefinition($id);

        if (!$serviceDefinition->isPublic()) {
            throw new InvalidArgumentException(sprintf('The service "%s" must be public as block %s are lazy-loaded.', $id, $type));
        }

        return $serviceDefinition;
    }
}
