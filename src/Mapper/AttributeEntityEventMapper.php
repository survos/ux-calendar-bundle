<?php

declare(strict_types=1);

namespace Survos\UxCalendarBundle\Mapper;

use Survos\UxCalendarBundle\Attribute\CalendarAllDay;
use Survos\UxCalendarBundle\Attribute\CalendarEnd;
use Survos\UxCalendarBundle\Attribute\CalendarStart;
use Survos\UxCalendarBundle\Attribute\CalendarTitle;
use Survos\UxCalendarBundle\Attribute\CalendarUrl;
use Survos\UxCalendarBundle\Contract\CalendarEntityInterface;
use Survos\UxCalendarBundle\Dto\CalendarEvent;

final class AttributeEntityEventMapper
{
    public function map(object $entity): CalendarEvent
    {
        if ($entity instanceof CalendarEntityInterface) {
            return $entity->toCalendarEvent();
        }

        $reflection = new \ReflectionObject($entity);

        $title = $this->extractValue($reflection, $entity, CalendarTitle::class);
        $start = $this->extractValue($reflection, $entity, CalendarStart::class);
        $end = $this->extractValue($reflection, $entity, CalendarEnd::class);
        $allDay = $this->extractValue($reflection, $entity, CalendarAllDay::class) ?? false;
        $url = $this->extractValue($reflection, $entity, CalendarUrl::class);

        if (!is_string($title) || !$start instanceof \DateTimeInterface) {
            throw new \InvalidArgumentException(sprintf('Entity "%s" is missing required calendar mapping attributes.', $reflection->getName()));
        }

        return new CalendarEvent(
            id: method_exists($entity, 'getId') ? (string) $entity->getId() : null,
            title: $title,
            start: $start,
            end: $end instanceof \DateTimeInterface ? $end : null,
            allDay: (bool) $allDay,
            url: is_string($url) ? $url : null,
        );
    }

    private function extractValue(\ReflectionObject $reflection, object $entity, string $attributeClass): mixed
    {
        foreach ($reflection->getProperties() as $property) {
            if ([] === $property->getAttributes($attributeClass)) {
                continue;
            }

            $property->setAccessible(true);

            return $property->getValue($entity);
        }

        foreach ($reflection->getMethods() as $method) {
            if ($method->getNumberOfRequiredParameters() > 0 || [] === $method->getAttributes($attributeClass)) {
                continue;
            }

            return $method->invoke($entity);
        }

        return null;
    }
}
