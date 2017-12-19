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
                     :items="shifts"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     :filter="filter"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     @filtered="onFiltered"
            >
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
    import FormatsDates from '../mixins/FormatsDates';
    import FormatsNumbers from '../mixins/FormatsNumbers';

    export default {
        props: {
            'shifts': {
                default() {
                    return [];
                }
            },
        },

        mixins: [FormatsDates, FormatsNumbers],

        data() {
            return {
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                editModalVisible: false,
                filter: null,
                modalDetails: { index:'', data:'' },
                selectedItem: {},
                fields: [
                    {
                        key: 'client_name',
                        label: 'Client',
                        sortable: true
                    },
                    {
                        key: 'checked_in_time',
                        label: 'Clocked In',
                        formatter: (value) => { return this.formatDateTimeFromUTC(value); },
                        sortable: true
                    },
                    {
                        key: 'checked_out_time',
                        label: 'Clocked Out',
                        formatter: (value) => { return this.formatDateTimeFromUTC(value); },
                        sortable: true
                    },
                    {
                        key: 'duration',
                        label: 'Hours',
                        sortable: true
                    },
                    {
                        key: 'caregiver_rate',
                        label: 'Rate',
                        formatter: (value) => { return this.moneyFormat(value) },
                        sortable: true
                    },
                    {
                        key: 'activity_names',
                        label: 'Activities'
                    },
                    {
                        key: 'verified',
                        label: 'Verified',
                        sortable: true,
                        formatter: (value) => { return value ? 'Verified' : 'Unverified'; }
                    },
                ]
            }
        },

        mounted() {
            this.totalRows = this.items.length;
        },

        methods: {
            details(item, index, button) {
                this.selectedItem = item;
                this.modalDetails.data = JSON.stringify(item, null, 2);
                this.modalDetails.index = index;
//                this.$root.$emit('bv::show::modal','caregiverEditModal', button);
                this.editModalVisible = true;
            },
            resetModal() {
                this.modalDetails.data = '';
                this.modalDetails.index = '';
            },
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            }
        }
    }
</script>
