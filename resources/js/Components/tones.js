// Single owner for ledger chip tones: status, payment method, account.
export const statusTone = (value) => {
    switch ((value || '').toLowerCase()) {
        case 'confirmed':
        case 'booked':
            return 'green';
        case 'pending':
        case 'new':
            return 'amber';
        case 'contacted':
            return 'blue';
        case 'cancelled':
        case 'closed':
            return 'gray';
        default:
            return 'gray';
    }
};

export const methodTone = (value) => {
    switch ((value || '').toLowerCase()) {
        case 'online':
            return 'blue';
        case 'cash':
            return 'gray';
        default:
            return 'gray';
    }
};

export const accountTone = (hasAccount) => (hasAccount ? 'blue' : 'gray');
