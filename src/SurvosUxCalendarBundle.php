<?php

declare(strict_types=1);

namespace Survos\UxCalendarBundle;

use Survos\UxCalendarBundle\Components\FullCalendarComponent;
use Survos\UxCalendarBundle\Contract\EventSourceInterface;
use Survos\UxCalendarBundle\Controller\CalendarFeedController;
use Survos\UxCalendarBundle\Mapper\AttributeEntityEventMapper;
use Survos\UxCalendarBundle\Service\EventSourceRegistry;
use Survos\UxCalendarBundle\Source\ConfiguredIcsSource;
use Survos\UxCalendarBundle\Source\IcsEventSource;
use Survos\Kit\AbstractUxBundle;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

class SurvosUxCalendarBundle extends AbstractUxBundle
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
            ->setArgument('$calendars', $config['calendars'] ?? [])
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

        // Any service implementing EventSourceInterface — in this bundle OR a consumer
        // app (e.g. a DatabaseEventSource) — is tagged automatically. No services.yaml.
        $builder->registerForAutoconfiguration(EventSourceInterface::class)
            ->addTag('survos.ux_calendar.event_source')
        ;

        $builder->register(EventSourceRegistry::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$sources', tagged_iterator('survos.ux_calendar.event_source'))
        ;

        $builder->register(IcsEventSource::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
        ;

        $builder->register(ConfiguredIcsSource::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$calendars', $config['calendars'] ?? [])
        ;
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('stimulus_controller')
                    ->defaultValue('@survos/ux-calendar-bundle/fullcalendar')
                ->end()
                ->arrayNode('calendars')
                    ->info('Named iCal calendars to aggregate, keyed by a short id.')
                    ->useAttributeAsKey('id')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('label')->defaultNull()->end()
                            ->scalarNode('color')->defaultNull()->end()
                            ->scalarNode('url')->isRequired()->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
