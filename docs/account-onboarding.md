# Account onboarding and welcome email

New Google and password registrations are marked as awaiting a welcome email. Google confirms the email address during OAuth; password registrations retain the existing email verification flow.

Before participating, new users save their country, gender (including “Prefer not to say”), and an explicit preferred language from the active site languages. The existing `users.locale` stores this preference for subsequent communications; `preferred_language_confirmed_at` records the choice. The language selector does not silently accept the OAuth page language for a new account.

After a verified user saves a complete profile, `SendWelcomeEmail` is queued. It uses the saved preferred language at delivery time, retries transient SMTP failures for up to seven days, and defers while SMTP or the provider quota is unavailable. A unique queue job and user row lock prevent repeated profile saves from generating repeated welcome emails. `welcome_email_sent_at` records acceptance by SMTP, not inbox delivery. SMTP delivery is at least once: a worker crash between provider acceptance and the database commit can still cause a retry.

Existing accounts are not automatically enrolled; there is no welcome bulk send and no change to optional product email consent. A permanently failed welcome job remains visible in `failed_jobs` and can be retried after the underlying error is fixed.

Deploy through the standard GitHub pipeline so the additive users-table migration runs before the new release is activated. No credentials are added to source control.
