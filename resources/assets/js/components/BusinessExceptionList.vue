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
    import Form from "../classes/Form";
    import FormatsDates from "../mixins/FormatsDates";

    export default {
        mixins: [FormatsDates],

        props: {
            'exceptions': {
                default() {
                    return [];
                }
            },
            'title': String,
            'hideAcknowledged': Number,
        },

        data() {
            return {
                totalRows: 0,
                perPage: 25,
                currentPage: 1,
                sortBy: 'created_at',
                sortDesc: true,
                filter: null,
                selectedItem: {},
                fields: [
                    {
                        key: 'created_at',
                        label: 'Date',
                        sortable: true,
                        formatter: (val) => this.formatDateTimeFromUTC(val),
                    },
                    {
                        key: 'reference_type',
                        label: "Type",
                        sortable: true,
                        formatter: this.formatType,
                    },
                    {
                        key: 'title',
                        sortable: true,
                    },
                    {
                        key: 'acknowledged_at',
                        label: 'Acknowledged',
                        sortable: true,
                        formatter: (val) => val ? this.formatDateTimeFromUTC(val) : null,
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ],
                items: this.exceptions,

            }
        },

        mounted() {
            if (this.hideAcknowledged) {
                Vue.delete(this.fields, 3);
            }
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

            formatType(str) {
                return str.replace("App\\", "");
            }
        }
    }
</script>