export default {
    methods: {
        numberFormat(number) {
            return numeral(number).format('0,0.00');
        },
        percentageFormat(number) {
            return numeral(number).format('0.00%');
        },
        moneyFormat( number, sign = '$', dash = false ) {

            if( dash && [ null, 'null' ].includes( number ) ) return '-';
            return sign + this.numberFormat( number );
        }
    }
}