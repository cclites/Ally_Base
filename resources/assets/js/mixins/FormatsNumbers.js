import { Decimal } from 'decimal.js';

export default {
    methods: {
        numberFormat(number) {
            return numeral(number).format('0,0.00');
        },
        percentageFormat(number) {
            return numeral(number).format('0.00%');
        },
        moneyFormat( number, sign = '$', dash = false ) {
            if( dash && [ null, 'null' ].includes( number ) ) {
                return '-';
            }
            return sign + this.numberFormat( number );
        },
        makeNegative(number, decimals = 2) {
            return (new Decimal(-1)).times(new Decimal(number)).toFixed(decimals);
        },
    }
}