<template>
    <div>
        <b-card header="Pay Statements"
                header-bg-variant="info"
                header-text-variant="white"
        >
            <caregiver-deposit-history :caregiver="caregiver" :deposits="deposits" :urlGenerator="depositUrl" />
        </b-card>
    </div>
</template>

<script>
    export default {
        props: ['caregiver', 'deposits'],

        data() {
            return {
                fields: [
                    { key: 'created_at', label: 'Paid' },
                    { key: 'week', label: 'Shifts Added'},
                    { key: 'success', label: 'Deposit Status' },
                    {
                        key: 'amount',
                        formatter: (value) => { return this.moneyFormat(value); }
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ]
            }
        },

        methods: {
            depositUrl(deposit, view="") {
                return `/business/statements/deposits/${deposit.id}/${view}`;
            }
        },

        computed: {
        }
    }
</script>
