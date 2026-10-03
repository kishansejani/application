{{-- Self-contained invoice styles (no Tailwind dependency) — used on screen and in print. --}}
<style>
    .inv-sheet { --inv-ink: #0f172a; --inv-ink-2: #334155; --inv-muted: #64748b; --inv-line: #e2e8f0; --inv-soft: #f8fafc; --inv-accent: #047857;
        background: #fff; color: var(--inv-ink); width: 210mm; max-width: 100%; min-height: 297mm; margin: 0 auto; padding: 14mm 14mm 12mm;
        box-sizing: border-box; font-family: 'Plus Jakarta Sans', 'Hind Vadodara', ui-sans-serif, system-ui, sans-serif; font-size: 11.5px; line-height: 1.5;
        -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .inv-sheet.is-landscape { width: 297mm; min-height: 210mm; }
    .inv-sheet *, .inv-sheet *::before, .inv-sheet *::after { box-sizing: border-box; }
    .inv-sheet [lang="gu"], .inv-store-gu, .inv-item-gu { font-family: 'Hind Vadodara', 'Noto Sans Gujarati', 'Shruti', sans-serif; }
    .inv-doc p, .inv-doc h2, .inv-doc h4 { margin: 0; }
    .inv-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; letter-spacing: -.01em; }
    .inv-muted { color: var(--inv-muted); }

    .inv-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; padding-bottom: 14px; border-bottom: 2px solid var(--inv-ink); }
    .inv-brand { display: flex; gap: 12px; align-items: flex-start; min-width: 0; }
    .inv-logo { width: 44px; height: 44px; border-radius: 12px; background: var(--inv-accent); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 15px; letter-spacing: .02em; flex-shrink: 0; }
    .inv-store { font-size: 19px; font-weight: 800; letter-spacing: -.01em; line-height: 1.2; }
    .inv-store-gu { font-size: 12.5px; font-weight: 600; color: var(--inv-accent); }
    .inv-addr { margin-top: 6px !important; color: var(--inv-muted); font-size: 10.5px; line-height: 1.55; }
    .inv-title-block { text-align: right; flex-shrink: 0; }
    .inv-title { font-size: 20px; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; }
    .inv-copy { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--inv-muted); margin-bottom: 6px !important; }
    .inv-ids { margin-left: auto; border-collapse: collapse; font-size: 10.5px; }
    .inv-ids th { text-align: left; font-weight: 600; color: var(--inv-muted); padding: 1px 10px 1px 0; }
    .inv-ids td { text-align: right; font-weight: 700; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; padding: 1px 0; }

    .inv-meta { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); border: 1px solid var(--inv-line); border-radius: 8px; margin-top: 14px; overflow: hidden; }
    .inv-meta > div { padding: 7px 10px; border-right: 1px solid var(--inv-line); border-bottom: 1px solid var(--inv-line); min-width: 0; }
    .inv-meta > div:nth-child(3n) { border-right: 0; }
    .inv-meta span { display: block; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--inv-muted); }
    .inv-meta strong { display: block; font-size: 11.5px; overflow-wrap: anywhere; }
    .inv-meta { margin-bottom: -1px; }

    .inv-parties { display: grid; grid-template-columns: 1.3fr 1fr; gap: 12px; margin-top: 14px; }
    .inv-party { border: 1px solid var(--inv-line); border-radius: 8px; padding: 10px 12px; background: var(--inv-soft); }
    .inv-party h4, .inv-words h4 { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; color: var(--inv-muted); margin-bottom: 4px; }
    .inv-party-name { font-size: 13px; font-weight: 800; }
    .inv-party p { color: var(--inv-ink-2); }

    .inv-items { width: 100%; border-collapse: collapse; margin-top: 14px; font-size: 11px; }
    .inv-items th { background: var(--inv-ink); color: #fff; font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; text-align: left; padding: 7px 8px; }
    .inv-items td { padding: 7px 8px; border-bottom: 1px solid var(--inv-line); vertical-align: top; }
    .inv-items tbody tr:nth-child(even) td { background: var(--inv-soft); }
    .inv-items tfoot td { font-weight: 800; border-top: 1.5px solid var(--inv-ink); border-bottom: 1.5px solid var(--inv-ink); padding: 7px 8px; }
    .inv-items .c-sn { width: 28px; color: var(--inv-muted); text-align: center; }
    .inv-items .c-unit { width: 70px; white-space: nowrap; }
    .inv-items .c-num { text-align: right; white-space: nowrap; width: 80px; }
    .inv-items thead .c-sn { color: #fff; }
    .inv-item-en { display: block; font-weight: 700; }
    .inv-item-gu { display: block; font-size: 10.5px; color: var(--inv-accent); }
    .inv-items tr { page-break-inside: avoid; break-inside: avoid; }

    .inv-summary { display: grid; grid-template-columns: 1fr 250px; gap: 18px; margin-top: 14px; align-items: start; page-break-inside: avoid; break-inside: avoid; }
    .inv-words p { font-weight: 700; font-style: italic; color: var(--inv-ink-2); }
    .inv-words .mt { margin-top: 12px; }
    .inv-words ol { margin: 2px 0 0; padding-left: 16px; color: var(--inv-muted); font-size: 10px; line-height: 1.6; }
    .inv-totals { width: 100%; border-collapse: collapse; border: 1px solid var(--inv-line); border-radius: 8px; overflow: hidden; }
    .inv-totals th { text-align: left; font-weight: 600; color: var(--inv-ink-2); padding: 6px 10px; }
    .inv-totals td { text-align: right; font-weight: 700; padding: 6px 10px; white-space: nowrap; }
    .inv-totals tr + tr th, .inv-totals tr + tr td { border-top: 1px solid var(--inv-line); }
    .inv-totals .is-discount th, .inv-totals .is-discount td { color: var(--inv-accent); }
    .inv-totals .is-grand th, .inv-totals .is-grand td { background: var(--inv-ink); color: #fff; font-size: 13.5px; font-weight: 800; padding: 9px 10px; }
    .inv-totals .is-status th, .inv-totals .is-status td { font-size: 10px; color: var(--inv-muted); }

    .inv-foot { display: flex; justify-content: space-between; align-items: flex-end; gap: 24px; margin-top: 26px; padding-top: 12px; border-top: 1px solid var(--inv-line); font-size: 10px; color: var(--inv-ink-2); page-break-inside: avoid; break-inside: avoid; }
    .inv-foot > div:first-child { max-width: 60%; }
    .inv-foot .inv-muted { margin-top: 4px !important; }
    .inv-sign { text-align: center; min-width: 170px; }
    .inv-sign-line { height: 34px; border-bottom: 1px solid var(--inv-ink-2); margin-bottom: 5px; }
    .inv-sign strong { display: block; font-size: 11px; color: var(--inv-ink); }
    .inv-sign span { font-size: 9.5px; color: var(--inv-muted); }
    .inv-thanks { margin-top: 14px !important; text-align: center; font-size: 9.5px; color: var(--inv-muted); }


    /* Landscape: tighter vertical rhythm so a typical order fits on one A4 sheet */
    .inv-sheet.is-landscape { font-size: 10.5px; line-height: 1.4; }
    .inv-sheet.is-landscape .inv-head { padding-bottom: 10px; }
    .inv-sheet.is-landscape .inv-meta { grid-template-columns: repeat(6, minmax(0, 1fr)); margin-top: 10px; }
    .inv-sheet.is-landscape .inv-meta > div { border-bottom: 0; }
    .inv-sheet.is-landscape .inv-meta > div:nth-child(3n) { border-right: 1px solid var(--inv-line); }
    .inv-sheet.is-landscape .inv-meta > div:last-child { border-right: 0; }
    .inv-sheet.is-landscape .inv-parties, .inv-sheet.is-landscape .inv-items, .inv-sheet.is-landscape .inv-summary { margin-top: 10px; }
    .inv-sheet.is-landscape .inv-items td { padding: 5px 8px; }
    .inv-sheet.is-landscape .inv-party { padding: 8px 12px; }
    .inv-sheet.is-landscape .inv-totals th, .inv-sheet.is-landscape .inv-totals td { padding: 4px 10px; }
    .inv-sheet.is-landscape .inv-foot { margin-top: 14px; padding-top: 8px; }
    .inv-sheet.is-landscape .inv-sign-line { height: 22px; }
    .inv-sheet.is-landscape .inv-thanks { margin-top: 8px !important; }

    @media print { .inv-sheet.is-landscape { zoom: .9; } }

    @media screen and (max-width: 700px) {
        .inv-sheet { padding: 18px 14px; min-height: 0; font-size: 11px; }
        .inv-head, .inv-foot { flex-direction: column; align-items: stretch; }
        .inv-title-block { text-align: left; }
        .inv-ids { margin-left: 0; }
        .inv-ids td { text-align: left; }
        .inv-meta { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .inv-meta > div:nth-child(3n) { border-right: 1px solid var(--inv-line); }
        .inv-meta > div:nth-child(2n) { border-right: 0; }
        .inv-parties, .inv-summary { grid-template-columns: 1fr; }
        .inv-foot > div:first-child { max-width: none; }
    }
</style>
