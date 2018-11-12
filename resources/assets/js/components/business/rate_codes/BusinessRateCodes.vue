<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="3">
                <b-btn @click="addCode()" variant="info">Add Rate Code</b-btn>
            </b-col>
            <b-col lg="5" md="2"></b-col>
            <b-col lg="4" class="text-right">
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
                    <b-btn size="sm" @click="editCode(row.item)"><i class="fa fa-edit"></i></b-btn>
                    <b-btn size="sm" variant="danger" @click="deleteCode(row.item)"><i class="fa fa-times"></i></b-btn>
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

        <rate-code-modal v-model="rateCodeModal" :code="selectedItem" @saved="updateList" />
    </b-card>
</template>

<script>
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsStrings from "../../../mixins/FormatsStrings";
    import RateCodeModal from "./RateCodeModal";

    export default {
        name: "BusinessRateCodes",
        components: {RateCodeModal},
        mixins: [FormatsNumbers, FormatsStrings],

        props: {
            'rateCodes': {
                type: Array,
                default() {
                    return [];
                }
            },
        },

        data() {
            return {
                items: this.rateCodes,
                rateCodeModal: false,
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: 'name',
                sortDesc: false,
                editModalVisible: false,
                filter: null,
                modalDetails: { index:'', data:'' },
                selectedItem: {},
                fields: [
                    {
                        key: 'name',
                        label: 'Name',
                        sortable: true,
                    },
                    {
                        key: 'type',
                        label: 'Type',
                        sortable: true,
                    },
                    {
                        key: 'rate',
                        label: 'Rate',
                        sortable: true,
                    },
                    {
                        key: 'fixed',
                        label: 'Method',
                        sortable: true,
                        formatter: val => val ? 'Fixed' : 'Hourly',
                    },
                   'actions'
                ]
            }
        },

        mounted() {
            this.totalRows = this.items.length;
        },

        methods: {
            addCode() {
                this.selectedItem = {};
                this.rateCodeModal = true;
            },
            editCode(item) {
                this.selectedItem = item;
                this.rateCodeModal = true;
            },
            async deleteCode(item) {
                if (confirm('Are you sure you wish to delete this rate code?')) {
                    const id = item.id;
                    const form = new Form;
                    await form.submit('delete', '/business/rate-codes/' + id);
                    this.items = this.items.filter(item => item.id != id);
                }
            },
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            },
            updateList(code) {
                let index = this.items.findIndex(item => item.id == code.id);
                if (index === -1) {
                    this.items.push(code);
                }
                else {
                    Vue.set(this.items, index, code);
                }
            }
        }
    }
</script>

<style scoped>

</style>