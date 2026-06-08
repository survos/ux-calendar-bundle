# Draft issue for symfony/symfony

> Title: `[AssetMapper] jsDelivr "/+esm" resolver produces broken output for some packages (e.g. FullCalendar) — need a raw-ESM escape hatch`

---

### Description

`importmap:require` resolves remote packages through jsDelivr's `/+esm` endpoint (`JsDelivrEsmResolver`). For most packages that's fine, but for some — notably **FullCalendar** (v6 and v7, a preact-based library) — the `/+esm` build is broken at runtime, so the package cannot be used via AssetMapper at all.

At render time you get:

```
TypeError: Class constructor component cannot be invoked without 'new'
    at <fullcalendar …/+esm>.render
    at <preact …/+esm>   ← preact diff
```

This is **not** a Symfony bug per se — the same failure reproduces with plain `/+esm` imports in a bare HTML page (reported to jsDelivr), and the **raw published ESM of the identical version works fine**. But because AssetMapper only ever fetches `/+esm`, there is no supported way to consume such a package.

### Why it matters

FullCalendar is widely used, and "it doesn't work with AssetMapper" has been an open pain point for years (fullcalendar/fullcalendar#7474, originally filed against Symfony AssetMapper's jsDelivr loading). The only working approach today is to **bypass** AssetMapper for these packages: remove them from `importmap.php` and hand-write a second `<script type="importmap">` pointing at the raw files — which defeats the purpose of AssetMapper.

### Proposal

A way to opt a package into **raw-ESM** resolution instead of `/+esm`. A few possible shapes (open to direction):

1. A per-entry flag in `importmap.php`, e.g. `'fullcalendar' => ['version' => '7.0.0-rc.3', 'esm' => false]`, that downloads the raw published files (and follows their relative `./chunks/*` imports) instead of the `/+esm` bundle.
2. A resolver option / alternate resolver that uses jsDelivr's raw file URLs.
3. At minimum, documentation of the FullCalendar workaround and a note that `/+esm` can produce broken output for some packages.

### Repro / details

Minimal bare-HTML repro and the working raw-ESM import map are in fullcalendar/fullcalendar#7474 and the jsDelivr issue. Raw URLs that work (FullCalendar 7.0.0-rc.3):

```
fullcalendar          → …/npm/fullcalendar@7.0.0-rc.3/index.js
fullcalendar/daygrid  → …/npm/fullcalendar@7.0.0-rc.3/daygrid.js   (+ timegrid/list/themes/classic)
preact                → …/npm/preact@10.29.1/dist/preact.mjs
preact/compat         → …/npm/preact@10.19.5/compat/dist/compat.mjs   (+ hooks, jsx-runtime, compat/client)
@full-ui/headless-calendar, temporal-polyfill/fns/*
```

Happy to work up a PR once there's agreement on the preferred shape.
