# STRIDE Threat Model
## Career Test & Guidance Platform with Integrated Cybersecurity Framework

**Author:** Jo'rayev Azizbek  
**Student Number:** 2427253  
**Module:** 6CS007/ZB1 — Project and Professionalism  
**Date:** April 2026  
**Version:** 1.0

---

## 1. Introduction

This document presents a STRIDE threat model for the Career Test & Guidance Platform. STRIDE is a threat modelling methodology developed by Microsoft that classifies threats into six categories: **S**poofing, **T**ampering, **R**epudiation, **I**nformation Disclosure, **D**enial of Service, and **E**levation of Privilege. Each identified threat is mapped to its mitigation controls implemented in the system.

---

## 2. System Overview

The platform is a server-rendered Laravel 12 web application that allows students to register, take career assessment tests, and receive career guidance recommendations. An admin role manages tests and questions. All logic executes server-side; the frontend uses Blade templates with Tailwind CSS and Alpine.js.

### 2.1 Key Components

| ID  | Component              | Description |
|-----|------------------------|-------------|
| C1  | Browser (Client)       | User's web browser — untrusted boundary |
| C2  | Laravel Web Server     | PHP application handling all HTTP requests |
| C3  | Authentication System  | Two-step login: email/password + secret word 2FA |
| C4  | Career Assessment Engine | Test flow, answer submission, scoring |
| C5  | Career Matching Service | Server-side scoring algorithm (`CareerMatchingService`) |
| C6  | Admin Console          | CRUD management for tests and questions |
| C7  | Audit Log System       | Records security-relevant events to `audit_logs` table |
| C8  | PostgreSQL Database    | Persistent storage for all application data |
| C9  | Session Store          | Database-backed Laravel session |

### 2.2 Trust Boundaries

```
┌─────────────────────────────────────────────────────────────────┐
│  UNTRUSTED ZONE (Internet)                                       │
│                                                                  │
│   [Browser / Client C1]                                          │
│         │  HTTPS (TLS)                                           │
└─────────┼───────────────────────────────────────────────────────┘
          │  ◄── Trust Boundary 1: Public Internet → Web Server
┌─────────┼───────────────────────────────────────────────────────┐
│  TRUSTED ZONE (Application Server)                              │
│                                                                  │
│   [Laravel Web Server C2]                                        │
│         ├── [Auth System C3]                                     │
│         ├── [Assessment Engine C4] → [Matching Service C5]      │
│         ├── [Admin Console C6]                                   │
│         └── [Audit Logger C7]                                    │
│               │  ◄── Trust Boundary 2: App → Database           │
│         [PostgreSQL Database C8]                                 │
│         [Session Store C9]                                       │
└─────────────────────────────────────────────────────────────────┘
```

### 2.3 Data Flows

| ID  | Data Flow                                          |
|-----|----------------------------------------------------|
| DF1 | User submits login credentials (C1 → C2 → C3)     |
| DF2 | Session token issued after 2FA (C3 → C9 → C1)     |
| DF3 | Student submits test answers (C1 → C2 → C4 → C8)  |
| DF4 | Matching algorithm reads answers (C8 → C5 → C8)   |
| DF5 | Results served to student browser (C8 → C2 → C1)  |
| DF6 | Admin performs CRUD operations (C1 → C6 → C8)     |
| DF7 | Audit events written to database (C7 → C8)         |

---

## 3. STRIDE Threat Analysis

### 3.1 SPOOFING — Pretending to be someone else

| ID    | Threat | Component | Likelihood | Impact |
|-------|--------|-----------|------------|--------|
| S-01  | Attacker uses stolen credentials to log in as a student | C3 — Auth System | Medium | High |
| S-02  | Attacker bypasses step 1 and directly accesses the secret word form | C3 — Auth System | Low | High |
| S-03  | Attacker forges a session cookie to impersonate another user | C9 — Session Store | Low | Critical |
| S-04  | Attacker registers with another student's email to claim their identity | C3 — Registration | Low | Medium |

**Mitigations Implemented:**

| Threat | Mitigation | Location in Code |
|--------|-----------|------------------|
| S-01 | Two-factor authentication (secret word, step 2) | `AuthController@verifySecretWord` |
| S-01 | Rate limiting: 5 attempts/min keyed by email + IP | `AppServiceProvider` — `throttle:login` |
| S-02 | Session guard: secret word form only accessible if `2fa_user_id` exists in session | `AuthController@showSecretWordForm` |
| S-03 | Sessions stored server-side in database; cookie contains only opaque session ID | `SESSION_DRIVER=database` in `.env` |
| S-03 | `session()->regenerate()` called on successful login to prevent session fixation | `AuthController@verifySecretWord` |
| S-04 | Email uniqueness enforced at registration | `unique:users` validation rule |

---

### 3.2 TAMPERING — Modifying data without authorisation

| ID    | Threat | Component | Likelihood | Impact |
|-------|--------|-----------|------------|--------|
| T-01  | Student manipulates POST body to submit inflated scores for scale questions | C4 — Assessment Engine | Medium | High |
| T-02  | Student tampers with `question_id` in answer submission to answer out-of-sequence | C4 — Assessment Engine | Medium | Medium |
| T-03  | Student modifies `test_attempt_id` in URL to submit answers into another user's attempt | C4 — Assessment Engine | Medium | High |
| T-04  | Attacker intercepts HTTP traffic and modifies answer data in transit | DF3 — Client to Server | Low | High |
| T-05  | Admin injects malicious JSON into `career_weights` field on a question | C6 — Admin Console | Low | Medium |

**Mitigations Implemented:**

| Threat | Mitigation | Location in Code |
|--------|-----------|------------------|
| T-01 | Score from client is only accepted for scale questions; all other scores computed server-side by `CareerMatchingService` | `TestController@submitAnswer`, `CareerMatchingService` |
| T-02 | Answers are appended sequentially; question index derived from `$testAttempt->answers()->count()` — client cannot choose which question | `TestController@take` |
| T-03 | Ownership check: `$testAttempt->user_id !== Auth::id()` aborts with 403 | `TestController@submitAnswer` |
| T-04 | HTTPS/TLS encrypts all data in transit (enforced in production via `APP_URL=https://`) | Server configuration |
| T-05 | Admin access restricted to users with `role = admin` via `EnsureUserIsAdmin` middleware | `app/Http/Middleware/EnsureUserIsAdmin.php` |

---

### 3.3 REPUDIATION — Denying that an action was performed

| ID    | Threat | Component | Likelihood | Impact |
|-------|--------|-----------|------------|--------|
| R-01  | Admin denies deleting a career test or question | C6 — Admin Console | Medium | Medium |
| R-02  | User denies having logged in from a specific IP (e.g. in a security incident) | C3 — Auth System | Medium | Medium |
| R-03  | User denies completing a test or submitting specific answers | C4 — Assessment Engine | Low | Medium |

**Mitigations Implemented:**

| Threat | Mitigation | Location in Code |
|--------|-----------|------------------|
| R-01 | All admin CRUD operations (create/update/delete test and question) are written to `audit_logs` with user ID, target, and timestamp | `AdminController`, `AuditLogger` |
| R-02 | `login.success`, `login.failed`, and `logout` events recorded with IP address and user agent | `AuthController`, `AuditLogger` |
| R-03 | `test.completed` event logged; `TestAttempt` stores `started_at` and `completed_at` timestamps; individual `Answer` records are immutable once written | `TestController`, `audit_logs` table |

---

### 3.4 INFORMATION DISCLOSURE — Exposing data to unauthorised parties

| ID    | Threat | Component | Likelihood | Impact |
|-------|--------|-----------|------------|--------|
| I-01  | Student accesses another student's career results by guessing a URL | C4/C5 — Results | Medium | High |
| I-02  | Unauthenticated user accesses dashboard or test routes directly via URL | C2 — Routing | Medium | High |
| I-03  | Student accesses admin routes by guessing `/admin/*` URLs | C6 — Admin Console | Medium | Critical |
| I-04  | Sensitive data (passwords, secret words) stored in plain text in database | C8 — Database | Low | Critical |
| I-05  | Stack traces or debug information exposed to users in production | C2 — Laravel | Low | Medium |
| I-06  | Career assessment answers visible in browser history or server logs | DF3 | Low | Medium |

**Mitigations Implemented:**

| Threat | Mitigation | Location in Code |
|--------|-----------|------------------|
| I-01 | Ownership check on `TestAttempt` and `CareerResult`; `Auth::id()` compared to record's `user_id` | `ResultController`, `TestController` |
| I-02 | All student routes wrapped in `middleware('auth')` group | `routes/web.php` |
| I-03 | Admin routes require both `auth` and `admin` middleware; returns 403 for non-admins | `EnsureUserIsAdmin`, `routes/web.php` |
| I-04 | Passwords and secret words hashed with bcrypt (`BCRYPT_ROUNDS=12`) — never stored in plain text | `AuthController@register` |
| I-05 | `APP_DEBUG=false` in production `.env` suppresses stack traces | `.env` |
| I-06 | Answers submitted via HTTP POST (not GET) — not stored in browser URL history | `routes/web.php` — `Route::post` |

---

### 3.5 DENIAL OF SERVICE — Making the system unavailable

| ID    | Threat | Component | Likelihood | Impact |
|-------|--------|-----------|------------|--------|
| D-01  | Brute-force attack on login endpoint floods the server | C3 — Auth System | High | High |
| D-02  | Brute-force attack on secret word (2FA step 2) | C3 — Auth System | High | High |
| D-03  | Attacker creates thousands of user accounts (registration flood) | C3 — Registration | Medium | Medium |
| D-04  | Attacker starts thousands of test attempts, filling the database | C4 — Assessment Engine | Low | Medium |

**Mitigations Implemented:**

| Threat | Mitigation | Location in Code |
|--------|-----------|------------------|
| D-01 | Named rate limiter `throttle:login` — 5 attempts/min per email+IP | `AppServiceProvider`, `routes/web.php` |
| D-02 | Named rate limiter `throttle:secret-word` — 5 attempts/min per session user ID+IP | `AppServiceProvider`, `routes/web.php` |
| D-03 | Registration requires valid unique email; bcrypt cost factor 12 makes mass registration computationally expensive | `AuthController@register` |
| D-04 | Test start requires authentication; existing in-progress attempts are reused rather than duplicated | `TestController@start` |

**Residual Risk:**
- D-03 and D-04 would benefit from CAPTCHA on the registration form — not yet implemented.
- Infrastructure-level DDoS protection (e.g. Cloudflare) is outside application scope but recommended for production.

---

### 3.6 ELEVATION OF PRIVILEGE — Gaining permissions beyond what is authorised

| ID    | Threat | Component | Likelihood | Impact |
|-------|--------|-----------|------------|--------|
| E-01  | Student accesses admin functionality by directly requesting `/admin/*` URLs | C6 — Admin Console | Medium | Critical |
| E-02  | Student manipulates their own `role` field via profile update | C2 — Profile | Low | Critical |
| E-03  | Unauthenticated user completes a test by manipulating session state | C4 — Assessment Engine | Low | High |
| E-04  | SQL injection allows attacker to modify their own role in the database | C8 — Database | Low | Critical |

**Mitigations Implemented:**

| Threat | Mitigation | Location in Code |
|--------|-----------|------------------|
| E-01 | `EnsureUserIsAdmin` middleware applied to entire `/admin` route group; returns 403 | `app/Http/Middleware/EnsureUserIsAdmin.php` |
| E-02 | `role` field is not exposed in profile update form or validation rules; mass-assignment protected by `$fillable` on `User` model | `ProfileController`, `User` model |
| E-03 | All test routes protected by `middleware('auth')`; test attempt ownership verified on every request | `routes/web.php`, `TestController` |
| E-04 | All database queries use Laravel Eloquent ORM with parameterised bindings — raw SQL not used | Throughout controllers |

---

## 4. Threat Summary Matrix

| ID    | Category | Threat (short) | Likelihood | Impact | Status |
|-------|----------|----------------|------------|--------|--------|
| S-01  | Spoofing | Credential theft login | Medium | High | ✅ Mitigated |
| S-02  | Spoofing | 2FA step bypass | Low | High | ✅ Mitigated |
| S-03  | Spoofing | Session cookie forgery | Low | Critical | ✅ Mitigated |
| S-04  | Spoofing | Email claim at registration | Low | Medium | ✅ Mitigated |
| T-01  | Tampering | Score inflation via POST | Medium | High | ✅ Mitigated |
| T-02  | Tampering | Out-of-sequence answers | Medium | Medium | ✅ Mitigated |
| T-03  | Tampering | Cross-user answer injection | Medium | High | ✅ Mitigated |
| T-04  | Tampering | Traffic interception | Low | High | ✅ Mitigated |
| T-05  | Tampering | Malicious admin input | Low | Medium | ✅ Mitigated |
| R-01  | Repudiation | Admin denies CRUD action | Medium | Medium | ✅ Mitigated |
| R-02  | Repudiation | User denies login | Medium | Medium | ✅ Mitigated |
| R-03  | Repudiation | User denies test completion | Low | Medium | ✅ Mitigated |
| I-01  | Info Disclosure | Cross-user result access | Medium | High | ✅ Mitigated |
| I-02  | Info Disclosure | Unauthenticated route access | Medium | High | ✅ Mitigated |
| I-03  | Info Disclosure | Student accesses admin routes | Medium | Critical | ✅ Mitigated |
| I-04  | Info Disclosure | Plain-text passwords in DB | Low | Critical | ✅ Mitigated |
| I-05  | Info Disclosure | Debug info in production | Low | Medium | ✅ Mitigated |
| I-06  | Info Disclosure | Answers in browser history | Low | Medium | ✅ Mitigated |
| D-01  | Denial of Service | Login brute-force | High | High | ✅ Mitigated |
| D-02  | Denial of Service | 2FA brute-force | High | High | ✅ Mitigated |
| D-03  | Denial of Service | Registration flood | Medium | Medium | ⚠️ Partial |
| D-04  | Denial of Service | Test attempt flood | Low | Medium | ⚠️ Partial |
| E-01  | Privilege Escalation | Student → admin access | Medium | Critical | ✅ Mitigated |
| E-02  | Privilege Escalation | Self role modification | Low | Critical | ✅ Mitigated |
| E-03  | Privilege Escalation | Unauthenticated test | Low | High | ✅ Mitigated |
| E-04  | Privilege Escalation | SQL injection role change | Low | Critical | ✅ Mitigated |

**Legend:** ✅ Mitigated — control implemented | ⚠️ Partial — residual risk remains

---

## 5. Residual Risks and Recommendations

| Risk | Recommendation | Priority |
|------|---------------|----------|
| Registration/test flood (D-03, D-04) | Add CAPTCHA (e.g. hCaptcha) to registration form | Medium |
| No infrastructure-level DDoS protection | Deploy behind Cloudflare or similar CDN in production | Medium |
| No CAPTCHA on login | Consider CAPTCHA after 3 failed attempts | Low |
| Backup & recovery not automated | Schedule automated encrypted PostgreSQL backups | High |

---

## 6. Mapping to Security Frameworks

| STRIDE Category | OWASP Top 10 (2021) | NIST CSF Function | ISO 27001 Clause |
|----------------|---------------------|-------------------|-----------------|
| Spoofing | A07: Identification & Authentication Failures | Protect | A.9 Access Control |
| Tampering | A03: Injection, A04: Insecure Design | Protect | A.14 Secure Development |
| Repudiation | A09: Security Logging & Monitoring Failures | Detect | A.12 Operations Security |
| Information Disclosure | A01: Broken Access Control | Protect | A.9, A.10 Cryptography |
| Denial of Service | A04: Insecure Design | Protect | A.12 Operations Security |
| Elevation of Privilege | A01: Broken Access Control | Protect | A.9 Access Control |

---

*This document was produced as part of the secure development lifecycle for the Career Test & Guidance Platform. All identified mitigations are implemented in the application codebase and verifiable via code review.*