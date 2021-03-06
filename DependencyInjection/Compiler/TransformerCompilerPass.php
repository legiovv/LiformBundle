<?php

/*
 * This file is part of the Limenius\LiformBundle package.
 *
 * (c) Limenius <https://github.com/Limenius/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Limenius\LiformBundle\DependencyInjection\Compiler;

use Limenius\Liform\Transformer\TransformerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Nacho Martín <nacho@limenius.com>
 */
class TransformerCompilerPass implements CompilerPassInterface
{
    const TRANSFORMER_TAG = 'liform.transformer';

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('liform.resolver')) {
            return;
        }

        $resolver = $container->getDefinition('liform.resolver');

        foreach ($container->findTaggedServiceIds(self::TRANSFORMER_TAG) as $id => $attributes) {
            $transformer = $container->getDefinition($id);

            if (!isset(class_implements($transformer->getClass())['Limenius\Liform\Transformer\TransformerInterface'])) {
                throw new \InvalidArgumentException(sprintf(
                    "The service %s was tagged as a '%s' but does not implement the mandatory %s",
                    $id,
                    self::TRANSFORMER_TAG,
                    'Limenius\Liform\Transformer\TransformerInterface'
                ));
            }

            foreach ($attributes as $attribute) {
                if (!isset($attribute['form_type'])) {
                    throw new \InvalidArgumentException(sprintf(
                        "The service %s was tagged as a '%s' but does not specify the mandatory 'form_type' option.",
                        $id,
                        self::TRANSFORMER_TAG
                    ));
                }

                $widget = null;

                if (isset($attribute['widget'])) {
                    $widget = $attribute['widget'];
                }

                $resolver->addMethodCall('setTransformer', array($attribute['form_type'], $transformer, $widget));
            }
        }
    }
}
