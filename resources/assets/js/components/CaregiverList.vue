<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="12">
                <a href="/business/caregivers/create" class="btn btn-info">Add Caregiver</a>
            </b-col>
        </b-row>
        <b-row class="mb-2">
            <b-col lg="12">
                <div class="d-flex flex-md-row flex-sm-column justify-content-between align-items-start">
                    <business-location-select v-model="filters.business" :allow-all="true" :hideable="false" class="f-1 mr-2"></business-location-select>

                    <b-form-select v-model="filters.status" class="f-1 mr-2">
                        <option value="">All Caregivers</option>
                        <option value="active">Active Caregivers</option>
                        <option value="inactive">Inactive Caregivers</option>
                        <option v-for="status in statuses.caregiver" :key="status.id" :value="status.id">
                            {{ status.name }}
                        </option>
                    </b-form-select>

                    <b-form-input v-model="filters.search" placeholder="Type to Search" class="f-1" />
                </div>
            </b-col>
        </b-row>

        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                     :items="itemProvider"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     :busy="loading"
                     ref="table"
            >
                <template slot="primaryPhone" scope="row">
                    {{ getPhone(row.item.caregiver) }}
                </template>
                <template slot="city" scope="row">
                    {{ row.item.city ? row.item.city : '-' }}
                </template>
                <template slot="zipcode" scope="row">
                    {{ row.item.zipcode ? row.item.zipcode : '-' }}
                </template>
                <template slot="location" scope="data">
                    <div v-for="(business, index) in getBusinessNames(data.item.caregiver)" :key="index">
                        {{ business }}
                    </div>
                </template>
                <template slot="actions" scope="row">
                    <!-- We use click.stop here to prevent a 'row-clicked' event from also happening -->
                    <b-btn size="sm" :href="'/business/caregivers/' + row.item.id">
                        <i class="fa fa-edit"></i>
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
    </b-card>
</template>

<script>
    import BusinessLocationSelect from "./business/BusinessLocationSelect";
    import FormatsListData from "../mixins/FormatsListData";
    import LocalStorage from "../mixins/LocalStorage";

    export default {
        mixins: [FormatsListData, LocalStorage],
        components: {BusinessLocationSelect},

        props: {},

        data() {
            return {
                totalRows: 0,
                perPage: 25,
                currentPage: 1,
                sortBy: 'lastname',
                sortDesc: false,
                editModalVisible: false,
                modalDetails: { index:'', data:'' },
                selectedItem: {},
                caregivers: [],
                fields: [
                    {
                        key: 'firstname',
                        label: 'First Name',
                        sortable: true,
                    },
                    {
                        key: 'lastname',
                        label: 'Last Name',
                        sortable: true,
                    },
                    {
                        key: 'email',
                        label: 'Email Address',
                        sortable: true,
                        formatter: this.formatEmail,
                    },
                    {
                        key: 'primaryPhone',
                        label: 'Primary Phone',
                        sortable: false,
                    },
                    {
                        key: 'city',
                        label: 'City',
                        sortable: true,
                    },
                    {
                        key: 'zipcode',
                        label: 'Zip Code',
                        sortable: true,
                    },
                    {
                        key: 'location',
                        label: 'Location',
                        sortable: false,
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ],
                loading: false,
                statuses: {caregiver: [], client: []},
                filters: new Form({
                    business: '',
                    status: '',
                    search: '',
                }),
                localStoragePrefix: 'caregiver_list_',
            }
        },

        async mounted() {
            await this.fetchStatusAliases();
            this.loadTable();
        },

        computed: {
            listUrl() {

                let active = '';
                let aliasId = '';

                this.loadFiltersFromStorage();

                if (this.filters.status === '') {
                    active = '';
                } else if (this.filters.status === 'active') {
                    active = 1;
                } else if (this.filters.status === 'inactive') {
                    active = 0;
                } else {
                    aliasId = this.filters.status;
                    let alias = this.statuses.caregiver.find(x => x.id == this.filters.status);
                    if (alias) {
                        aliasId = alias.id;
                        active = alias.active;
                    }
                }

                return `/business/caregivers/paginate?search=${this.filters.search}&active=${active}&status=${aliasId}&businesses=${this.filters.business}`;
            },
        },

        methods: {
            loadTable() {
                this.$refs.table.refresh();
            },

            itemProvider(ctx) {
                this.loading = true;

                let sort = ctx.sortBy == null ? '' : ctx.sortBy;
                return axios.get(this.listUrl + `&page=${ctx.currentPage}&perpage=${ctx.perPage}&sort=${sort}&desc=${ctx.sortDesc}`)
                    .then( ({ data }) => {
                        this.totalRows = data.total;
                        return data.results || [];
                    })
                    .catch(() => {
                        return [];
                    })
                    .finally(() => {

                        this.loading = false;
                    });
            },

            getAddress(caregiver)
            {
                if (caregiver.address) {
                    return caregiver.address;
                }
                return { city: '-', zip: '-' };
            },

            getPhone(caregiver)
            {
                if (caregiver.phone_number) {
                    return caregiver.phone_number.number;
                }
                return '-';
            },

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

            getBusinessNames(caregiver) {
                return caregiver.businesses.map(x => x.name);
            },

            async fetchStatusAliases() {
                axios.get(`/business/status-aliases`)
                    .then( ({ data }) => {
                        if (data && data.caregiver) {
                            this.statuses = data;
                        } else {
                            this.statuses = {caregiver: [], client: []};
                        }
                    })
                    .catch(() => {})
            },
            loadFiltersFromStorage() {
                if (typeof(Storage) !== "undefined") {
                    // Saved filters
                    for (let filter of Object.keys(this.filters)) {
                        let value = this.getLocalStorage(filter);
                        if (value) this.filters[filter] = value;
                    }
                    // Sorting/show UI
                    let sortBy = this.getLocalStorage('sortBy');
                    if (sortBy) this.sortBy = sortBy;
                    let sortDesc = this.getLocalStorage('sortDesc');
                    if (sortDesc === false || sortDesc === true) this.sortDesc = sortDesc;
                }
            },
            updateSavedFormFilters() {
                for (let filter of Object.keys(this.filters)) {
                    this.setLocalStorage(filter, this.filters[filter]);
                }
            },

            updateSortOrder(){
                this.setLocalStorage('sortBy', this.sortBy);
            }
        },

        watch: {
            'filters.status'(newVal, oldVal) {
                if (newVal != oldVal) {
                    this.updateSavedFormFilters();
                    this.$refs.table.refresh();
                }
            },

            'filters.business'(newVal, oldVal) {
                if (newVal != oldVal) {
                    this.updateSavedFormFilters();
                    this.$refs.table.refresh();
                }
            },

            'filters.search'(newVal, oldVal) {
                // debounce the reloading of the table to prevent
                // unnecessary calls.
                _.debounce(() => {
                    this.updateSavedFormFilters();
                    this.$refs.table.refresh();
                }, 350)();
            },

            sortBy() {
                this.updateSortOrder();
            }
        },
    }
</script>
