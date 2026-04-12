# Catalog Pages — Performance Checklist

- [ ] Product list uses server pagination (`per_page`, `page`) — no unbounded client fetches
- [ ] Search input debounced (≥ 300ms) before triggering list reload
- [ ] Category tree flattened once per load for grid display; avoid deep repeated traversals in template
