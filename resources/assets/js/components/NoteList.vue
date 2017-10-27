<template>
    <b-card>
        <b-row class="mb-3">
            <b-col lg="2">
                <b-form-input
                        type="text"
                        id="start-date"
                        class="datepicker"
                        v-model="searchForm.start_date"
                        placeholder="Start Date"
                >
                </b-form-input>
            </b-col>

            <b-col lg="2">
                <b-form-input
                        type="text"
                        id="end-date"
                        class="datepicker"
                        v-model="searchForm.end_date"
                        placeholder="End Date"
                >
                </b-form-input>
            </b-col>

            <b-col lg="2" class="text-right">
                <b-form-select v-model="searchForm.caregiver" class="mb-3">
                    <template slot="first">
                        <!-- this slot appears above the options from 'options' prop -->
                        <option :value="null" disabled>-- Caregiver --</option>
                    </template>
                    <option :value="caregiver.id" v-for="caregiver in business.caregivers">{{ caregiver.name }}</option>
                </b-form-select>
            </b-col>

            <b-col lg="2" class="text-right">
                <b-form-select v-model="searchForm.client" class="mb-3">
                    <template slot="first">
                        <!-- this slot appears above the options from 'options' prop -->
                        <option :value="null" disabled>-- Client --</option>
                    </template>
                    <option :value="client.id" v-for="client in business.clients">{{ client.name }}</option>
                </b-form-select>
            </b-col>

            <b-col lg="2">
                <b-form-input
                    type="text"
                    id="tags"
                    v-model="searchForm.tags"
                    placeholder="Tags">
                </b-form-input>
            </b-col>

            <b-col lg="2">
                <b-button >
                    Filter
                </b-button>
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
                     @filtered="onFiltered"
            >
                <template slot="caregiver" scope="data">
                    {{ data.item.caregiver.name }}
                </template>
                <template slot="client" scope="data">
                    {{ data.item.client.name }}
                </template>
            </b-table>
        </div>

        <b-row>
            <b-col lg="6">
                <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage"/>
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
            'business': Object,
        },

        data() {
            return {
                items: this.business.notes,
                searchForm: {
                    caregiver: null,
                    client: null,
                    tags: ''
                },
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: null,
                filter: null,
                selectedItem: {},
                fields: [
                    {
                        key: 'created_at',
                        label: 'Note Date',
                        sortable: true,
                    },
                    {
                        key: 'caregiver',
                        label: 'Caregiver',
                        sortable: true,
                    },
                    {
                        key: 'client',
                        label: 'Client',
                        sortable: true,
                    },
                    {
                        key: 'tags',
                        label: 'Tags',
                        sortable: true,
                    },
                    {
                        key: 'body',
                        label: 'Preview',
                        sortable: false
                    }
                ]
            }
        },

        mounted() {
            this.totalRows = this.items.length;

            let startDate = jQuery('#start-date');
            let endDate = jQuery('#end-date');
            let component = this;
            startDate.datepicker({
                forceParse: false,
                autoclose: true,
                todayHighlight: true
            }).on("changeDate", function () {
                component.searchForm.start_date = startDate.val();
            });
            endDate.datepicker({
                forceParse: false,
                autoclose: true,
                todayHighlight: true
            }).on("changeDate", function () {
                component.searchForm.end_date = endDate.val();
            });

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
