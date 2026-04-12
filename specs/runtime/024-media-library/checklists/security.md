# Security — Media Library

- [ ] Upload MIME and extension validated against allowlist
- [ ] Path traversal prevented; stored names opaque (UUID segments)
- [ ] Authorization on every mutating route (`MediaPolicy`)
- [ ] Rate limiting on `POST /api/v1/media/upload`
- [ ] Mediable morph allowlist prevents arbitrary class instantiation
- [ ] No secrets or raw file paths leaked in error responses
