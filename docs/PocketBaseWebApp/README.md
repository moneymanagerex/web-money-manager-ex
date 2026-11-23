# MMEX Web + PocketBase

This static frontend requires a reachable PocketBase instance. The default URL
is `https://ab0yt2hi73hitsy.pwz5.pocketbasecloud.com`, with `demo@mmex.org` as
the default email. Enter the password yourself, or change the URL and sign in
to your own instance. Remote instances must use HTTPS. The URL is saved in
local storage. The login token is kept in session storage by default; ticking
**Remember me on this device** stores it in local storage instead, so only use
that option on a private device. Signing out removes both.

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
When updating modules, change the relevant version queries in the HTML and
module imports so cached older scripts are not reused. After
publishing, a hard refresh may be needed if the HTML itself is still cached.

The canonical collection definitions and seed data live in the
[database repository](https://github.com/moneymanagerex/database/tree/master/pocketbase).
For development, check out that repository as `../database` and run
`sh scripts/sync-pocketbase-schema.sh` from this repository's root. Commit the
resulting bundled files so the static frontend remains self-contained.
`sh scripts/sync-pocketbase-schema.sh --check` detects drift. Set
`DATABASE_ROOT` if the checkout is elsewhere. End users need no extra step.

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
all other clients. Enter your superuser credentials and select **Inspect reset
scope** first; the delete button remains disabled until inspection succeeds and
finds MMEX collections. Review the collection names and record counts, then
re-enter the superuser password, confirm the backup and type the exact reset
phrase displayed on the page. The default-data checkbox controls whether
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
creates version `21` for a fresh schema. An administrator can run the same
setup on an existing unversioned or legacy PocketBase v1 instance: it checks
expected collection names and field types before marking it `21`, without
recreating collections or changing business records. Back up the instance and
stop other clients first; older WebApp versions will not accept version `21`.
The check does not verify indexes, rules, or semantic compatibility, which an
administrator must review. Version `21` denotes the shared schema baseline,
not proof that SQLite v20/v21 data-repair scripts were applied. The frontend
refuses to open an unversioned or unsupported version until setup is run.

PocketBase's built-in `_migrations` tracks server-side migration files, not
remote API imports. `dbInfo` remains necessary for this self-contained
frontend to check the MMEX schema version on remote instances.

### PocketBase audit fields

Besides the mirrored MMEX columns, every collection carries PocketBase's system
`created` and `updated` autodate fields. PocketBase sets them on the server, so
clients cannot forge them, and `updated` is the natural change cursor for
future incremental synchronization. Setup adds these fields to instances built
from an older baseline, verifies the result, and leaves business data and the
`21` marker untouched; the repair is idempotent. Records that predate the
fields keep empty timestamps — they are not back-filled, because a synthetic
value would misreport when the data actually changed.

Apart from the legacy v1 version-marker transition, there are no automatic
migrations yet. For a future version, define an
explicit, ordered migration from each supported prior version, validate its
preconditions, preserve existing data and IDs, test on a restored backup, and
update the version **only after** successful migration and verification.
Coordinate downtime or disable older clients during migration: client-side
version checks are not a server-enforced migration lock. Back up the instance,
including its collection definitions, first; design and test recovery for
partial failures. Never import the latest schema over an existing instance
as a shortcut. `dbInfo` is currently writable by authenticated users, so its
version is a compatibility marker, not a security or concurrency guarantee.

## Schema compatibility choices

The PocketBase collections keep MMEX's integer business keys (`ACCOUNTID`,
`TRANSID`, `CATEGID`, …) instead of PocketBase `relation` fields. Relation
fields store PocketBase record IDs, so replacing these columns would break the
shared v21 schema used by MMEX desktop and other clients and would require
writing both identities everywhere. The frontend resolves links from the loaded
records instead. PocketBase therefore does not enforce these references on the
server; guarded deletes are client-side checks, as described below.

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

Every signed-in section uses the same layout: a short description, search or
filters, an optional editor card, then the list with its own record count and a
**Refresh data** button. Empty search results are reported instead of leaving a
blank table.

The app supports account creation/editing and guarded deletion; category creation/editing, parent assignment and activation; creating and
editing withdrawals, deposits and transfers; duplicating an existing
transaction into a new one; soft-deleting and restoring transactions; visually distinguishing deposits, withdrawals and transfers; transfers remain one row with source/destination accounts. In the all-account view a transfer displays the MMEX-style payee `> destination account` and a negative source amount; when filtered to the destination account it displays `< source account` and the positive received amount; filtering
by account, date and type with one-click filter reset; searching categories and accounts; creating/editing payees, setting default categories and activating or deactivating payees; and
managing currencies. Choosing a payee pre-fills its default category when that
category exists, but does not clear a category you chose yourself. Same-currency
transfers default to matching amounts; enter the received amount for
cross-currency transfers. Split and asset/stock-linked transactions are
read-only to avoid corrupting related records. The app loads all pages of
displayed collections and transaction links.

The base currency is shown in the overview and currencies sections, taken from
`INFOTABLE_V1` (`INFONAME = "BASECURRENCYID"`). When it is not set yet, the
currencies list offers **Set base**, which records the choice without touching
conversion rates; an invalid or dangling stored value is repaired in place. Changing an
existing base currency is not offered: the desktop app also resets stored
conversion rates and deletes currency history when the base currency changes,
and this frontend cannot reproduce that safely yet — use MMEX desktop instead.
Currencies referenced by an account, asset, currency history or the
base-currency setting cannot be deleted. Duplicate or ambiguous
`BASECURRENCYID` rows block the in-app action and must be fixed in PocketBase.

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
