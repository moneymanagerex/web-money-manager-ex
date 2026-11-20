const test = require("node:test");
const assert = require("node:assert/strict");

const modulePath = name => `../../docs/PocketBaseWebApp/src/${name}.mjs`;
const json = value => new Response(JSON.stringify(value), {
  headers: { "content-type": "application/json" }
});

test("record loading follows every PocketBase page and keeps sort and filter", async t => {
  const { state } = await import(modulePath("core"));
  const { fetchRecords } = await import(modulePath("api"));
  state.base = "https://example.test";
  state.token = "token";
  const urls = [];
  t.mock.method(globalThis, "fetch", async (url, options) => {
    urls.push(url);
    assert.equal(options.headers.Authorization, "token");
    return json(url.includes("page=1")
      ? { items: [1, 2], totalPages: 2 }
      : { items: [3], totalPages: 2 });
  });
  assert.deepEqual(await fetchRecords("PAYEE_V1", "PAYEENAME", "ACTIVE = 1"), [1, 2, 3]);
  assert.equal(urls.length, 2);
  assert.match(urls[1], /sort=PAYEENAME/);
  assert.match(urls[1], /filter=ACTIVE%20%3D%201/);
  assert.match(urls[1], /page=2/);
});

test("default import checks both collections before writing", async t => {
  const { seedDefaults } = await import(modulePath("seed"));
  const calls = [];
  t.mock.method(globalThis, "fetch", async (url, options = {}) => {
    if (url === "defaults.json") return json({ CATEGORY_V1: [{}], CURRENCYFORMATS_V1: [{}] });
    calls.push(options.method || "GET");
    return json({ totalItems: url.includes("CURRENCYFORMATS_V1") ? 1 : 0 });
  });
  await assert.rejects(seedDefaults("https://example.test", "admin"), /CURRENCYFORMATS_V1 is not empty/);
  assert.deepEqual(calls, ["GET", "GET"]);
});

test("default import reports partial writes", async t => {
  const { seedDefaults } = await import(modulePath("seed"));
  let writes = 0;
  t.mock.method(globalThis, "fetch", async (url, options = {}) => {
    if (url === "defaults.json") return json({ CATEGORY_V1: [{}], CURRENCYFORMATS_V1: [{}] });
    if (options.method !== "POST") return json({ totalItems: 0 });
    if (++writes === 2) return new Response(JSON.stringify({ message: "server unavailable" }), { status: 500 });
    return json({});
  });
  await assert.rejects(seedDefaults("https://example.test", "admin"), /stopped after 1 records: server unavailable/);
});

test("schema compatibility rejects missing and unknown versions", async () => {
  const { checkSchemaVersion } = await import(modulePath("schema"));
  assert.doesNotThrow(() => checkSchemaVersion({ VALUE: "21" }));
  assert.doesNotThrow(() => checkSchemaVersion(null, true));
  assert.throws(() => checkSchemaVersion(null), /no MMEX schema version/);
  assert.throws(() => checkSchemaVersion({ VALUE: "1" }), /Unsupported MMEX schema version 1/);
  assert.doesNotThrow(() => checkSchemaVersion({ VALUE: "1" }, true, true));
  assert.throws(() => checkSchemaVersion({ VALUE: "2" }), /Unsupported MMEX schema version 2/);
  assert.throws(() => checkSchemaVersion({ VALUE: "invalid" }), /Unsupported MMEX schema version invalid/);
});

test("setup never overwrites an existing or unsupported schema version", async t => {
  const { markSchemaVersion } = await import(modulePath("schema"));
  let version = "2";
  const calls = [];
  t.mock.method(globalThis, "fetch", async (url, options = {}) => {
    calls.push(options.method || "GET");
    return json({ items: [{ VALUE: version }] });
  });
  await assert.rejects(markSchemaVersion("https://example.test", "admin"), /Unsupported MMEX schema version 2/);
  version = "21";
  await markSchemaVersion("https://example.test", "admin");
  assert.deepEqual(calls, ["GET", "GET"]);
});

test("setup records version only when missing", async t => {
  const { markSchemaVersion } = await import(modulePath("schema"));
  const calls = [];
  t.mock.method(globalThis, "fetch", async (url, options = {}) => {
    calls.push(options);
    return json(options.method === "POST" ? {} : { items: [] });
  });
  await markSchemaVersion("https://example.test", "admin");
  assert.deepEqual(JSON.parse(calls[1].body), { KEY: "MMEX_SCHEMA_VERSION", VALUE: "21" });
});

test("legacy v1 is upgraded only after validating its collections", async t => {
  const { markSchemaVersion } = await import(modulePath("schema"));
  const schema = [{ name: "items", fields: [{ name: "value", type: "number" }] }];
  const collections = [{ name: "items", type: "base", fields: [{ name: "value", type: "number" }] }];
  let version = "1";
  const calls = [];
  t.mock.method(globalThis, "fetch", async (url, options = {}) => {
    calls.push({ url, method: options.method || "GET", body: options.body });
    if (options.method === "PATCH") {
      version = JSON.parse(options.body).VALUE;
      return json({ id: "versionid", VALUE: version });
    }
    return json({ items: [{ id: "versionid", VALUE: version }] });
  });
  await assert.rejects(markSchemaVersion("https://example.test", "admin"), /without checking/);
  await assert.rejects(markSchemaVersion("https://example.test", "admin", [{ ...collections[0], fields: [] }], schema), /value missing\/type mismatch/);
  assert.equal(calls.some(call => call.method === "PATCH"), false);
  await markSchemaVersion("https://example.test", "admin", collections, schema);
  assert.equal(version, "21");
  assert.deepEqual(calls.filter(call => call.method === "PATCH").map(call => JSON.parse(call.body)), [{ VALUE: "21" }]);
  assert.match(calls.find(call => call.method === "PATCH").url, /\/dbInfo\/records\/versionid$/);
});

test("legacy upgrade reports an unconfirmed version update", async t => {
  const { markSchemaVersion } = await import(modulePath("schema"));
  const schema = [{ name: "items", fields: [] }];
  const collections = [{ name: "items", type: "base", fields: [] }];
  t.mock.method(globalThis, "fetch", async (url, options = {}) => {
    if (options.method === "PATCH") return json({});
    return json({ items: [{ id: "versionid", VALUE: "1" }] });
  });
  await assert.rejects(markSchemaVersion("https://example.test", "admin", collections, schema), /version update could not be verified/);
});

test("legacy upgrade reports an uncertain version write", async t => {
  const { markSchemaVersion } = await import(modulePath("schema"));
  t.mock.method(globalThis, "fetch", async (url, options = {}) => {
    if (options.method === "PATCH") throw Error("offline");
    return json({ items: [{ id: "versionid", VALUE: "1" }] });
  });
  await assert.rejects(markSchemaVersion("https://example.test", "admin",
    [{ name: "items", type: "base", fields: [] }], [{ name: "items", fields: [] }]),
  /version update may have succeeded/);
});

test("currency name and code uniqueness ignores case and the edited record", async () => {
  const { findDuplicateCurrency } = await import(modulePath("currencies"));
  const currencies = [
    { id: "one", CURRENCYNAME: "US dollar", CURRENCY_SYMBOL: "USD" },
    { id: "two", CURRENCYNAME: "Euro", CURRENCY_SYMBOL: "EUR" }
  ];
  assert.equal(findDuplicateCurrency(currencies, " US DOLLAR ", "other", ""), currencies[0]);
  assert.equal(findDuplicateCurrency(currencies, "other", "eur", ""), currencies[1]);
  assert.equal(findDuplicateCurrency(currencies, "US dollar", "USD", "one"), undefined);
});

test("currency deletion checks all supported references and rejects invalid responses", async t => {
  const { state } = await import(modulePath("core"));
  const { checkCurrencyReferences } = await import(modulePath("currencies"));
  state.base = "https://example.test";
  state.token = "token";
  const urls = [];
  let baseReferenced = true;
  let invalidResponse = false;
  t.mock.method(globalThis, "fetch", async url => {
    urls.push(url);
    if (invalidResponse && url.includes("ASSETS_V1")) return json({});
    if (url.includes("INFOTABLE_V1")) return json({ totalItems: baseReferenced ? 1 : 0 });
    return json({ totalItems: 0 });
  });
  await assert.rejects(checkCurrencyReferences(42), /INFOTABLE_V1 \(1\)/);
  assert.equal(urls.length, 4);
  assert.match(decodeURIComponent(urls[3]), /INFONAME = "BASECURRENCYID" && INFOVALUE = "42"/);
  baseReferenced = false;
  await checkCurrencyReferences(42);
  invalidResponse = true;
  await assert.rejects(checkCurrencyReferences(42), /invalid PocketBase response/);
  await assert.rejects(checkCurrencyReferences(-1), /Invalid currency ID/);
});

test("current balances use exact decimal sums and both sides of transfers", async () => {
  const { accountBalance, formatDecimal, formatTransactionAmount } = await import(modulePath("balances"));
  const a = { ACCOUNTID: 1, INITIALBAL: 0.1 };
  const b = { ACCOUNTID: 2, INITIALBAL: 0 };
  const rows = [
    { TRANSID: 1, ACCOUNTID: 1, TRANSCODE: "Deposit", TRANSAMOUNT: 0.2, TRANSDATE: "2025-01-01", STATUS: "" },
    { TRANSID: 2, ACCOUNTID: 1, TOACCOUNTID: 2, TRANSCODE: "Transfer", TRANSAMOUNT: 0.1, TOTRANSAMOUNT: 0.00000001, TRANSDATE: "2025-01-02", STATUS: "R" },
    { TRANSID: 3, ACCOUNTID: 1, TRANSCODE: "Withdrawal", TRANSAMOUNT: 99, TRANSDATE: "2025-01-02", STATUS: "V" },
    { TRANSID: 4, ACCOUNTID: 1, TRANSCODE: "Withdrawal", TRANSAMOUNT: 99, TRANSDATE: "2025-01-02", DELETEDTIME: "2025-01-02" },
    { TRANSID: 5, ACCOUNTID: 1, TRANSCODE: "Withdrawal", TRANSAMOUNT: 99, TRANSDATE: "2025-02-01" },
    { TRANSID: 6, ACCOUNTID: 1, TOACCOUNTID: 1, TRANSCODE: "Transfer", TRANSAMOUNT: 5, TOTRANSAMOUNT: 6, TRANSDATE: "2025-01-02" }
  ];
  const sep = new Intl.NumberFormat().formatToParts(1.1).find(x => x.type === "decimal").value;
  assert.equal(formatDecimal(accountBalance(a, rows, "2025-01-31"), { SCALE: 100 }), `0${sep}20`);
  assert.equal(formatDecimal(accountBalance(b, rows, "2025-01-31"), { SCALE: 100000000 }), `0${sep}00000001`);
  assert.equal(formatTransactionAmount(1e-21), `0${sep}000000000000000000001`);
});

test("balances report malformed data instead of silently calculating a value", async () => {
  const { accountBalance } = await import(modulePath("balances"));
  const account = { ACCOUNTID: 1, INITIALBAL: 0 };
  const row = { TRANSID: 1, ACCOUNTID: 1, TRANSCODE: "Deposit", TRANSAMOUNT: 1, TRANSDATE: "2025-01-01" };
  assert.throws(() => accountBalance(account, [{ ...row, TRANSDATE: "2025-02-30" }], "2025-03-01"), /invalid date/);
  assert.throws(() => accountBalance(account, [{ ...row, STATUS: "unknown" }], "2025-03-01"), /unsupported status/);
  assert.throws(() => accountBalance(account, [{ ...row, TRANSCODE: "unknown" }], "2025-03-01"), /unsupported type/);
  assert.throws(() => accountBalance(account, [{ ...row, TRANSAMOUNT: null }], "2025-03-01"), /Invalid monetary amount/);
});

test("amount input rejects decimal precision loss", async () => {
  const { parseStoredNumber } = await import(modulePath("balances"));
  assert.equal(parseStoredNumber(".10"), 0.1);
  assert.equal(parseStoredNumber("1e-8"), 1e-8);
  assert.throws(() => parseStoredNumber("0.10000000000000001"), /precision/);
  assert.throws(() => parseStoredNumber("9007199254740993"), /precision/);
  assert.throws(() => parseStoredNumber("1e-999999"), /range/);
});

test("transaction filters include both transfer accounts and exclude deleted by default", async () => {
  const { filterTransactions } = await import(modulePath("transactions"));
  const accounts = [{ ACCOUNTID: 1, ACCOUNTNAME: "Cash" }, { ACCOUNTID: 2, ACCOUNTNAME: "Savings" }];
  const transactions = [
    { TRANSID: 1, ACCOUNTID: 1, TOACCOUNTID: 2, TRANSCODE: "Transfer", TRANSDATE: "2025-01-02" },
    { TRANSID: 2, ACCOUNTID: 2, TRANSCODE: "Deposit", TRANSDATE: "2025-01-03", DELETEDTIME: "2025-01-04" },
    { TRANSID: 3, ACCOUNTID: 2, TRANSCODE: "Deposit", TRANSDATE: "2025-02-01" }
  ];
  const filter = options => filterTransactions(transactions, options, accounts, [], []).map(t => t.TRANSID);
  assert.deepEqual(filter({ account: "2", through: "2025-01-31" }), [1]);
  assert.deepEqual(filter({ query: "savings", showDeleted: true, through: "2025-01-31" }), [1, 2]);
  assert.deepEqual(filter({ type: "Deposit", from: "2025-01-03", showDeleted: true }), [2, 3]);
});

test("editing preserves unchanged legacy fields and transaction time", async () => {
  const { changedFields } = await import(modulePath("transactions"));
  const old = { ACCOUNTID: 1, TOACCOUNTID: -1, PAYEEID: -1, TRANSCODE: "Deposit", TRANSAMOUNT: 2, TOTRANSAMOUNT: 0, STATUS: "", NOTES: "old", CATEGID: -1, TRANSDATE: "2025-01-01 14:30:00" };
  const form = { ACCOUNTID: 1, TOACCOUNTID: null, PAYEEID: -1, TRANSCODE: "Deposit", TRANSAMOUNT: 2, TOTRANSAMOUNT: null, STATUS: "", NOTES: "new", CATEGID: null, TRANSDATE: "2025-01-01" };
  assert.deepEqual(changedFields(form, old), { NOTES: "new" });
  assert.deepEqual(changedFields({ ...form, TRANSCODE: "Transfer", TOACCOUNTID: 2, TOTRANSAMOUNT: 1 }, old), { TOACCOUNTID: 2, TRANSCODE: "Transfer", TOTRANSAMOUNT: 1, NOTES: "new" });
});

test("network and server errors expose uncertain mutation outcomes", async t => {
  const { api } = await import(modulePath("api"));
  t.mock.method(globalThis, "fetch", async () => { throw Error("offline"); });
  await assert.rejects(api("https://example.test", "/", { method: "POST" }), err => err.uncertain === true);
  globalThis.fetch.mock.mockImplementation(async () => new Response('{"message":"server error"}', { status: 500 }));
  await assert.rejects(api("https://example.test", "/", { method: "POST" }), err => err.uncertain === true);
  globalThis.fetch.mock.mockImplementation(async () => new Response('{"message":"invalid"}', { status: 400 }));
  await assert.rejects(api("https://example.test", "/", { method: "POST" }), err => err.uncertain === false);
});

test("schema reset deletes only inspected MMEX collections and recreates the schema", async t => {
  const { inspectReset, resetCollections } = await import(modulePath("schema-reset"));
  const collections = [
    { id: "catid", name: "CATEGORY_V1", type: "base", fields: [] },
    { id: "otherid", name: "Other", type: "base", fields: [] },
    { id: "usersid", name: "users", type: "auth", fields: [] }
  ];
  const calls = [];
  t.mock.method(globalThis, "fetch", async (url, options = {}) => {
    calls.push({ url, method: options.method || "GET", body: options.body });
    if (url.includes("/api/collections?")) return json({ items: collections, totalPages: 1 });
    if (url.includes("/catid/records")) return json({ totalItems: 2 });
    if (options.method === "DELETE") return new Response(null, { status: 204 });
    if (url.endsWith("/api/collections/import")) return json({});
    throw Error(`Unexpected request: ${url}`);
  });
  const preview = await inspectReset("https://example.test", "admin");
  assert.deepEqual(preview.entries, [{ name: "CATEGORY_V1", id: "catid", count: 2 }]);
  await resetCollections(preview.base, "admin", preview, [{ name: "CATEGORY_V1" }], null, () => {});
  assert.deepEqual(calls.filter(c => c.method === "DELETE").map(c => c.url), ["https://example.test/api/collections/catid"]);
  const imported = calls.find(c => c.method === "PUT");
  assert.equal(JSON.parse(imported.body).deleteMissing, false);
});

test("schema reset stops before deletion if records changed or another collection references MMEX", async t => {
  const { inspectReset, resetCollections } = await import(modulePath("schema-reset"));
  let count = 2, reference = false;
  const mutations = [];
  t.mock.method(globalThis, "fetch", async (url, options = {}) => {
    if (options.method) mutations.push(options.method);
    if (url.includes("/api/collections?")) return json({ items: [
      { id: "catid", name: "CATEGORY_V1", type: "base", fields: [] },
      { id: "otherid", name: "Other", type: "base", fields: reference ? [{ type: "relation", collectionId: "catid" }] : [] }
    ], totalPages: 1 });
    return json({ totalItems: count });
  });
  const preview = await inspectReset("https://example.test", "admin");
  count = 3;
  await assert.rejects(resetCollections(preview.base, "admin", preview, [], null, () => {}), /changed since inspection/);
  reference = true;
  await assert.rejects(inspectReset(preview.base, "admin"), /Other references an MMEX collection/);
  assert.deepEqual(mutations, []);
});

test("schema reset reports an uncertain deletion and never imports after failure", async t => {
  const { inspectReset, resetCollections } = await import(modulePath("schema-reset"));
  const calls = [];
  t.mock.method(globalThis, "fetch", async (url, options = {}) => {
    calls.push(options.method || "GET");
    if (url.includes("/api/collections?")) return json({ items: [{ id: "catid", name: "CATEGORY_V1", type: "base", fields: [] }], totalPages: 1 });
    if (options.method === "DELETE") throw Error("connection lost");
    return json({ totalItems: 0 });
  });
  const preview = await inspectReset("https://example.test", "admin");
  await assert.rejects(resetCollections(preview.base, "admin", preview, [], null, () => {}), /deletion of CATEGORY_V1 may have succeeded/);
  assert.equal(calls.includes("PUT"), false);
});

test("schema reset refuses an unsupported schema version", async t => {
  const { inspectReset } = await import(modulePath("schema-reset"));
  t.mock.method(globalThis, "fetch", async url => {
    if (url.includes("/api/collections?")) return json({ items: [{ id: "dbid", name: "dbInfo", type: "base", fields: [] }], totalPages: 1 });
    if (url.includes("/dbInfo/records")) return json({ items: [{ VALUE: "2" }] });
    throw Error(`Unexpected request: ${url}`);
  });
  await assert.rejects(inspectReset("https://example.test", "admin"), /Unsupported MMEX schema version 2/);
});

test("reset inspection shows the exact phrase and gates deletion", async t => {
  const { state } = await import(modulePath("core"));
  const oldBase = state.base;
  const previousDocument = globalThis.document;
  const elements = new Map();
  const element = id => {
    if (!elements.has(id)) elements.set(id, {
      value: "", checked: false, disabled: id === "resetButton",
      textContent: "", innerHTML: "", validity: { valid: true },
      listeners: {}, addEventListener(type, handler) { this.listeners[type] = handler; }
    });
    return elements.get(id);
  };
  globalThis.document = { getElementById: element };
  state.base = "https://example.test";
  let empty = false;
  let fail = false;
  let releaseAuth;
  const authGate = new Promise(resolve => { releaseAuth = resolve; });
  const calls = [];
  t.mock.method(globalThis, "fetch", async (url, options = {}) => {
    calls.push(options.method || "GET");
    if (url.endsWith("/auth-with-password")) {
      await authGate;
      return fail ? new Response('{"message":"Invalid credentials"}', { status: 400 }) : json({ token: "admin" });
    }
    if (url.includes("/api/collections?")) return json({
      items: empty ? [] : [{ id: "catid", name: "CATEGORY_V1", type: "base", fields: [] }],
      totalPages: 1
    });
    if (url.includes("/catid/records")) return json({ totalItems: 2 });
    throw Error(`Unexpected request: ${url}`);
  });
  try {
    await import(modulePath("setup"));
    element("adminEmail").value = "admin@example.test";
    element("adminPassword").value = "password";
    assert.equal(element("resetButton").disabled, true);
    const inspection = element("inspectResetButton").listeners.click();
    assert.match(element("inspectResetStatus").textContent, /Authenticating and inspecting/);
    assert.equal(element("inspectResetButton").disabled, true);
    releaseAuth();
    await inspection;
    assert.equal(element("resetButton").disabled, false);
    assert.equal(element("resetPhrase").textContent, "RESET https://example.test");
    assert.equal(element("adminPassword").value, "");
    element("resetConfirm").value = "RESET https://example.test";
    await element("resetButton").listeners.click();
    assert.match(element("resetStatus").textContent, /confirm the backup/);
    assert.equal(calls.filter(method => method === "POST").length, 1);
    empty = true;
    element("adminPassword").value = "password";
    await element("inspectResetButton").listeners.click();
    assert.equal(element("resetButton").disabled, true);
    assert.equal(element("resetPhrase").textContent, "shown after inspection");
    fail = true;
    element("adminPassword").value = "password";
    await element("inspectResetButton").listeners.click();
    assert.equal(element("resetButton").disabled, true);
    assert.match(element("inspectResetStatus").textContent, /Invalid credentials/);
  } finally {
    state.base = oldBase;
    if (previousDocument === undefined) delete globalThis.document;
    else globalThis.document = previousDocument;
  }
});

test("payee default category applies only to an existing category", async () => {
  const { defaultCategoryForPayee } = await import(modulePath("transactions"));
  const payees = [{ PAYEEID: 7, CATEGID: 3 }, { PAYEEID: 8, CATEGID: -1 }, { PAYEEID: 9, CATEGID: 99 }];
  const categories = [{ CATEGID: 3 }, { CATEGID: 4 }];
  assert.equal(defaultCategoryForPayee("7", payees, categories), "3");
  assert.equal(defaultCategoryForPayee(7, payees, categories), "3");
  assert.equal(defaultCategoryForPayee("8", payees, categories), null);
  assert.equal(defaultCategoryForPayee("9", payees, categories), null);
  assert.equal(defaultCategoryForPayee("-1", payees, categories), null);
});

test("remembered tokens stay out of session storage and are cleared on sign out", async () => {
  const fakeStorage = () => {
    const items = new Map();
    return {
      items,
      getItem: key => items.has(key) ? items.get(key) : null,
      setItem: (key, value) => items.set(key, String(value)),
      removeItem: key => items.delete(key)
    };
  };
  const previousSession = globalThis.sessionStorage;
  const previousLocal = globalThis.localStorage;
  globalThis.sessionStorage = fakeStorage();
  globalThis.localStorage = fakeStorage();
  try {
    const { state } = await import(modulePath("core"));
    const { tokenKey } = await import(modulePath("core"));
    const { readStoredToken, saveStoredToken, clearStoredToken } = await import(modulePath("auth"));
    state.base = "https://example.test";
    const key = tokenKey();
    assert.equal(readStoredToken(), null);
    saveStoredToken("session-token", false);
    assert.equal(globalThis.sessionStorage.getItem(key), "session-token");
    assert.equal(globalThis.localStorage.getItem(key), null);
    assert.deepEqual(readStoredToken(), { token: "session-token", remember: false });
    saveStoredToken("persistent-token", true);
    assert.equal(globalThis.localStorage.getItem(key), "persistent-token");
    assert.equal(globalThis.sessionStorage.getItem(key), null);
    assert.deepEqual(readStoredToken(), { token: "persistent-token", remember: true });
    globalThis.sessionStorage.setItem(key, "newer-session-token");
    assert.deepEqual(readStoredToken(), { token: "newer-session-token", remember: false });
    clearStoredToken();
    assert.equal(globalThis.sessionStorage.getItem(key), null);
    assert.equal(globalThis.localStorage.getItem(key), null);
  } finally {
    if (previousSession === undefined) delete globalThis.sessionStorage;
    else globalThis.sessionStorage = previousSession;
    if (previousLocal === undefined) delete globalThis.localStorage;
    else globalThis.localStorage = previousLocal;
  }
});

test("base currency display reports unset, invalid, ambiguous and missing values", async () => {
  const { state } = await import(modulePath("core"));
  const { baseCurrencyText } = await import(modulePath("currencies"));
  state.currencies = [{ CURRENCYID: 1, CURRENCYNAME: "US Dollar", CURRENCY_SYMBOL: "USD" }];
  state.baseCurrencyInfo = [];
  assert.match(baseCurrencyText(), /not set/);
  state.baseCurrencyInfo = [{ INFONAME: "BASECURRENCYID", INFOVALUE: "1" }];
  assert.equal(baseCurrencyText(), "US Dollar (USD)");
  state.baseCurrencyInfo = [{ INFOVALUE: "2" }];
  assert.match(baseCurrencyText(), /missing currency 2/);
  state.baseCurrencyInfo = [{ INFOVALUE: "0" }];
  assert.match(baseCurrencyText(), /invalid value 0/);
  state.baseCurrencyInfo = [{ INFOVALUE: "1" }, { INFOVALUE: "2" }];
  assert.match(baseCurrencyText(), /ambiguous: 1, 2/);
});

test("base currency plan inserts only when unset and never rewrites an existing choice", async () => {
  const { planBaseCurrencyChange, canSetBaseCurrency, baseCurrencyId } = await import(modulePath("currencies"));
  assert.deepEqual(planBaseCurrencyChange([], 5), { mode: "insert" });
  assert.deepEqual(planBaseCurrencyChange([{ id: "r1", INFOVALUE: "5" }], 5), { mode: "none" });
  assert.deepEqual(planBaseCurrencyChange([{ id: "r1", INFOVALUE: "5" }], 6), { mode: "blocked", reason: "change", current: 5 });
  assert.deepEqual(planBaseCurrencyChange([{ id: "r1", INFOVALUE: "0" }], 7), { mode: "update", recordId: "r1" });
  assert.deepEqual(planBaseCurrencyChange([{ id: "r1", INFOVALUE: "abc" }], 7), { mode: "update", recordId: "r1" });
  assert.deepEqual(planBaseCurrencyChange([{ id: "r1", INFOVALUE: "5" }, { id: "r2", INFOVALUE: "6" }], 5), { mode: "blocked", reason: "duplicate" });
  assert.deepEqual(planBaseCurrencyChange([], 0), { mode: "invalid" });
  assert.deepEqual(planBaseCurrencyChange([], -1), { mode: "invalid" });
  assert.equal(canSetBaseCurrency([]), true);
  assert.equal(canSetBaseCurrency([{ id: "r1", INFOVALUE: "0" }]), true);
  assert.equal(canSetBaseCurrency([{ id: "r1", INFOVALUE: "5" }]), false);
  assert.equal(canSetBaseCurrency([{ id: "r1", INFOVALUE: "5" }, { id: "r2", INFOVALUE: "6" }]), false);
  assert.equal(baseCurrencyId([{ id: "r1", INFOVALUE: "5" }]), 5);
  assert.equal(baseCurrencyId([]), null);
  const ids = new Set([5, 6]);
  assert.deepEqual(planBaseCurrencyChange([{ id: "r1", INFOVALUE: "5" }], 6, ids), { mode: "blocked", reason: "change", current: 5 });
  assert.deepEqual(planBaseCurrencyChange([{ id: "r1", INFOVALUE: "99" }], 5, ids), { mode: "update", recordId: "r1" });
  assert.equal(canSetBaseCurrency([{ id: "r1", INFOVALUE: "99" }], ids), true);
  assert.equal(canSetBaseCurrency([{ id: "r1", INFOVALUE: "5" }], ids), false);
});

test("schema inspection separates missing audit fields from real mismatches", async () => {
  const { inspectSchema, isAuditField, checkSchemaFields } = await import(modulePath("schema"));
  const audit = [
    { id: "a1", name: "created", type: "autodate", system: true, onCreate: true, onUpdate: false },
    { id: "a2", name: "updated", type: "autodate", system: true, onCreate: true, onUpdate: true }
  ];
  const schema = [
    { name: "items", type: "base", fields: [{ name: "value", type: "number" }, ...audit] },
    { name: "others", type: "base", fields: [{ name: "label", type: "text" }, ...audit] }
  ];
  assert.equal(isAuditField(audit[0]), true);
  assert.equal(isAuditField({ name: "created", type: "text", system: true }), false);
  assert.equal(isAuditField({ name: "other", type: "autodate", system: true }), false);

  const complete = [
    { id: "c1", name: "items", type: "base", fields: [{ name: "value", type: "number" }, ...audit] },
    { id: "c2", name: "others", type: "base", fields: [{ name: "label", type: "text" }, ...audit] }
  ];
  assert.deepEqual(inspectSchema(complete, schema), { problems: [], additions: [] });
  assert.doesNotThrow(() => checkSchemaFields(complete, schema));

  const legacy = [
    { id: "c1", name: "items", type: "base", fields: [{ name: "value", type: "number" }] },
    { id: "c2", name: "others", type: "base", fields: [{ name: "label", type: "text" }] }
  ];
  const legacyPlan = inspectSchema(legacy, schema);
  assert.deepEqual(legacyPlan.problems, []);
  assert.deepEqual(legacyPlan.additions.map(item => item.collection.name), ["items", "others"]);
  assert.deepEqual(legacyPlan.additions[0].fields.map(field => field.name), ["created", "updated"]);
  assert.throws(() => checkSchemaFields(legacy, schema), /items missing created\/updated/);

  const broken = [
    { id: "c1", name: "items", type: "base", fields: [{ name: "value", type: "text" }] },
    { id: "c2", name: "others", type: "base", fields: [{ name: "label", type: "text" }] }
  ];
  const brokenPlan = inspectSchema(broken, schema);
  assert.deepEqual(brokenPlan.problems, ["items.value missing/type mismatch"]);
  assert.deepEqual(brokenPlan.additions.map(item => item.collection.name), ["items", "others"]);
});

test("audit field repair patches collections then re-verifies the schema", async t => {
  const { state } = await import(modulePath("core"));
  const { ensureSchemaFields } = await import(modulePath("schema"));
  const audit = [
    { id: "a1", name: "created", type: "autodate", system: true, onCreate: true, onUpdate: false },
    { id: "a2", name: "updated", type: "autodate", system: true, onCreate: true, onUpdate: true }
  ];
  const schema = [{ name: "items", type: "base", fields: [{ name: "value", type: "number" }, ...audit] }];
  state.base = "https://example.test";
  let added = false;
  const patches = [];
  t.mock.method(globalThis, "fetch", async (url, options = {}) => {
    if (options.method === "PATCH") {
      patches.push({ url, body: JSON.parse(options.body) });
      added = true;
      return json({ id: "c1", name: "items", type: "base", fields: JSON.parse(options.body).fields });
    }
    return json({});
  });
  const reload = async () => [{ id: "c1", name: "items", type: "base", fields: [{ name: "value", type: "number" }, ...(added ? audit : [])] }];
  const result = await ensureSchemaFields(state.base, "admin", [{ id: "c1", name: "items", type: "base", fields: [{ name: "value", type: "number" }] }], schema, reload);
  assert.deepEqual(result.applied, ["items"]);
  assert.equal(patches.length, 1);
  assert.match(patches[0].url, /\/api\/collections\/c1$/);
  assert.deepEqual(patches[0].body.fields.map(field => field.name), ["value", "created", "updated"]);
  const noop = await ensureSchemaFields(state.base, "admin", [{ id: "c1", name: "items", type: "base", fields: [{ name: "value", type: "number" }, ...audit] }], schema, reload);
  assert.deepEqual(noop.applied, []);
  await assert.rejects(
    ensureSchemaFields(state.base, "admin", [{ id: "c1", name: "items", type: "base", fields: [] }], schema, reload),
    /items\.value missing\/type mismatch/
  );
});
