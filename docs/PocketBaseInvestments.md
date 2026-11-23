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

The desktop **Sync investments** action synchronizes the prerequisites
(currencies, accounts, categories, and payees), assets, stock positions, price
history, investment-linked transactions, transaction links, and share details
in dependency order. It downloads missing rows and uploads local-only rows;
rows that differ on both sides are reported as conflicts and left untouched.
The separate **Sync transactions** action continues to sync regular
withdrawals, deposits, and transfers without investment links.

Stock history, transaction links, and share details use their MMEX row IDs.
Assets, stocks, and transactions use their MMEX business IDs. The sync compares
the serialized business fields and does not overwrite edits made on either
side. Linked transactions are only imported when their referenced asset or
stock exists; stock trades also need a share detail record.

The WebApp currently displays assets and stock records as read-only data. It
loads stock price history when the Investments view opens. It does not retrieve
live prices or combine valuations across account currencies.
