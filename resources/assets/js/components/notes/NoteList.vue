<template>
    <b-card>
        <b-form inline @submit.prevent="filter" class="mb-4">
            <a href="/notes/create" class="btn btn-info mr-5 mb-2">Add Note</a>

            <b-form-input
                    type="text"
                    id="start-date"
                    class="datepicker mr-2 mb-2"
                    v-model="searchForm.start_date"
                    placeholder="Start Date"
                    @change="filter"
            >
            </b-form-input>

            <b-form-input
                    type="text"
                    id="end-date"
                    class="datepicker mr-2 mb-2"
                    v-model="searchForm.end_date"
                    placeholder="End Date"
            >
            </b-form-input>

            <b-form-select v-model="searchForm.caregiver" class="mr-2 mb-2">
                <template slot="first">
                    <!-- this slot appears above the options from 'options' prop -->
                    <option :value="null">-- Caregiver --</option>
                </template>
                <option :value="caregiver.id" v-for="caregiver in caregivers" :key="caregiver.id">{{ caregiver.name }}</option>
            </b-form-select>

            <b-form-select v-model="searchForm.client" class="mr-2 mb-2">
                <template slot="first">
                    <!-- this slot appears above the options from 'options' prop -->
                    <option :value="null">-- Client --</option>
                </template>
                <option :value="client.id" v-for="client in clients" :key="client.id">{{ client.name }}</option>
            </b-form-select>

            <b-form-input
                type="text"
                id="tags"
                v-model="searchForm.tags"
                class="mr-2 mb-2"
                placeholder="Tags">
            </b-form-input>

            <b-button variant="info" class="mb-2">
                Filter
            </b-button>

        </b-form>

        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                     :items="items"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     :sort-by.sync="sortBy"
                     @filtered="onFiltered"
            >
                <template slot="created_at" scope="data">
                    {{ createdAt(data) }}
                </template>
                <template slot="caregiver" scope="data">
                    <span v-if="data.item.caregiver">{{ data.item.caregiver.name }}</span>
                </template>
                <template slot="client" scope="data">
                    <span v-if="data.item.client">{{ data.item.client.name }}</span>
                </template>
                <template slot="action" scope="data">
                    <a class="btn btn-secondary" :href="'/notes/' + data.item.id + '/edit'">
                        <i class="fa fa-edit"></i>
                    </a>
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

    import moment from 'moment';

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
                    },
                    'action'
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
            caregivers() {
                return _.sortBy(this.business.caregivers, ['name']);
            },
            clients() {
                return _.sortBy(this.business.clients, ['name']);
            }
        },

        methods: {
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            },
            createdAt(data) {
                return moment(data.item.created_at).format('L');
            },
            filter() {
                axios.post('/notes/search', this.searchForm)
                    .then(response => {
                        this.items = response.data;
                    }).catch(error => {
                        console.error(error.response);
                    });
            }
        }
    }
</script>
