<?php

declare(strict_types=1);

namespace Survos\UxCalendarBundle\Dto;

final readonly class CalendarEvent
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public ?string $id,
        public string $title,
        public \DateTimeInterface $start,
        public ?\DateTimeInterface $end = null,
        public bool $allDay = false,
        public ?string $url = null,
        public ?string $description = null,
        public ?string $location = null,
        public ?string $resourceId = null,
        public array $metadata = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'title' => $this->title,
            'start' => $this->start->format(\DateTimeInterface::ATOM),
            'allDay' => $this->allDay,
        ];

        if ($this->id) {
            $data['id'] = $this->id;
        }

        if ($this->end) {
            $data['end'] = $this->end->format(\DateTimeInterface::ATOM);
        }

        if ($this->url) {
            $data['url'] = $this->url;
        }

        if ($this->resourceId) {
            $data['resourceId'] = $this->resourceId;
        }

        if ($this->description) {
            $data['description'] = $this->description;
        }

        if ($this->location) {
            $data['location'] = $this->location;
        }

        return [...$data, ...$this->metadata];
    }
}
