<template>
    <b-card header="Tax Documents"
            header-bg-variant="info"
            header-text-variant="white"
    >
        <b-alert show variant="info">
            1099 totals do not include expenses, mileage, deductions, adjustments, etc.
        </b-alert>
        <div v-if="items.length" class="table-responsive">
            <b-table
                    bordered striped hover show-empty
                    :items="items"
                    :fields="fields"
                    sort-by="year"
            >
                <template slot="actions" scope="row">
                    <a :href="`/client/client-1099/download/${row.item.id}`">Download 1099</a>
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
                    caregiver: {label: 'Caregiver', sortable: true},
                    actions: {sortable: false},
                },
            }
        },

        mounted(){
            this.fetch();
        },

        methods:{
            fetch(){
                axios.get('/client/client-1099')
                    .then(response => {
                        this.items = response.data;
                    })
                    .catch( e => {})
                    .finally(() => {});
            }
        }
    }
</script>

<style scoped>

</style>