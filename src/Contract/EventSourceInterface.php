<?php

declare(strict_types=1);

namespace Survos\UxCalendarBundle\Contract;

use Survos\UxCalendarBundle\Dto\CalendarEvent;

interface EventSourceInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function supports(array $context = []): bool;

    /**
     * @param array<string, mixed> $context
     *
     * @return iterable<CalendarEvent>
     */
    public function getEvents(?\DateTimeInterface $start = null, ?\DateTimeInterface $end = null, array $context = []): iterable;
}
