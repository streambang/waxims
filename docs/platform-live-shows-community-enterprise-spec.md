# PLATFORM LIVE SHOWS + COMMUNITY (Enterprise SaaS)
## showup.tv / chaturbate-like, but better

**Wersja:** 1.0 Enterprise  
**Data:** 2026-03-02  
**Docelowy builder:** Manus / Base44  
**Język:** Polski (primary), English (secondary)  
**Architektura:** Mikroserwisy (DDD + Event-Driven + CQRS where useful)  
**Tryb wdrożenia:** SaaS multi-tenant + white-label + enterprise compliance  
**Uwaga:** dokument nie definiuje schematu bazy danych (DB designed by builder).

---

## 0) Product Goal (Enterprise Positioning)

Zbudować platformę live streaming + community + monetization do:
- transmisji na żywo: 1:1, 1:N, private/public rooms,
- interakcji: chat, reactions, tips, goals, polls,
- monetyzacji: tokens, subscriptions, PPV, paid private shows,
- obsługi twórców i wypłat: payouts, commissions, invoices,
- trust & safety / compliance: age gating, creator verification, moderation, audit.

Produkt ma być oferowany jako **Enterprise SaaS** z możliwością white-label i konfiguracji per tenant.

---

## 1) Streaming Providers API Strategy (No Vendor Lock-in)

### 1.1 Streaming Provider Interface (SPI)

Wymagany adapter layer dla dostawców streamingu:
- `create_stream_session`
- `get_ingest_endpoint`
- `get_playback_url`
- `rotate_stream_key`
- `start_recording`
- `stop_recording`
- `get_metrics`
- `end_stream_session`

Webhook/event contract:
- `stream.started`
- `stream.stopped`
- `recording.ready`
- `health.degraded`

### 1.2 Recommended Providers (Enterprise)

1. **Amazon IVS**: managed low-latency live streaming.
2. **Mux Live**: API-first workflow + LL-HLS delivery.
3. **Ant Media Server**: self-hosted WebRTC/RTMP/HLS.
4. **Optional**: Agora for RTC-heavy scenarios.

### 1.3 Streaming Deployment Profiles

- **Public Rooms (1→N):** RTMP/RTMPS ingest → LL-HLS playback.
- **Private 1:1 / Small Group:** WebRTC (ultra-low latency).
- **Fallback:** standard HLS.
- **Recording:** policy-driven (tenant + compliance).
- **CDN Security:** signed URLs, geo restrictions, tokenized playback.

---

## 2) Monetization (Token-first)

### 2.1 Token Economy
- token packages + promotions,
- tips and goal systems,
- PPV unlocks,
- paid private shows,
- paid messages,
- marketplace (badges/emotes/highlight messages).

### 2.2 Additional Revenue Models
- channel subscriptions,
- membership tiers,
- sponsorship/brand integrations,
- optional ad module per tenant policy,
- marketplace commissions.

### 2.3 Creator Payouts
- earnings balance + ledger history,
- payout schedules (weekly/monthly),
- threshold controls,
- KYC/KYB gate,
- chargeback/risk holds,
- optional tax reporting by jurisdiction.

---

## 3) Core Product Modules

### 3.1 Live Rooms / Shows
- public/private/group sessions,
- metadata: title, tags, pricing, language,
- live chat + moderation,
- reactions, gifts, tip menu,
- goals/counters/alerts,
- optional multi-cam + screen share,
- OBS-ready stream key + ingest instructions.

### 3.2 Community
- profiles + follow system,
- activity feed,
- posts/comments/reactions,
- direct messaging (optional paid DM),
- optional clubs/groups tied to creators.

### 3.3 Discovery & Search
- categories/tags,
- ranking: live now, trending, rising creators,
- full-text and faceted search,
- filters: language, region, type, pricing, popularity.

### 3.4 Moderation & Creator Tools
- mute/ban/keyword filters,
- room-level moderator roles,
- report queue + appeals,
- auto moderation: spam/scam/rapid-fire/link reputation.

### 3.5 Payments Security
- SCA/3DS support,
- anti-fraud scoring,
- transactional limits,
- signed webhook verification,
- reconciliation jobs.

---

## 4) Reliability & Error-Prevention Requirements

### 4.1 Contracts
- OpenAPI 3.1 for each service,
- contract testing for APIs,
- CloudEvents + JSON Schema for events,
- idempotency keys (financial ops + consumers),
- outbox pattern,
- DLQ + retry/backoff + poison handling.

### 4.2 SLO/SLA Targets
- API latency: P95 ≤ 250ms, P99 ≤ 900ms,
- chat delivery P95 ≤ 400ms,
- stream TTFF ≤ 2.5s,
- availability: 99.9% standard / 99.95% enterprise,
- RPO 15m, RTO 2h (enterprise baseline).

### 4.3 Observability
- OpenTelemetry traces/metrics/logs in all services,
- per-tenant dashboards,
- alerting on stream success, queue lag, payment failures.

### 4.4 Safe Rollouts
- canary / blue-green,
- kill-switch for billing/chat/payouts,
- backward-compatible migrations.

---

## 5) High-Level Architecture

1. Web App + optional Mobile Apps
2. API Gateway (auth/tenant/rate-limit/routing)
3. Domain Microservices
4. Event Broker (Kafka/RabbitMQ/NATS)
5. Redis (cache + rate limit)
6. Search (OpenSearch/Elasticsearch)
7. Object storage (S3) + CDN
8. Streaming provider adapters (IVS/Mux/Ant/Agora)
9. Observability stack (Prometheus/Grafana/Loki/Tempo)

Design principles:
- `tenant_id` present in every request/event,
- zero trust/mTLS service-to-service,
- RBAC/ABAC,
- encryption at rest + PII partitioning.

---

## 6) Bounded Contexts (DDD)

1. Identity & Access
2. Tenancy & White-label
3. Creators
4. Live Shows
5. Streaming Orchestration
6. Chat & Realtime
7. Tokens & Wallet
8. Payments
9. Payouts
10. Community & Social
11. Discovery & Search
12. Moderation & Trust
13. Content Safety
14. Analytics & BI
15. Notifications
16. Audit & Compliance
17. Integrations

---

## 7) Proposed Microservices

1. Gateway Service
2. Identity Service
3. Tenant Service
4. Creator Service
5. LiveShow Service
6. Streaming Orchestrator Service
7. Chat/Realtimes Service
8. Wallet/Token Service
9. Payments Service
10. Payouts Service
11. Social Service
12. Messaging Service
13. Search/Discovery Service
14. Moderation Service
15. Trust & Safety Service
16. Analytics Service
17. Notifications Service
18. Audit/Compliance Service
19. Admin Console Service

> Transitional option: modular monolith with strict contracts to allow later extraction.

---

## 8) Streaming Orchestrator Contract (SPI)

### Required operations
- `CreateStreamSession(tenant_id, creator_id, room_id, profile)`
- `RotateKey(session_id)`
- `GetPlayback(session_id, viewer_context)`
- `StartRecording(session_id)`
- `StopRecording(session_id)`
- `GetHealth(session_id)`
- `EndSession(session_id)`

### Quality profiles
- Standard: HLS 1080p
- Low-latency: LL-HLS / IVS low-latency
- RTC: WebRTC
- Enterprise policies: Geo/DRM/token gating

---

## 9) Tokens & Wallet (Ledger-first)

### Rules
- append-only transaction ledger,
- wallet balance derived from ledger,
- idempotency for purchase/tips,
- anti-fraud checks (limits + velocity + optional device fingerprint).

### Transaction types
- `purchase`
- `tip`
- `ppv_unlock`
- `subscription_benefit`
- `refund_or_chargeback_adjustment`
- `payout_conversion`

---

## 10) Trust & Safety (Mandatory)

### 10.1 Verification & age-gating
- user age-gating (jurisdiction + tenant policy),
- creator verification (identity + consent + legal agreement),
- strict policy against illegal content.

### 10.2 Moderation
- reporting for live/DM/posts,
- real-time moderation tools,
- abuse pattern detection,
- panic button + escalation channel.

### 10.3 Audit & enforcement
- immutable moderation logs,
- appeal workflow,
- retention + legal hold support.

---

## 11) API Surface (REST + OpenAPI 3.1 Examples)

### Live
- `POST /v1/rooms`
- `POST /v1/rooms/{roomId}/start`
- `GET /v1/rooms/{roomId}/playback`
- `POST /v1/rooms/{roomId}/end`

### Chat
- `GET /v1/rooms/{roomId}/chat`
- `POST /v1/rooms/{roomId}/chat/message`
- `POST /v1/rooms/{roomId}/chat/moderate`

### Wallet
- `POST /v1/wallet/purchase`
- `POST /v1/wallet/tip`
- `GET /v1/wallet/balance`
- `GET /v1/wallet/ledger?cursor=...`

### Payments/Payouts
- `POST /v1/payments/checkout`
- `POST /v1/payments/webhooks/{provider}`
- `GET /v1/creators/payouts`
- `POST /v1/creators/payouts/request`

### Moderation
- `POST /v1/reports`
- `GET /v1/moderation/queue`
- `POST /v1/moderation/decisions`

API standards:
- RFC 7807 problem+json,
- cursor-based pagination,
- idempotency keys,
- webhook signatures.

---

## 12) Enterprise KPI Set

- stream uptime + success rate,
- peak concurrency + watch time,
- token conversion + ARPPU + chargeback ratio,
- creator retention + earnings,
- moderation SLA (time-to-action),
- QoE metrics: bitrate, rebuffer, latency.

---

## 13) DevOps & Scalability

- Kubernetes + HPA,
- separate node pools (API/realtime/workers/search/observability),
- chaos testing,
- backup + DR (optionally multi-region),
- secrets via Vault/KMS,
- WAF + DDoS + bot management.

---

## 14) Anti-bug Checklist per Service

Every service must include:
- OpenAPI 3.1 + contract tests,
- event schema + outbox + idempotency,
- retries + DLQ monitoring,
- OTel traces/metrics/logs,
- SLO + alerts + runbook,
- security baseline (OWASP ASVS L2 + dependency scan + secret hygiene),
- feature flags + safe rollout,
- load tests for critical endpoints.

Critical focus:
- Wallet/Payments: append-only ledger + reconciliation,
- Streaming: key rotation + playback tokenization + health checks,
- Chat: rate limiting + spam prevention + moderation latency,
- Payouts: KYC gating + fraud holds + immutable audit trail.

---

## 15) Delivery Blueprint for Manus/Base44

### Phase 1 (MVP Enterprise-ready)
- Identity/Tenant/Creator
- LiveShow + Streaming Orchestrator (1 provider + SPI)
- Chat (basic moderation)
- Wallet + Payments checkout + ledger
- Admin Console (tenant configs + feature flags)

### Phase 2
- Payouts + KYC/KYB workflows
- Social feed + DM
- Discovery + recommendations
- Trust & Safety automation

### Phase 3
- Multi-provider streaming failover
- Advanced analytics & BI
- White-label self-service portal
- Enterprise compliance add-ons (audit exports/legal hold)

Definition of done per phase:
- contract tests pass,
- SLO dashboards live,
- runbooks ready,
- security controls validated,
- tenant isolation verified.

---

## 16) EN Appendix (short)

Enterprise-grade live streaming and community SaaS platform with token-based monetization, multi-provider streaming orchestration (IVS/Mux/Ant Media), trust & safety, payouts, and multi-tenant white-label capabilities. Database schema intentionally omitted.
