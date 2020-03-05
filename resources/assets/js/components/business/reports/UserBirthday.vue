<template>
    <b-card :title="`${type} Birthday`">
        <b-row class="mb-2">
            <b-col md="3">
                <business-location-form-group
                        v-model="search.businesses"
                        :allow-all="true"
                        class="mb-2 mr-2"
                />
            </b-col>
            <b-col md="3">
                <b-form-group v-if="type === 'Clients'" label="Clients">
                    <b-form-select v-model="search.selectedId" ref="clientFilter" class="w-100">
                        <option value="All">All Clients</option>
                        <option v-for="item in clients" :value="item.id" :key="item.id">{{ item.name }}</option>
                    </b-form-select>
                </b-form-group>
                <b-form-group v-else label="Caregiver">
                    <b-form-select v-model="search.selectedId" ref="caregiverFilter" class="w-100">
                        <option value="All">All Caregivers</option>
                        <option v-for="item in caregivers" :value="item.id" :key="item.id">{{ item.name }}</option>
                    </b-form-select>
                </b-form-group>
            </b-col>
            <b-col v-if="type === 'Clients'" md="3">
                <b-form-group label="Client Type" class="form-inline">
                    <client-type-dropdown ref="clientTypeFilter" v-model="search.client_type"/>
                </b-form-group>
            </b-col>
            <b-col md="3" class="mt-4">
                <b-button @click="fetch()" variant="info" :disabled="loading">Generate Report</b-button>
            </b-col>
        </b-row>
        <b-row class="mb-2">
            <b-col cols="4">
                <b-form-checkbox v-model="showEmpty" :value="true" :unchecked-value="false">
                    Show {{type}} without birthdays
                </b-form-checkbox>
            </b-col>
            <b-col cols="4">
                <b-form-checkbox v-model="search.show_inactive" :value="true" :unchecked-value="false">
                    Show inactive {{type}}
                </b-form-checkbox>
            </b-col>
        </b-row>
        <b-row class="mb-2">
            <b-col lg="3">
                <b-form-checkbox v-model="showDateRange" :value="true" :unchecked-value="false" class="d-flex align-items-center">
                    Filter by {{type}} birthday
                </b-form-checkbox>
            </b-col>
            <b-col lg="3" v-show="showDateRange" >
                <b-form-group label="Upcoming Birthdays" class="f-1 mx-1">

                    <b-form-input v-model="search.days" placeholder="Birthdays in next xx days" type="number"/>
                </b-form-group>
            </b-col>

        </b-row>

        <loading-card v-if="loading"/>

        <div v-else class="table-responsive">
            <ally-table id="user-birthday" :columns="fields" :items="items" :per-page="50" sort-by="name">
                <template slot="name" scope="data">
                    <a :href="`/business/${type.toLowerCase()}/${data.item.id}`">{{ data.item.name }}</a>
                </template>
                <template slot="date_of_birth" scope="data">
                    {{ data.item.formatted_date }}
                </template>
            </ally-table>
        </div>
    </b-card>
</template>

<script>
    import FormatsDates from '../../../mixins/FormatsDates';
    import BusinessLocationFormGroup from '../../../components/business/BusinessLocationFormGroup';
    import {mapGetters} from "vuex";

    export default {
        mixins: [FormatsDates],
        components: { BusinessLocationFormGroup },

        props: {
            type: {
                type: String,
                required: true,
            },
        },

        data() {
            return {
                loading: false,
                showEmpty: true,
                showDateRange: false,
                search: new Form({
                    filterDates: 0,
                    selectedId: 'All',
                    type: this.type,
                    json: 1,
                    businesses: '',
                    client_type: '',
                    show_inactive: false,
                    days: ''
                }),
                data: [],
                fields: [
                    {
                        key: 'name',
                        label: 'Name',
                        sortable: true,
                        shouldShow: true,
                    },
                    {
                        key: 'date_of_birth',
                        label: 'Birthday',
                        sortable: true,
                        shouldShow: true,
                    },
                    {
                        key: 'street_address',
                        label: 'Address',
                        sortable: true,
                        shouldShow: true,
                    },
                    {
                        key: 'city',
                        label: 'City',
                        sortable: true,
                        shouldShow: true,
                    },
                    {
                        key: 'state',
                        label: 'State',
                        sortable: true,
                        shouldShow: true,
                    },
                    {
                        key: 'zip',
                        label: 'Zip',
                        sortable: true,
                        shouldShow: true,
                    },
                    {
                        key: 'phone',
                        label: 'Phone',
                        sortable: true,
                        shouldShow: true,
                    },
                ],
            };
        },

        async mounted() {
            this.$store.commit('filters/setBusiness', this.search.businesses);
            await this.$store.dispatch('filters/fetchResources', ['clients', 'caregivers']);
        },

        computed: {
            ...mapGetters({
                clientList: 'filters/clientList',
                caregiverList: 'filters/caregiverList',
            }),

            items() {
                return this.data.filter(user =>
                    this.showEmpty || !!user.date_of_birth
                ).map((item) => {
                    return {
                        ...item,
                        formatted_date: item.date_of_birth ? moment(item.date_of_birth).format('MM/DD/YYYY') : '-',
                        date_of_birth: moment(item.date_of_birth).format('MMDD'),
                    };
                });
            },

            clients() {
                return this.clientList.filter(x => {
                    if (this.search.client_type) {
                        if (x.client_type != this.search.client_type) {
                            return false;
                        };
                    }

                    if (! this.search.show_inactive) {
                        return x.active == 1;
                    }

                    return true;
                });
            },

            caregivers() {
                if (this.search.show_inactive) {
                    return this.caregiverList;
                }

                return this.caregiverList.filter(x => x.active == 1);
            },
        },

        methods: {
            async fetch() {
                this.loading = true;
                if (this.showDateRange) {
                    this.search.filterDates = 1;
                }

                this.search.get(`/business/reports/birthdays`)
                    .then(({ data }) => {
                        this.data = data;
                        this.search.filterDates = 0;
                        this.loading = false;
                    })
                    .catch (() => {})
                    .finally(() => {
                        this.loading = false;
                    });
            },
        },

        watch: {
            'search.businesses'(newVal) {
                this.$store.commit('filters/setBusiness', newVal);
            },
        },
    }
</script>

<style scoped>
    .filter {
        margin: 20px 0;
    }
</style>

