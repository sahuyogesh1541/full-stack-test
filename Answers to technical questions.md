# Answers to technical questions

## How long did you spend on the coding test? What would you add to your solution if you had more time?

I spent approximately **4–5 hours** on this test.

**What I would add with more time:**

- **Slide CRUD in the admin panel** — the current admin manages tabs only. I would add a per-tab slide editor with fields for eyebrow text, title, and image upload, including server-side validation and image resizing via GD.
- **Drag-to-reorder** — tabs and slides should support drag-and-drop reordering (SortableJS), persisting `sort_order` via PATCH requests.
- **Image upload pipeline** — rather than relying on file paths, a proper upload endpoint would accept images, validate MIME type and size, store them in an `uploads/` directory, and return a path.
- **Authentication** — the admin panel should sit behind session-based auth before going to production.
- **Unit and integration tests** — PHPUnit covering the API endpoints, and Cypress for the tab-switching and slider sync behaviours.
- **Error and empty states** — graceful UI when the API fails or a tab has no slides.
- **Accessibility** — full keyboard navigation for tabs (arrow keys per ARIA tabs pattern) and focus management on accordion open/close.

---

## How would you track down a performance issue in production? Have you ever had to do this?

Yes — the most common scenario I've encountered is a page that performs fine under low traffic but degrades under load.

**My approach:**

1. **Reproduce and scope** — check server monitoring (New Relic, Datadog, or PHP slow logs) to confirm whether the bottleneck is server CPU, database query time, network, or client-side rendering.

2. **Database first** — most PHP performance issues are SQL-related. I run `EXPLAIN` on slow queries, look for missing indexes, and eliminate N+1 patterns (e.g. loading all tabs then issuing a separate `SELECT` per tab inside a loop).

3. **Profiling** — Xdebug + KCachegrind gives a call-graph showing where time is actually spent. On the frontend, Chrome DevTools Performance and Lighthouse identify render-blocking resources and long tasks.

4. **Caching** — once the slow path is identified, introduce the right cache: query-level (Redis/Memcached), HTTP cache headers (`Cache-Control`, `ETag`), or a CDN for public pages.

5. **Load test the fix** — verify the improvement holds under simulated traffic (k6, Apache Bench) before deploying to production.

In one past project, a site was timing out under moderate traffic. The cause was a plugin issuing 60+ uncached external API calls per page load. Replacing it with a cron-driven local cache brought response time from ~8 s down to under 400 ms.

---

## Please describe yourself using JSON

```json
{
  "name": "Yogesh Sahu",
  "role": "Full Stack Developer",
  "experience_years": 2.5,
  "stack": {
    "backend":  ["PHP", "MySQL", "Node.js", "REST APIs"],
    "frontend": ["HTML5", "CSS3", "JavaScript", "jQuery", "React.js", "Vue.js"],
    "tools":    ["Git", "npm", "Linux CLI", "phpMyAdmin"]
  },
  "traits": {
    "detail_oriented": true,
    "self_directed":   true,
    "writes_tests":    true,
    "reads_docs":      "always"
  },
  "currently_learning": ["TypeScript", "Next.js", "AWS"],
  "values": [
    "readable code over clever code",
    "performance is a feature",
    "design and engineering are the same discipline"
  ],
  "available": true
}
```
