<div class="calc-summary">
    <div class="calc-summary__hero">
        <div class="calc-summary__label">Ընդհանուր արժեքը</div>
        <div class="calc-summary__total">{{ $data['total_price_fiz'] ?? '—' }}</div>
        <div class="calc-summary__note">{{ $data['year_text'] ?? '' }}</div>
    </div>

    <div class="calc-summary__grid">
        <section class="calc-card">
            <header class="calc-card__header">Ռաստամոժկա</header>
            <ul class="calc-list">
                <li>
                    <span>Մեքենայի գին</span>
                    <strong>{{ $data['price'] ?? '—' }}</strong>
                </li>
                <li>
                    <span>Մաքսային վճար</span>
                    <strong>{{ $data['customs_clearance'] ?? '—' }}</strong>
                </li>
                <li>
                    <span>ԱԱՀ</span>
                    <strong>{{ $data['vat'] ?? '—' }}</strong>
                </li>
                <li>
                    <span>Շրջ. հարկ</span>
                    <strong>{{ $data['environmental'] ?? '—' }}</strong>
                </li>
            </ul>
            <div class="calc-card__footer">
                <span>Ինքնաարժեք</span>
                <strong>{{ $data['total_price1'] ?? '—' }}</strong>
            </div>
        </section>

        <section class="calc-card">
            <header class="calc-card__header">Առաքում</header>
            <ul class="calc-list">
                <li>
                    <span>Ընդհանուր (առաքում)</span>
                    <strong>{{ $data['total'] ?? '—' }}</strong>
                </li>
                <li>
                    <span>Ապահովագրություն</span>
                    <strong>{{ $data['insurance_price'] ?? '—' }}</strong>
                </li>
                <li>
                    <span>Տրանսպորտ ԱՄՆ</span>
                    <strong>{{ $data['auction_location_price'] ?? '—' }}</strong>
                </li>
            </ul>
            <div class="calc-card__footer">
                <span>Ինքնաարժեք</span>
                <strong>{{ $data['total_price2'] ?? '—' }}</strong>
            </div>
        </section>
    </div>
</div>

<style>
.calc-summary {
    background: #f5f7fb;
    padding: 24px;
    border-radius: 18px;
    box-shadow: 0 10px 35px rgba(0,0,0,0.08);
}
.calc-summary__hero {
    background: linear-gradient(135deg, #1c8adb, #3ac6f7);
    color: #fff;
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 18px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.calc-summary__label { font-size: 14px; opacity: 0.9; letter-spacing: 0.3px; }
.calc-summary__total { font-size: 32px; font-weight: 700; line-height: 1.2; }
.calc-summary__note { font-size: 13px; opacity: 0.85; }

.calc-summary__grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
}
.calc-card {
    background: #fff;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 6px 22px rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.calc-card__header {
    font-weight: 700;
    font-size: 15px;
    color: #1f2a3d;
}
.calc-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.calc-list li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #2b3c4f;
    font-size: 14px;
    padding: 8px 10px;
    border: 1px solid #eef1f6;
    border-radius: 10px;
    background: #fafbfe;
}
.calc-list li span { color: #4c5b6d; }
.calc-list li strong { color: #0c2c63; font-weight: 700; }

.calc-card__footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 12px;
    border-radius: 10px;
    background: #edf4ff;
    color: #0c2c63;
    font-weight: 700;
}
@media (max-width: 576px) {
    .calc-summary { padding: 16px; }
    .calc-summary__total { font-size: 26px; }
}
</style>
