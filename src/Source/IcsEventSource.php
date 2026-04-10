<?php

declare(strict_types=1);

namespace Survos\UxCalendarBundle\Source;

use ICal\ICal;
use Survos\UxCalendarBundle\Contract\EventSourceInterface;
use Survos\UxCalendarBundle\Dto\CalendarEvent;

final class IcsEventSource implements EventSourceInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function supports(array $context = []): bool
    {
        return is_string($context['icsUrl'] ?? null) && '' !== trim($context['icsUrl']);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return iterable<CalendarEvent>
     */
    public function getEvents(?\DateTimeInterface $start = null, ?\DateTimeInterface $end = null, array $context = []): iterable
    {
        if (!$this->supports($context)) {
            return [];
        }

        $ical = new ICal($context['icsUrl']);
        $events = ($start && $end)
            ? $ical->eventsFromRange($start->format('c'), $end->format('c'))
            : $ical->events();

        return array_map(
            fn(object $event): CalendarEvent => $this->mapEvent($event),
            $events,
        );
    }

    private function mapEvent(object $event): CalendarEvent
    {
        $start = $event->dtstart instanceof \DateTimeInterface ? $event->dtstart : new \DateTimeImmutable((string) $event->dtstart);
        $end = null;

        if (isset($event->dtend) && '' !== (string) $event->dtend) {
            $end = $event->dtend instanceof \DateTimeInterface ? $event->dtend : new \DateTimeImmutable((string) $event->dtend);
        }

        $allDay = strlen((string) $event->dtstart) === 8;

        return new CalendarEvent(
            id: isset($event->uid) ? (string) $event->uid : null,
            title: (string) ($event->summary ?? 'Untitled event'),
            start: $start,
            end: $end,
            allDay: $allDay,
            description: isset($event->description) ? (string) $event->description : null,
            location: isset($event->location) ? (string) $event->location : null,
            metadata: [
                'icsUid' => isset($event->uid) ? (string) $event->uid : null,
            ],
        );
    }
}
