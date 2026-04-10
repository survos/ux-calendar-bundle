<?php

declare(strict_types=1);

namespace Survos\UxCalendarBundle\Controller;

use Survos\UxCalendarBundle\Service\EventSourceRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class CalendarFeedController extends AbstractController
{
    public function __construct(
        private readonly EventSourceRegistry $registry,
    ) {
    }

    #[Route('/ux-calendar/events', name: 'survos_ux_calendar_feed', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $start = $this->parseDate($request->get('start'), 'start');
        $end = $this->parseDate($request->get('end'), 'end');
        $filters = $this->parseFilters($request->get('filters', []));

        $events = array_map(
            static fn($event) => $event->toArray(),
            $this->registry->getEvents($start, $end, $filters),
        );

        return $this->json($events);
    }

    private function parseDate(mixed $value, string $name): ?\DateTimeImmutable
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (!is_string($value)) {
            throw new BadRequestHttpException(sprintf('Query parameter "%s" must be a string.', $name));
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Throwable $e) {
            throw new BadRequestHttpException(sprintf('Query parameter "%s" must be a valid date string.', $name), $e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function parseFilters(mixed $filters): array
    {
        if (is_array($filters)) {
            return $filters;
        }

        if (is_string($filters) && '' !== $filters) {
            $decoded = json_decode($filters, true);

            if (is_array($decoded)) {
                return $decoded;
            }

            throw new BadRequestHttpException('Query parameter "filters" must be valid JSON or an array.');
        }

        return [];
    }
}
