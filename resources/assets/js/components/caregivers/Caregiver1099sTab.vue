<template>
    <b-card header="Tax Documents"
            header-bg-variant="info"
            header-text-variant="white"
    >
        <b-alert show variant="info">
            If you do not see a 1099 listed, your client did not elect provide you with one. This is common and okay.
            In that case, to report your income, please see your
            <a href="/caregiver/deposits">Year Summary Report on your Pay Statements Screen</a> and fill out a
            <a href="https://www.irs.gov/pub/irs-pdf/f1040sc.pdf" target="_blank">Schedule C</a> from the IRS.
        </b-alert>
        <div v-if="items.length" class="table-responsive">
            <b-table
                    bordered striped hover show-empty
                    :items="items"
                    :fields="fields"
                    sort-by="year"
            >
                <template slot="actions" scope="row">
                    <a :href="`/caregiver/caregiver-1099/download/${row.item.id}`">Download 1099</a>
                </template>
            </b-table>
        </div>
        <div v-else>
            There are no records to display.
        </div>

        <hr>
        <b-alert show variant="info">Note: 2018 and prior years would have been mailed to you and are not available
            electronically.
        </b-alert>
    </b-card>
</template>

<script>
    export default {
        data() {
            return {
                items: [],
                fields: {
                    year: {sortable: true},
                    payer: {label: 'Payer', sortable: true},
                    client: {label: 'For Client(s)', sortable: true},
                    actions: {sortable: false},
                },
            }
        },

        mounted() {
            this.fetch();
        },

        methods: {
            fetch() {
                axios.get('/caregiver/caregiver-1099')
                    .then(response => {
                        this.items = response.data;
                    })
                    .catch(e => {
                    })
                    .finally(() => {
                    });
            }
        }
    }
</script>
