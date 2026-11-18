# MMEX Web + PocketBase

This static frontend requires a reachable PocketBase instance. The default URL
is `https://ab0yt2hi73hitsy.pwz5.pocketbasecloud.com`, with `demo@mmex.org` as
the default email. Enter the password yourself, or change the URL and sign in
to your own instance. Remote instances must use HTTPS. The URL is saved in
local storage and the authentication token in session storage.

The included access rules provide **one shared dataset**, not per-user
isolation: every authenticated user can read and modify it. Use a private
instance with trusted accounts, HTTPS, appropriate server configuration and
backups. Review its PocketBase rules before exposing it; the default demo
account and shared instance are not suitable for private financial records.
Schema administration requires a superuser: use only a frontend you trust.

## Publishing

The source is `docs/PocketBaseWebApp/`. Commit and push changes under `docs/`.
In repository Settings → Pages, select **Deploy from a branch**, `master`, and
`/docs`. The site root redirects to `PocketBaseWebApp/`; no build or generated
copy is needed.

For self-hosting, serve the contents of `docs/PocketBaseWebApp/` over HTTPS
(or HTTP on localhost). Pages does not run PocketBase; the selected server must be
reachable by the browser and allow requests from the frontend origin.

## Instance setup

The default server must already have the compatible schema and user; this
frontend does not automatically create them there. To set up a fresh instance:

1. Create a PocketBase superuser using the server's administration tools.
2. Select the instance URL and open **Administrator / schema setup**. Sign in
   as superuser and provide a first regular user's email and password if needed.
3. Setup imports the bundled collections when none exist, and optionally
   imports default categories and currencies derived from `tables.sql`. It
   never imports the POC's private sample database. Partial schemas are rejected.
4. Sign in as a regular user. The superuser credentials and token are not saved.

Initialization is not transactional. If an import fails, inspect the partial
instance before retrying. Do not delete records from an instance containing
data you need.

### Resetting an instance

The administrator page can delete the known MMEX collections and recreate the
bundled schema. **This permanently deletes their records**, including the
entire ledger. It does not delete the `users` collection, superusers, or
unrelated collections. First make and verify a full PocketBase backup and stop
all other clients. Inspect the collection names and record counts, then
re-enter the superuser password, confirm the backup and type the exact reset
phrase including the instance URL. The default-data checkbox controls whether
categories and currencies are seeded into the new schema.

Reset rechecks the inspected collection IDs and counts before deleting, and
refuses known relations to MMEX collections or an unsupported schema
version. These checks are not a server-side lock; other clients must remain
stopped. Reset is not transactional: a network or server error can leave
collections deleted or initialization incomplete. Inspect the instance and
restore the backup, or inspect again before another reset. External views,
hooks and integrations may depend on the deleted collections even when no
PocketBase relation is detected; review them before resetting.

## Schema versions and future upgrades

The schema version is stored as `MMEX_SCHEMA_VERSION` in `dbInfo`. Setup
creates version `1` for a fresh schema. For an existing unversioned schema, it
checks the expected collection names and field types before marking it `1`;
this check does not verify indexes, rules, or semantic compatibility, which an
administrator must review. Versioned schemas are never silently relabeled.
The frontend refuses to open an unversioned or unsupported version.

There are no automatic migrations yet. For a future version, define an
explicit, ordered migration from each supported prior version, validate its
preconditions, preserve existing data and IDs, test on a restored backup, and
update the version **only after** successful migration and verification.
Coordinate downtime or disable older clients during migration: client-side
version checks are not a server-enforced migration lock. Back up the instance,
including its collection definitions, first; design and test recovery for
partial failures. Never import the latest schema over an existing instance
as a shortcut. `dbInfo` is currently writable by authenticated users, so its
version is a compatibility marker, not a security or concurrency guarantee.

## Future synchronization

The intended direction is for MMEX desktop, Android and iOS clients to push
and pull against the chosen PocketBase instance using SUID. Synchronization is
not implemented here, and existing MMEX primary keys and PocketBase record IDs
are not assumed to be SUIDs. A shared cross-client contract must first define
SUID generation and scope, mapping of existing records, deletion/tombstones,
incremental change tracking, conflict resolution, and upgrade/backfill rules.
Until that contract and a migration exist, this application must not claim
cross-device synchronization compatibility.

## Current functionality

The app supports account creation/editing and guarded deletion; creating and
editing withdrawals, deposits and transfers; soft-deleting and restoring
transactions; filtering by account, date and type; viewing categories; adding
payees; and managing currencies. Same-currency transfers default to matching
amounts; enter the received amount for cross-currency transfers. Split and
asset/stock-linked transactions are read-only to avoid corrupting related
records. The app loads all pages of displayed collections and transaction links.

Accounts show their current cash balance through the browser's local today:
initial balance plus deposits and incoming transfers, minus withdrawals and
outgoing transfers. Void and deleted transactions and future-dated transactions
are excluded. Balances use the stored decimal amounts and are not a valuation
of investment holdings or assets. Invalid or unsupported transaction data is
reported as unavailable rather than silently omitted. Reconciliation, split
editing, desktop synchronization and `.mmb` export are not implemented.
Amounts are stored as PocketBase numbers; inputs that would lose decimal
precision when converted to a JavaScript number are rejected.

If a transaction save succeeds but reloading fails, the app reports both
outcomes separately. If a network failure leaves a creation unconfirmed, check
the list before retrying; retries reuse the same business ID. This relies on
the server's unique transaction ID index. Concurrent edits and client-side
reference checks are not atomic; configure and operate PocketBase accordingly.
Account deletion checks transactions, scheduled
transactions and stocks, but the client-side check is not atomic. Timestamp
business keys can collide across clients; unique indexes reject collisions.
Currency deletion checks account, asset, currency-history and base-currency
references, and names and codes are checked for duplicates before saving.
These checks are client-side and not atomic; enforce integrity on the server
when multiple clients can write concurrently. Conversion rates are stored
values and are not updated automatically. Wide tables scroll horizontally on
mobile devices.

The ES modules in `src/` separate connection/authentication, schema setup,
shared state and feature views. Run `node --test tests/mmex/frontend.test.js`
from the repository root for focused checks.
