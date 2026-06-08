# Draft comment for fullcalendar/fullcalendar#7474

> Post this as a comment on https://github.com/fullcalendar/fullcalendar/issues/7474

---

Confirming this and, hopefully, closing the loop for anyone arriving here from Symfony **AssetMapper** / native import maps.

**It's the jsDelivr `/+esm` build, not FullCalendar.** AssetMapper's import-map resolver downloads the `https://cdn.jsdelivr.net/npm/<pkg>/+esm` variant of each package. That `+esm` build of FullCalendar throws at runtime; the **raw published ESM of the identical version works**.

Minimal reproduction — **no bundler, no Symfony**, just three `+esm` imports in a plain HTML page:

```html
<script type="module">
  const { Calendar } = await import('https://cdn.jsdelivr.net/npm/fullcalendar@7.0.0-rc.3/+esm');
  const daygrid = (await import('https://cdn.jsdelivr.net/npm/fullcalendar@7.0.0-rc.3/daygrid/+esm')).default;
  new Calendar(document.getElementById('cal'), { plugins:[daygrid], initialView:'dayGridMonth' }).render();
</script>
```
→
```
TypeError: Class constructor component cannot be invoked without 'new'
    at Tn.render (https://cdn.jsdelivr.net/npm/fullcalendar@7.0.0-rc.3/+esm:7:37050)
    at $ (https://cdn.jsdelivr.net/npm/preact@10.29.1/+esm …)   ← preact diff
```

The crash is inside FullCalendar's render, where a generator is dispatched with `typeof r === "function" ? r(props, h) : r` — under the `+esm` build a preact `Component` **class** reaches that branch and is invoked without `new`. Swap the two URLs to the **raw** files (`…/fullcalendar@7.0.0-rc.3/index.js`, `…/daygrid.js`) and it renders.

I checked the obvious suspects: it is **not** preact duplication (at a single version, `(await import('…/preact@10.29.1/+esm')).Component === (await import('…/preact@10.29.1/compat/+esm')).Component` is `true`), and FullCalendar's `+esm` uses native `class extends` (no ES5 downlevel). Something in how jsDelivr's `+esm` bundles FullCalendar's class components is the trigger — I've filed a parallel issue with jsDelivr. Reproduces with **both v6 and v7** via `/+esm`.

**The fix: use the raw published ESM, not `/+esm`.** FullCalendar's own importmap manual test (`standard/packages/vanilla-tests/manual/importmap.public.html`) already does exactly this and works. The raw files load their own `./chunks/*` from the CDN and resolve bare specifiers (`preact`, `fullcalendar`, …) through the import map. Verified rendering (v7.0.0-rc.3) under Symfony AssetMapper with this import map:

```html
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

CSS in v7 is separate (no JS auto-injection): `…/fullcalendar@7.0.0-rc.3/skeleton.css` + `themes/classic/theme.css` + `themes/classic/palette.css`.

For Symfony specifically, the practical workaround is to **remove** `fullcalendar`/`preact` from `importmap.php` (so AssetMapper doesn't fetch `/+esm`) and supply the raw URLs in a second `<script type="importmap">` (browsers merge multiple import maps). I've filed a corresponding request on the Symfony side so AssetMapper can do this without the manual step.

TL;DR: **`/+esm` mangles FullCalendar; the raw published ESM works.** Might be worth a docs note for import-map / AssetMapper / Vite-CDN users.
