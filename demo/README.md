# ux-calendar-bundle / demo

> **This is the bundle's minimal smoke-test app** — it proves `survos/ux-calendar-bundle`
> renders FullCalendar (v7, raw ESM) under AssetMapper and aggregates a few sample iCal
> feeds with color-coding + per-calendar toggles.
>
> **It is NOT the community calendar app.** The real, deployable Community Calendar
> Aggregator (orgs, feeds, moderation, user subscriptions, event flags) is
> **[ccal](https://github.com/survos/ccal)**, which consumes this bundle for rendering.

## Run

```bash
composer install
php bin/console importmap:install
php -S 127.0.0.1:8123 -t public
```

Then open http://127.0.0.1:8123/. Sample calendars are configured in
`config/packages/survos_ux_calendar.yaml`.
