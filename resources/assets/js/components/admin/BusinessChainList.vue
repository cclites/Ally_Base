<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="6">
            </b-col>
            <b-col lg="6" class="text-right">
                <b-form-input v-model="filter" placeholder="Type to Search" />
            </b-col>
        </b-row>

        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                     :items="items"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     :filter="filter"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     @filtered="onFiltered"
            >
                <template slot="actions" scope="row">
                    <b-btn size="sm" :href="'/admin/chains/' + row.item.id">Edit</b-btn>
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
            'chains': {},
        },

        data() {
            return {
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                filter: null,
                items: this.chains,
                fields: [
                    {
                        key: 'name',
                        label: 'Chain',
                        sortable: true,
                    },
                    {
                        key: 'city',
                        label: 'City',
                        sortable: true,
                    },
                    {
                        key: 'state',
                        label: 'State',
                        sortable: true,
                    },
                    {
                        key: 'phone1',
                        label: 'Phone',
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ],
            }
        },

        mounted() {
            this.totalRows = this.items.length;
        },

        computed: {

        },

        methods: {
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            },
        }
    }
</script>