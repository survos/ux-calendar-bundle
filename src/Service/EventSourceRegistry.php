<?php

declare(strict_types=1);

namespace Survos\UxCalendarBundle\Service;

use Survos\UxCalendarBundle\Contract\EventSourceInterface;
use Survos\UxCalendarBundle\Dto\CalendarEvent;

final readonly class EventSourceRegistry
{
    /**
     * @param iterable<EventSourceInterface> $sources
     */
    public function __construct(
        private iterable $sources,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return list<CalendarEvent>
     */
    public function getEvents(?\DateTimeInterface $start = null, ?\DateTimeInterface $end = null, array $context = []): array
    {
        $events = [];

        foreach ($this->sources as $source) {
            if (!$source->supports($context)) {
                continue;
            }

            foreach ($source->getEvents($start, $end, $context) as $event) {
                $events[] = $event;
            }
        }

        return $events;
    }
}
