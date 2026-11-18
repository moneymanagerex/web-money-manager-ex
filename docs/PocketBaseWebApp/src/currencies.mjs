import { state, $, esc, setStatus, newBusinessId } from "./core.mjs";
import { api, fetchRecords } from "./api.mjs";

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

export function renderCurrencies() {
  const query = $("currencySearch").value.trim().toLowerCase();
  const rows = state.currencies.filter(currency =>
    !query || [currency.CURRENCYNAME, currency.CURRENCY_SYMBOL, currency.CURRENCY_TYPE, currency.PFX_SYMBOL, currency.SFX_SYMBOL]
      .some(value => String(value ?? "").toLowerCase().includes(query))
  );
  $("currenciesBody").innerHTML = rows.map(currency => `<tr>
    <td>${esc(currency.CURRENCYID)}</td><td>${esc(currency.CURRENCYNAME)}</td>
    <td>${esc(currency.CURRENCY_SYMBOL)}</td><td>${esc(currency.CURRENCY_TYPE)}</td>
    <td>${esc(currency.PFX_SYMBOL)}</td><td>${esc(currency.SFX_SYMBOL)}</td>
    <td>${esc(currency.SCALE)}</td><td>${esc(currency.BASECONVRATE)}</td>
    <td><button type="button" class="editCurrency" data-id="${esc(currency.id)}">Edit</button>
    <button type="button" class="deleteCurrency" data-id="${esc(currency.id)}">Delete</button></td>
  </tr>`).join("");
  $("currenciesStatus").textContent = `${rows.length} shown / ${state.currencies.length} loaded`;
  document.querySelectorAll(".editCurrency").forEach(button => button.addEventListener("click", () => editCurrency(button.dataset.id)));
  document.querySelectorAll(".deleteCurrency").forEach(button => button.addEventListener("click", () => deleteCurrency(button.dataset.id)));
}

export function resetCurrencyForm() {
  $("currencyForm").reset();
  $("currencyRecordId").value = "";
  $("currencyFormTitle").textContent = "New currency";
  $("saveCurrencyButton").textContent = "Create currency";
  $("cancelCurrencyEdit").classList.add("hidden");
  setStatus("currencyFormStatus", "");
}

function editCurrency(id) {
  const currency = state.currencies.find(item => item.id === id);
  if (!currency) return;
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
