<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class HomepageController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function __invoke(): Response
    {
        return $this->render('homepage.html.twig', [
            'eventsUrl' => $this->generateUrl('survos_ux_calendar_feed', [], UrlGeneratorInterface::ABSOLUTE_PATH),
            'icsUrl' => 'https://www.calendarlabs.com/ical-calendar/ics/76/US_Holidays.ics',
        ]);
    }
}
