<?php

declare(strict_types=1);

namespace Survos\UxCalendarBundle\Source;

use ICal\ICal;
use Psr\Log\LoggerInterface;
use Survos\UxCalendarBundle\Contract\EventSourceInterface;
use Survos\UxCalendarBundle\Dto\CalendarEvent;

/**
 * Aggregates events from a configured list of named iCal calendars, tagging each
 * event with its source id and color so the frontend can color-code and toggle them.
 *
 * Each configured calendar is a map: { label, color, url }, keyed by a short id.
 * A failed feed (404, network error, parse error) is skipped, not fatal — one bad
 * calendar must never take down the whole aggregation.
 */
final class ConfiguredIcsSource implements EventSourceInterface
{
    /**
     * @param array<string, array{label?: string, color?: string, url: string}> $calendars
     */
    public function __construct(
        private readonly array $calendars = [],
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supports(array $context = []): bool
    {
        // Contribute the configured calendars unless the caller asked for a single
        // ad-hoc icsUrl (handled by IcsEventSource), in which case stay out of the way.
        return [] !== $this->calendars && empty($context['icsUrl']);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return iterable<CalendarEvent>
     */
    public function getEvents(?\DateTimeInterface $start = null, ?\DateTimeInterface $end = null, array $context = []): iterable
    {
        $events = [];

        foreach ($this->calendars as $id => $calendar) {
            $url = $calendar['url'] ?? null;
            if (!is_string($url) || '' === $url) {
                continue;
            }

            try {
                $ical = new ICal($url, ['skipRecurrence' => false]);
            } catch (\Throwable $e) {
                $this->logger?->warning('ux-calendar: failed to load calendar "{id}": {message}', [
                    'id' => $id,
                    'message' => $e->getMessage(),
                    'url' => $url,
                ]);
                continue;
            }

            $parsed = ($start && $end)
                ? $ical->eventsFromRange($start->format('c'), $end->format('c'))
                : $ical->events();

            $color = $calendar['color'] ?? null;
            $label = $calendar['label'] ?? (string) $id;

            foreach ($parsed as $event) {
                $events[] = $this->mapEvent((string) $id, $label, $color, $event);
            }
        }

        return $events;
    }

    private function mapEvent(string $sourceId, string $label, ?string $color, object $event): CalendarEvent
    {
        $start = $event->dtstart instanceof \DateTimeInterface
            ? $event->dtstart
            : new \DateTimeImmutable((string) $event->dtstart);

        $end = null;
        if (isset($event->dtend) && '' !== (string) $event->dtend) {
            $end = $event->dtend instanceof \DateTimeInterface
                ? $event->dtend
                : new \DateTimeImmutable((string) $event->dtend);
        }

        $allDay = 8 === strlen((string) $event->dtstart);

        // Color + sourceId travel in metadata, which CalendarEvent::toArray() spreads
        // onto the top-level FullCalendar event object. `backgroundColor`/`borderColor`
        // are native FullCalendar props; `sourceId`/`sourceLabel` land in extendedProps.
        // sourceId/sourceColor are non-native keys, so FullCalendar keeps them in
        // extendedProps (native backgroundColor is dropped by the v7 classic theme).
        // The controller reads them to color and toggle each calendar.
        $metadata = [
            'sourceId' => $sourceId,
            'sourceLabel' => $label,
        ];
        if (null !== $color && '' !== $color) {
            $metadata['sourceColor'] = $color;
            $metadata['backgroundColor'] = $color;
            $metadata['borderColor'] = $color;
        }

        return new CalendarEvent(
            id: isset($event->uid) ? (string) $event->uid : null,
            title: (string) ($event->summary ?? 'Untitled event'),
            start: $start,
            end: $end,
            allDay: $allDay,
            description: isset($event->description) ? (string) $event->description : null,
            location: isset($event->location) ? (string) $event->location : null,
            metadata: $metadata,
        );
    }
}
