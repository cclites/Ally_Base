<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="12">
                <b-btn @click="add()" variant="info">Add Payer</b-btn>
            </b-col>
        </b-row>
        <div>
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
                        <!-- We use click.stop here to prevent a 'row-clicked' event from also happening -->
                        <b-btn size="sm" @click="edit(row.item.id)">
                            <i class="fa fa-edit"></i>
                        </b-btn>
                        <b-btn size="sm" @click="destroy(row.item.id)">
                            <i class="fa fa-trash"></i>
                        </b-btn>
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
        </div>
    
        <business-payer-modal 
            @saved="save"
            v-model="showModal" 
            :source="payer">
        </business-payer-modal>
    </b-card>
</template>

<script>
    import FormatsDates from "../../mixins/FormatsDates";
    export default {
        props: {
            payers: {},
        },

        mixins: [FormatsDates],

        data() {
            return {
                items: [],
                payer: {},
                showModal: false,
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: 'name',
                sortDesc: false,
                filter: null,
                fields: [
                    {
                        key: 'name',
                        label: 'Name',
                        sortable: true
                    },
                    {
                        key: 'npi_number',
                        label: 'Account Number',
                        sortable: true,
                    },
                    {
                        key: 'updated_at',
                        label: 'Last Updated',
                        sortable: true,
                        formatter: x => this.formatDateTimeFromUTC(x),
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ]
            }
        },

        mounted() {
            this.items = this.payers;
        },

        computed: {
        },

        methods: {
            add() {
                this.payer = {};
                this.showModal = true;
            },
            
            edit(id) {
                this.payer = this.items.find(x => x.id == id);
                this.showModal = true;
            },

            save(data) {
                let item = this.items.find(x => x.id === data.id);
                if (item) {
                    item.name = data.name;
                    item.npi_number = data.npi_number;
                } else {
                    this.items.push(data);
                }
            },

            destroy(id) {
                if (confirm('Are you sure you want to delete this payer?')) {
                    let form = new Form();
                    form.submit('delete', `/business/payers/${id}`)
                        .then( ({ data }) => {
                            this.items = this.items.filter(x => x.id !== id);
                        });
                }
            },

            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            },
        }
    }
</script>