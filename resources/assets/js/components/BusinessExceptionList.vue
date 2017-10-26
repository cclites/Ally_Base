<template>
    <b-card
        :header="title"
        header-text-variant="white"
        header-bg-variant="info"
        >
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
                    <b-btn size="sm" :href="'/business/exceptions/' + row.item.id">Details</b-btn>
                </template>
            </b-table>
        </div>
    </b-card>
</template>

<script>
    import Form from "../classes/Form";

    export default {
        props: {
            'exceptions': {
                default() {
                    return [];
                }
            },
        },

        data() {
            return {
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                filter: null,
                selectedItem: {},
                fields: [
                    {
                        key: 'date',
                        label: 'Date',
                        sortable: true,
                    },
                    {
                        key: 'title',
                        label: 'Title',
                        sortable: true,
                    },
                    {
                        key: 'acknowledged_at',
                        label: 'Acknowledged',
                        sortable: true,
                    },
                    'actions'
                ],
                items: this.exceptions.map(function(exception) {
                    exception.date = moment.utc(exception.created_at).local().format('L LT');
                    exception.acknowledged_at = (exception.acknowledged_at) ? moment.utc(exception.acknowledged_at).local().format('L LT') : '';
                    return exception;
                }),

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
            }
        }
    }
</script>