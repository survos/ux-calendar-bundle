<?php

declare(strict_types=1);

namespace Survos\UxCalendarBundle\Contract;

use Survos\UxCalendarBundle\Dto\CalendarEvent;

interface CalendarEntityInterface
{
    public function toCalendarEvent(): CalendarEvent;
}
