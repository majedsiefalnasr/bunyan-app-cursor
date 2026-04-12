# Catalog Pages — Performance Checklist

- [x] Product list uses server pagination (`per_page`, `page`) — no unbounded client fetches
- [x] Search input debounced (≥ 300ms) before triggering list reload
- [x] Category tree flattened once per load for grid display; avoid deep repeated traversals in template
