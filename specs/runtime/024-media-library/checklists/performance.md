# Performance — Media Library

- [ ] Media index paginated; indexed filters (`uploaded_by`, `collection`)
- [ ] Eager loading avoided where single-model; no N+1 on list
- [ ] Thumbnail generation does not block response unreasonably (sync acceptable for MVP sizes)
