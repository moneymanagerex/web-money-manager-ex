# PocketMMEX Investments

PocketMMEX groups assets and securities under one Investments view. The stored
MMEX records remain separate: an asset has its own valuation settings; a stock
position carries market data and is linked to trades; and the related cash
movement remains a normal account transaction.

```mermaid
flowchart LR
  subgraph PB[ PocketBase collections ]
    A[ASSETS_V1]
    S[STOCK_V1]
    H[STOCKHISTORY_V1]
    T[CHECKINGACCOUNT_V1]
    L[TRANSLINK_V1]
    Q[SHAREINFO_V1]
    AC[ACCOUNTLIST_V1]
  end

  subgraph WEB[PocketMMEX Investments view]
    AV[Assets list]
    SV[Stock positions]
    TV[Buy / sell activity and lots]
    HV[Saved price history]
  end

  subgraph DESK[MMEX desktop local SQLite]
    DA[AssetModel]
    DS[StockModel]
    DH[StockHistoryModel]
    DT[TrxModel]
    DL[TrxLinkModel]
    DQ[TrxShareModel]
  end

  A --> AV
  S --> SV
  H --> HV
  T --> TV
  L --> TV
  Q --> TV
  AC --> SV
  S -->|held at| AC
  L -->|stock trade reference| S
  L -->|cash transaction| T
  Q -->|shares / price / commission / lot| T

  A <--> DA
  S <--> DS
  H <--> DH
  T <--> DT
  L <--> DL
  Q <--> DQ
```

## Sync order

The desktop **Sync investments** action must preserve dependencies when it
imports records: accounts and investment records first, then linked
transactions, then transaction links and share details. Upload follows the
same order so a linked record never points to a missing local business key.
Stock history is keyed by symbol and date; the other records use their MMEX
business IDs. Sync compares rows by those keys and leaves conflicting rows for
the user to resolve.

The WebApp currently displays assets and stock records as read-only data. It
loads stock price history when the Investments view opens. It does not retrieve
live prices or combine valuations across account currencies.
