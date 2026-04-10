# UX Calendar Bundle

`survos/ux-calendar-bundle` is a small Symfony UX bundle for rendering FullCalendar with AssetMapper, Twig components, and Stimulus.

## Current scope

- AssetMapper-aware bundle setup
- Twig component for rendering a calendar shell
- Stimulus controller wired to FullCalendar
- Support for passing a JSON feed URL and an optional iCal URL as component input
- Backend contracts for normalized calendar events and event sources
- iCal import via `johngrogg/ics-parser`

## Example

```twig
<twig:full_calendar
    url="/fc-load-events"
    icsUrl="https://www.calendarlabs.com/ical-calendar/ics/76/US_Holidays.ics"
    initialView="dayGridMonth"
/>
```

## iCal

This bundle now includes the first step toward first-class iCal parsing via an `EventSourceInterface` contract plus an `IcsEventSource` adapter backed by `johngrogg/ics-parser`.

The intended architecture is:

- `EventSourceInterface` for calendar providers
- an event DTO normalized for FullCalendar and iCal export/import
- one or more iCal adapters/parsers
- optional export mapping, likely using `spatie/icalendar-generator`
- optional entity mapping via attributes and `CalendarEntityInterface`

That is the cleaner path if you want JSON feeds, iCal ingestion, and later iCal export to coexist without baking transport details into the Stimulus controller.

## Finding calendars

You can find public iCal calendars to test against at [Calendar Labs](https://www.calendarlabs.com/ical-calendar/).
