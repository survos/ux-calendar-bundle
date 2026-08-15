# UX Calendar Bundle

`survos/ux-calendar-bundle` is a small Symfony UX bundle for rendering FullCalendar with AssetMapper, Twig components, and Stimulus.

## Where this fits

Three projects, three jobs — don't confuse them:

| Project | Role |
|---|---|
| **survos/ux-calendar-bundle** (this) | The **rendering/aggregation library**: FullCalendar v7 + AssetMapper + a normalized `EventSourceInterface` to merge iCal feeds. |
| **[ccal](https://github.com/survos/ccal)** | The **Community Calendar Aggregator app**: orgs, feeds, moderation, user subscriptions, event flags. Consumes this bundle. |
| `demo/` (in this repo) | A **minimal smoke-test** of the bundle. Not the app — that's ccal. |

> Deprecated/archived predecessors that blurred these lines: `survos/rappcal`,
> `tacman/CalendarBundle` (tattali fork), `survos-sites/calendar-demo`.

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

## Installing the FullCalendar assets (required)

**FullCalendar is deliberately *not* declared in this bundle's `symfony.importmap`.** Only
`@hotwired/stimulus` is, because that's the one dependency AssetMapper can resolve safely.

AssetMapper's remote-package provider downloads jsDelivr's `/+esm` auto-bundled builds, and that
transform mangles FullCalendar's preact class components — you get
`Class constructor component cannot be invoked without 'new'` at runtime
([fullcalendar#7474](https://github.com/fullcalendar/fullcalendar/issues/7474)). Listing
`fullcalendar` here would mean every `composer require` of this bundle silently installs a broken
calendar.

Instead, consumer apps supply the raw published ESM files via a second `<script type="importmap">`
in their base template, before `{{ importmap('app') }}`. The browser merges multiple importmaps.
Copy this block (see `demo/templates/base.html.twig` for the working reference):

```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@7.0.0-rc.3/skeleton.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@7.0.0-rc.3/themes/classic/theme.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@7.0.0-rc.3/themes/classic/palette.css">

<script type="importmap">
{
  "imports": {
    "preact": "https://cdn.jsdelivr.net/npm/preact@10.29.1/dist/preact.mjs",
    "preact/hooks": "https://cdn.jsdelivr.net/npm/preact@10.19.5/hooks/dist/hooks.mjs",
    "preact/jsx-runtime": "https://cdn.jsdelivr.net/npm/preact@10.19.5/jsx-runtime/dist/jsxRuntime.mjs",
    "preact/compat": "https://cdn.jsdelivr.net/npm/preact@10.19.5/compat/dist/compat.mjs",
    "preact/compat/client": "https://cdn.jsdelivr.net/npm/preact@10.19.5/compat/client.mjs",
    "temporal-polyfill/fns/zoneddatetime": "https://cdn.jsdelivr.net/npm/temporal-polyfill@0.3.2/fns/zoneddatetime.js",
    "temporal-polyfill/fns/plaindatetime": "https://cdn.jsdelivr.net/npm/temporal-polyfill@0.3.2/fns/plaindatetime.js",
    "temporal-polyfill/fns/instant": "https://cdn.jsdelivr.net/npm/temporal-polyfill@0.3.2/fns/instant.js",
    "@full-ui/headless-calendar": "https://cdn.jsdelivr.net/npm/@full-ui/headless-calendar@7.0.0-rc.3/index.js",
    "fullcalendar": "https://cdn.jsdelivr.net/npm/fullcalendar@7.0.0-rc.3/index.js",
    "fullcalendar/themes/classic": "https://cdn.jsdelivr.net/npm/fullcalendar@7.0.0-rc.3/themes/classic.js",
    "fullcalendar/daygrid": "https://cdn.jsdelivr.net/npm/fullcalendar@7.0.0-rc.3/daygrid.js",
    "fullcalendar/timegrid": "https://cdn.jsdelivr.net/npm/fullcalendar@7.0.0-rc.3/timegrid.js",
    "fullcalendar/list": "https://cdn.jsdelivr.net/npm/fullcalendar@7.0.0-rc.3/list.js"
  }
}
</script>
```

Hand-writing this per app is the known wart — [survos/mono#5](https://github.com/survos/mono/issues/5)
tracks moving it behind a Twig helper shipped by this bundle.

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
