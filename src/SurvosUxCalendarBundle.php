<?php

declare(strict_types=1);

namespace Survos\UxCalendarBundle;

use Survos\UxCalendarBundle\Components\FullCalendarComponent;
use Survos\UxCalendarBundle\Controller\CalendarFeedController;
use Survos\UxCalendarBundle\Mapper\AttributeEntityEventMapper;
use Survos\UxCalendarBundle\Service\EventSourceRegistry;
use Survos\UxCalendarBundle\Source\IcsEventSource;
use Survos\CoreBundle\Bundle\AssetMapperBundle;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

class SurvosUxCalendarBundle extends AssetMapperBundle
{
    public const ASSET_PACKAGE = 'ux-calendar';

    /**
     * @param array<mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->register(FullCalendarComponent::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$stimulusController', $config['stimulus_controller'])
        ;

        $builder->autowire(CalendarFeedController::class)
            ->addTag('container.service_subscriber')
            ->addTag('controller.service_arguments')
            ->setPublic(true)
        ;

        $builder->register(AttributeEntityEventMapper::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
        ;

        $builder->register(EventSourceRegistry::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$sources', tagged_iterator('survos.ux_calendar.event_source'))
        ;

        $builder->register(IcsEventSource::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->addTag('survos.ux_calendar.event_source')
        ;
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
            ->scalarNode('stimulus_controller')
                ->defaultValue('@survos/ux-calendar/fullcalendar')
            ->end()
            ->end();
    }
}
