<?php

declare(strict_types=1);

namespace Survos\UxCalendarBundle\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('full_calendar', template: '@SurvosUxCalendar/components/full_calendar.html.twig')]
final class FullCalendarComponent
{
    public function __construct(
        public ?string $stimulusController = null,
    ) {
    }

    public ?string $url = null;
    public ?string $icsUrl = null;
    public string $format = 'json';
    public string $initialView = 'dayGridMonth';
    public string $timeZone = 'UTC';
    public bool $editable = false;
    public bool $navLinks = true;
    public bool $dayMaxEvents = true;
    public array $filters = [];
    public array $headerToolbar = [
        'left' => 'prev,next today',
        'center' => 'title',
        'right' => 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
    ];
    public array $options = [];
    public string $calendarClass = 'ux-calendar';
}
