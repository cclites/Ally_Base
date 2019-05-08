<template>
    <b-card>
        <b-row class="mb-2" align-h="end">
            <b-col md="4" class="text-right">
                <b-form-input v-model="filter" placeholder="Type to Search" />
            </b-col>
        </b-row>

        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                     :items="items"
                     :current-page="currentPage"
                     :per-page="perPage"
                     :filter="filter"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     :fields="fields">
                <template slot="quickbooks" scope="row">
                    <b-form-select v-model="selected">
                        <option value="Do No Match">Do No Match</option>
                    </b-form-select>
                </template>
            </b-table>
        </div>

        <b-row>
            <b-col lg="6" >
                <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" />
            </b-col>
            <b-col lg="6" class="text-right">
                Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    export default {
        props: {
            caregivers: Array,
        },

        data() {
            return {
                active: 'active',
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: 'ally',
                sortDesc: false,
                filter: null,
                selected: 'Do No Match',
                selectedItem: {},
                fields: [
                    {
                        key: 'name',
                        label: 'Ally',
                        sortable: true
                    },
                    {
                        key: 'quickbooks',
                        label: 'QuickBooks',
                        sortable: false
                    },
                ]
            }
        },

        mounted() {
            this.totalRows = this.items.length;
        },

        computed: {
            items() {
                return this.caregivers.map(function(caregiver) {
                    return {
                        id: caregiver.id,
                        name: caregiver.nameLastFirst,
                    }
                });

            },
        },

        methods: {

        }
    }
</script>

<style>

</style>
