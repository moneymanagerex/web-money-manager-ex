import { state, $, esc, setStatus, newBusinessId } from "./core.mjs";
import { api, fetchRecords } from "./api.mjs?v=20261123-payee-management";

let reloadWorkspace;

export function findDuplicateCurrency(currencies, name, code, recordId) {
  const normalize = value => String(value ?? "").trim().toLowerCase();
  return currencies.find(currency => currency.id !== recordId &&
    (normalize(currency.CURRENCYNAME) === normalize(name) ||
     normalize(currency.CURRENCY_SYMBOL) === normalize(code)));
}

export async function checkCurrencyReferences(currencyId) {
  const key = Number(currencyId);
  if (!Number.isSafeInteger(key) || key <= 0) throw Error("Invalid currency ID.");
  const queries = [
    ["ACCOUNTLIST_V1", `CURRENCYID = ${key}`],
    ["ASSETS_V1", `CURRENCYID = ${key}`],
    ["CURRENCYHISTORY_V1", `CURRENCYID = ${key}`],
    ["INFOTABLE_V1", `INFONAME = "BASECURRENCYID" && INFOVALUE = "${key}"`]
  ];
  const results = await Promise.all(queries.map(([collection, filter]) =>
    api(state.base, `/api/collections/${collection}/records?perPage=1&filter=${encodeURIComponent(filter)}`, {}, state.token)
  ));
  if (results.some(result => !Number.isSafeInteger(result.totalItems) || result.totalItems < 0)) {
    throw Error("Could not verify currency references: invalid PocketBase response.");
  }
  const refs = results.map((result, index) => ({ collection: queries[index][0], count: result.totalItems }))
    .filter(result => result.count > 0);
  if (refs.length) {
    throw Error(`Cannot delete: currency is referenced by ${refs.map(result => `${result.collection} (${result.count})`).join(", ")}.`);
  }
}

function validCurrencyId(value) {
  const id = Number(value);
  return Number.isSafeInteger(id) && id > 0 ? id : null;
}

function knownCurrency(existingIds, id) {
  return existingIds === null || existingIds.has(id);
}

export function baseCurrencyId(rows) {
  return rows.length === 1 ? validCurrencyId(rows[0].INFOVALUE) : null;
}

export function canSetBaseCurrency(rows, existingIds = null) {
  if (rows.length === 0) return true;
  if (rows.length !== 1) return false;
  const id = validCurrencyId(rows[0].INFOVALUE);
  return id === null || !knownCurrency(existingIds, id);
}

export function planBaseCurrencyChange(baseRows, currencyId, existingIds = null) {
  const target = validCurrencyId(currencyId);
  if (target === null) return { mode: "invalid" };
  if (baseRows.length === 0) return { mode: "insert" };
  if (baseRows.length > 1) return { mode: "blocked", reason: "duplicate" };
  const current = validCurrencyId(baseRows[0].INFOVALUE);
  if (current === null || !knownCurrency(existingIds, current)) return { mode: "update", recordId: baseRows[0].id };
  if (current === target) return { mode: "none" };
  return { mode: "blocked", reason: "change", current };
}

export function baseCurrencyText() {
  const rows = state.baseCurrencyInfo ?? [];
  if (rows.length === 0) return "not set in INFOTABLE_V1";
  if (rows.length > 1) return `ambiguous: ${rows.map(row => row.INFOVALUE).join(", ")}`;
  const id = Number(rows[0].INFOVALUE);
  if (!Number.isSafeInteger(id) || id <= 0) return `invalid value ${rows[0].INFOVALUE}`;
  const currency = state.currencies.find(item => Number(item.CURRENCYID) === id);
  if (!currency) return `missing currency ${id}`;
  return `${currency.CURRENCYNAME} (${currency.CURRENCY_SYMBOL})`;
}

export function renderCurrencies() {
  const query = $("currencySearch").value.trim().toLowerCase();
  const baseRows = state.baseCurrencyInfo ?? [];
  const baseId = baseCurrencyId(baseRows);
  const currencyIds = new Set(state.currencies.map(currency => Number(currency.CURRENCYID)));
  const settable = canSetBaseCurrency(baseRows, currencyIds);
  const rows = state.currencies.filter(currency =>
    !query || [currency.CURRENCYNAME, currency.CURRENCY_SYMBOL, currency.CURRENCY_TYPE, currency.PFX_SYMBOL, currency.SFX_SYMBOL]
      .some(value => String(value ?? "").toLowerCase().includes(query))
  );
  $("currenciesBody").innerHTML = rows.length ? rows.map(currency => `<tr>
    <td>${esc(currency.CURRENCYID)}</td><td>${esc(currency.CURRENCYNAME)}${Number(currency.CURRENCYID) === baseId ? ' <span class="pill">Base</span>' : ""}</td>
    <td>${esc(currency.CURRENCY_SYMBOL)}</td><td>${esc(currency.CURRENCY_TYPE)}</td>
    <td>${esc(currency.PFX_SYMBOL)}</td><td>${esc(currency.SFX_SYMBOL)}</td>
    <td>${esc(currency.SCALE)}</td><td>${esc(currency.BASECONVRATE)}</td>
    <td><button type="button" class="editCurrency" data-id="${esc(currency.id)}">Edit</button>
    <button type="button" class="deleteCurrency" data-id="${esc(currency.id)}">Delete</button>${settable ? `
    <button type="button" class="setBaseCurrency" data-id="${esc(currency.id)}">Set base</button>` : ""}</td>
  </tr>`).join("") : `<tr><td colspan="9" class="muted">No currencies match the search.</td></tr>`;
  $("currenciesStatus").textContent = `${rows.length} shown / ${state.currencies.length} loaded`;
  const baseStatus = $("currencyBaseStatus");
  if (baseStatus) baseStatus.textContent = baseCurrencyText();
  document.querySelectorAll(".editCurrency").forEach(button => button.addEventListener("click", () => editCurrency(button.dataset.id)));
  document.querySelectorAll(".deleteCurrency").forEach(button => button.addEventListener("click", () => deleteCurrency(button.dataset.id)));
  document.querySelectorAll(".setBaseCurrency").forEach(button => button.addEventListener("click", () => setBaseCurrency(button.dataset.id)));
}

async function setBaseCurrency(recordId) {
  const currency = state.currencies.find(item => item.id === recordId);
  if (!currency) return;
  const label = `${currency.CURRENCYNAME} (${currency.CURRENCY_SYMBOL})`;
  if (!confirm(`Set ${label} as the base currency? Only the setting is recorded; conversion rates are not converted.`)) return;
  try {
    const baseRows = await fetchRecords("INFOTABLE_V1", "", `INFONAME = "BASECURRENCYID"`);
    const currencyIds = new Set(state.currencies.map(item => Number(item.CURRENCYID)));
    const plan = planBaseCurrencyChange(baseRows, currency.CURRENCYID, currencyIds);
    if (plan.mode === "invalid") throw Error("This currency has an invalid ID and cannot become the base currency.");
    if (plan.mode === "none") {
      setStatus("currencyFormStatus", `${label} is already the base currency.`, "ok");
      return;
    }
    if (plan.mode === "blocked") {
      if (plan.reason === "duplicate")
        throw Error("Multiple BASECURRENCYID rows exist. Fix them in PocketBase before choosing a base currency.");
      throw Error(`Changing the base currency from ${plan.current} is not supported here: that also resets conversion rates and deletes currency history. Change it in MMEX desktop.`);
    }
    if (plan.mode === "update") {
      await api(state.base, `/api/collections/INFOTABLE_V1/records/${encodeURIComponent(plan.recordId)}`, { method: "PATCH", body: JSON.stringify({ INFOVALUE: String(currency.CURRENCYID) }) }, state.token);
    } else {
      await api(state.base, "/api/collections/INFOTABLE_V1/records", {
        method: "POST",
        body: JSON.stringify({ INFOID: newBusinessId(), INFONAME: "BASECURRENCYID", INFOVALUE: String(currency.CURRENCYID) })
      }, state.token);
    }
    await reloadWorkspace();
    setStatus("currencyFormStatus", `${label} is now the base currency. Conversion rates were not changed.`, "ok");
  } catch (error) {
    setStatus("currencyFormStatus", error.message, "error");
  }
}

export function resetCurrencyForm() {
  $("currencyForm").reset();
  $("currencyFormPanel").open = false;
  $("currencyRecordId").value = "";
  $("currencyFormTitle").textContent = "New currency";
  $("saveCurrencyButton").textContent = "Create currency";
  $("cancelCurrencyEdit").classList.add("hidden");
  setStatus("currencyFormStatus", "");
}

function editCurrency(id) {
  const currency = state.currencies.find(item => item.id === id);
  if (!currency) return;
  $("currencyFormPanel").open = true;
  $("currencyFormPanel").scrollIntoView({ behavior: "smooth", block: "start" });
  if (!["Fiat", "Crypto"].includes(currency.CURRENCY_TYPE)) {
    setStatus("currencyFormStatus", `Unsupported currency type: ${currency.CURRENCY_TYPE}. Edit it in PocketBase instead.`, "error");
    return;
  }
  $("currencyRecordId").value = currency.id;
  $("currencyName").value = currency.CURRENCYNAME ?? "";
  $("currencyCode").value = currency.CURRENCY_SYMBOL ?? "";
  $("currencyType").value = currency.CURRENCY_TYPE;
  $("currencyPrefix").value = currency.PFX_SYMBOL ?? "";
  $("currencySuffix").value = currency.SFX_SYMBOL ?? "";
  $("currencyDecimal").value = currency.DECIMAL_POINT ?? "";
  $("currencyGroup").value = currency.GROUP_SEPARATOR ?? "";
  $("currencyUnit").value = currency.UNIT_NAME ?? "";
  $("currencyCent").value = currency.CENT_NAME ?? "";
  $("currencyScale").value = currency.SCALE ?? "";
  $("currencyRate").value = currency.BASECONVRATE ?? "";
  $("currencyFormTitle").textContent = `Edit currency · ${currency.CURRENCYNAME}`;
  $("saveCurrencyButton").textContent = "Save currency";
  $("cancelCurrencyEdit").classList.remove("hidden");
  setStatus("currencyFormStatus", `Editing MMEX currency ${currency.CURRENCYID}. The primary key will not change.`);
}

async function deleteCurrency(id) {
  const currency = state.currencies.find(item => item.id === id);
  if (!currency || !confirm(`Delete currency “${currency.CURRENCYNAME}”? This cannot be undone.`)) return;
  try {
    await checkCurrencyReferences(currency.CURRENCYID);
    await api(state.base, `/api/collections/CURRENCYFORMATS_V1/records/${encodeURIComponent(id)}`, { method: "DELETE" }, state.token);
    await reloadWorkspace();
    resetCurrencyForm();
    setStatus("currencyFormStatus", `Deleted currency ${currency.CURRENCYNAME}.`, "ok");
  } catch (error) {
    setStatus("currencyFormStatus", error.message, "error");
  }
}

export function initCurrencies(reload) {
  reloadWorkspace = reload;
  $("currencySearch").addEventListener("input", renderCurrencies);
  $("cancelCurrencyEdit").addEventListener("click", resetCurrencyForm);
  $("currencyForm").addEventListener("submit", async event => {
    event.preventDefault();
    const button = $("saveCurrencyButton");
    button.disabled = true;
    try {
      const recordId = $("currencyRecordId").value;
      const payload = {
        CURRENCYNAME: $("currencyName").value.trim(),
        CURRENCY_SYMBOL: $("currencyCode").value.trim(),
        CURRENCY_TYPE: $("currencyType").value,
        PFX_SYMBOL: $("currencyPrefix").value,
        SFX_SYMBOL: $("currencySuffix").value,
        DECIMAL_POINT: $("currencyDecimal").value,
        GROUP_SEPARATOR: $("currencyGroup").value,
        UNIT_NAME: $("currencyUnit").value,
        CENT_NAME: $("currencyCent").value,
        SCALE: Number($("currencyScale").value),
        BASECONVRATE: Number($("currencyRate").value)
      };
      if (!payload.CURRENCYNAME || !payload.CURRENCY_SYMBOL) throw Error("Currency name and code are required.");
      if (!["Fiat", "Crypto"].includes(payload.CURRENCY_TYPE)) throw Error("Choose a supported currency type.");
      if (!Number.isSafeInteger(payload.SCALE) || payload.SCALE < 1) throw Error("Scale must be a positive whole number.");
      if ($("currencyRate").value === "" || !Number.isFinite(payload.BASECONVRATE) || payload.BASECONVRATE < 0) throw Error("Base conversion rate must be a non-negative number.");
      const currencies = await fetchRecords("CURRENCYFORMATS_V1", "CURRENCYNAME");
      if (findDuplicateCurrency(currencies, payload.CURRENCYNAME, payload.CURRENCY_SYMBOL, recordId)) {
        throw Error("A currency with this name or code already exists.");
      }
      if (recordId) {
        await api(state.base, `/api/collections/CURRENCYFORMATS_V1/records/${encodeURIComponent(recordId)}`, { method: "PATCH", body: JSON.stringify(payload) }, state.token);
      } else {
        payload.CURRENCYID = newBusinessId();
        await api(state.base, "/api/collections/CURRENCYFORMATS_V1/records", { method: "POST", body: JSON.stringify(payload) }, state.token);
      }
      await reloadWorkspace();
      resetCurrencyForm();
      setStatus("currencyFormStatus", recordId ? "Currency updated." : "Currency created.", "ok");
    } catch (error) {
      setStatus("currencyFormStatus", error.message, "error");
    } finally {
      button.disabled = false;
    }
  });
}
