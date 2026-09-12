import { computed } from 'vue';

// Shared pax→price tier shape for the package editor and cards.
// One package row, many client cards: tiers are data, never split rows.
export const tiersFromPackage = (pkg) => {
    const map = pkg.pax_prices ?? {};
    const entries = Object.entries(map);
    if (entries.length) {
        return entries.map(([pax, price]) => ({ pax: String(pax), price: String(price ?? '') }));
    }
    const legacy = pkg.pax_options ?? [];
    if (legacy.length) {
        return legacy.map((pax) => ({ pax: String(pax), price: '' }));
    }
    return [{ pax: '', price: '' }];
};

export const validTiers = (tiers) => (tiers ?? []).filter((tier) => String(tier.pax ?? '').trim() !== '');

export const tiersToMap = (tiers) => {
    const map = {};
    (tiers ?? []).forEach((tier) => {
        if (String(tier.pax ?? '').trim() !== '' && String(tier.price ?? '').trim() !== '') {
            map[String(tier.pax).trim()] = tier.price;
        }
    });
    return map;
};

export const useStartsAt = (tiersSource) => computed(() => {
    const tiers = typeof tiersSource === 'function' ? tiersSource() : tiersSource.value;
    const prices = (tiers ?? [])
        .map((tier) => parseFloat(tier.price))
        .filter((price) => !isNaN(price) && price >= 0);
    return prices.length ? Math.min(...prices) : null;
});

export const formatMoney = (value, prefix = '') => {
    return prefix + Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
