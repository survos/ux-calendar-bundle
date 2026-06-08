# Draft issue for jsdelivr/jsdelivr

> Title: `/+esm` build of `fullcalendar` throws "Class constructor component cannot be invoked without 'new'" at runtime (raw ESM works)

---

### What happens

The `/+esm` build of [`fullcalendar`](https://www.jsdelivr.com/package/npm/fullcalendar) (a preact-based library) throws a runtime `TypeError` when a calendar renders, while the **raw published ESM of the identical version works**.

### Minimal reproduction

Plain HTML, no bundler, no framework — three `/+esm` imports:

```html
<!DOCTYPE html><div id="cal"></div>
<script type="module">
  const { Calendar } = await import('https://cdn.jsdelivr.net/npm/fullcalendar@7.0.0-rc.3/+esm');
  const daygrid = (await import('https://cdn.jsdelivr.net/npm/fullcalendar@7.0.0-rc.3/daygrid/+esm')).default;
  new Calendar(document.getElementById('cal'), { plugins: [daygrid], initialView: 'dayGridMonth' }).render();
</script>
```

Result (Chrome):

```
TypeError: Class constructor component cannot be invoked without 'new'
    at Tn.render (https://cdn.jsdelivr.net/npm/fullcalendar@7.0.0-rc.3/+esm:7:37050)
    at $ (https://cdn.jsdelivr.net/npm/preact@10.29.1/+esm:7:6113)   ← preact diff
    at W (https://cdn.jsdelivr.net/npm/preact@10.29.1/+esm …)
    …
```

### It works with the raw ESM

Same version, same code, but mapping the bare specifiers to the **raw published files** instead of `/+esm` renders correctly:

```
fullcalendar          → https://cdn.jsdelivr.net/npm/fullcalendar@7.0.0-rc.3/index.js
fullcalendar/daygrid  → https://cdn.jsdelivr.net/npm/fullcalendar@7.0.0-rc.3/daygrid.js
preact                → https://cdn.jsdelivr.net/npm/preact@10.29.1/dist/preact.mjs
preact/compat         → https://cdn.jsdelivr.net/npm/preact@10.19.5/compat/dist/compat.mjs
…(preact/hooks, jsx-runtime, compat/client, temporal-polyfill/fns/*, @full-ui/headless-calendar)
```

### What I ruled out

- **Not preact duplication.** At a single version, `(await import('…/preact@10.29.1/+esm')).Component === (await import('…/preact@10.29.1/compat/+esm')).Component` is `true`, and `fullcalendar@7.0.0-rc.3/+esm` pins all its preact imports consistently to `@10.29.1`.
- **Not an ES5 downlevel** of FullCalendar's source — the `+esm` output uses native `class … extends …`.

The crash is at FullCalendar's `Tn.render`, which dispatches a generator with `typeof r === "function" ? r(props, h) : r`; under the `+esm` build a preact `Component` **class** ends up on that branch and is called without `new`. The raw build doesn't hit this. So something in how the `+esm` pipeline bundles/links FullCalendar's preact class components differs from the raw output in a way that breaks class dispatch.

Also reproduces with FullCalendar **v6** via `/+esm` (same error). Happy to provide more detail or a hosted repro. Upstream context: fullcalendar/fullcalendar#7474, and a Symfony AssetMapper issue (its jsDelivr resolver uses `/+esm`).
