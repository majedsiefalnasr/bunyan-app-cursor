# Performance Checklist — Analytics

- [ ] Aggregations are precomputed (jobs/schedule), not computed in-request
- [ ] Redis cache keys/TTLs defined per metric + bucket + date range
- [ ] Cache freshness target met (~5 minutes) without stampedes
- [ ] DB queries use proper indexes (time bucket + metric + org/project where applicable)
- [ ] Avoid N+1 and select only needed columns
