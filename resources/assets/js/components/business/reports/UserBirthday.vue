<template>

    <b-card :title="`${type} Birthday`">

        <b-row class="filter justify-content-between align-items-center">
            <b-col cols="3">
                <b-form-checkbox v-model="showEmpty" :value="true" :unchecked-value="false" class="d-flex align-items-center">
                    Show {{type}} without birthdays
                </b-form-checkbox>
            </b-col>
            <b-col cols="3" v-if="type === 'Clients'">
                <label>Client Types:
                    <b-form-select class="form-group-label " v-model="search.selectedClients">
                        <option value="All">All</option>
                        <option v-for="option in clientTypes" :value="option" :key="option.id">{{ option }}</option>
                    </b-form-select>
                </label>
            </b-col>

            <b-col cols="3" v-if="type === 'Caregivers'">
                <b-form-group label="Caregiver">
                    <b-form-select v-model="search.selectedId">
                        <option value="All">All</option>
                        <option v-for="caregiver in caregiverList" :value="caregiver.id">{{
                            caregiver.name }}
                        </option>
                    </b-form-select>
                </b-form-group>
            </b-col>
            <b-col cols="3" v-else>
                <b-form-group label="Clients">
                    <b-form-select v-model="search.selectedId">
                        <option value="All">All</option>
                        <option v-for="client in clientList" :value="client.id">{{
                            client.name }}
                        </option>
                    </b-form-select>
                </b-form-group>
            </b-col>
            <b-button @click=" fetch() " variant="info">Generate Report</b-button>
        </b-row>

        <b-row class="filter">
            <b-col lg="3">
                <b-form-checkbox v-model="showInactive" :value="true" :unchecked-value="false" class="d-flex align-items-center" v-if="type === 'Clients'">
                    Show inactive {{type}}
                </b-form-checkbox>
            </b-col>
            <b-col lg="3">
                <business-location-form-group
                        v-model="search.businesses"
                        :allow-all="true"
                        class="mb-2 mr-2"
                        :label="null"
                />
            </b-col>
        </b-row>

        <b-row class="filter">
            <b-col lg="3">
                <b-form-checkbox v-model="showDateRange" :value="true" :unchecked-value="false" class="d-flex align-items-center">
                    Filter by {{type}} birthday
                </b-form-checkbox>
            </b-col>

            <b-col lg="3" v-show="showDateRange">
                <b-form-group label="Start Date">
                    <date-picker
                            class="mb-1"
                            name="start_date"
                            v-model="search.start_date"
                            placeholder="Start Date"
                    ></date-picker>
                </b-form-group>
            </b-col>

            <b-col lg="3" v-show="showDateRange">
                <b-form-group label="End Date">
                    <date-picker
                            class="mb-1"
                            v-model="search.end_date"
                            name="end_date"
                            placeholder="End Date"
                    ></date-picker>
                </b-form-group>
            </b-col>
        </b-row>

        <loading-card v-show="loading"/>
        <div v-show="! loading" class="table-responsive">
            <ally-table id="user-birthday" :columns="fields" :items="items" sort-by="nameLastFirst">
                <template slot="name" scope="data">
                    <a :href="`/business/${type}s/${data.item.id}`">{{ data.item.nameLastFirst }}</a>
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

    export default {
        mixins: [FormatsDates],
        components: { BusinessLocationFormGroup },

        props: {
            type: {
                type: String,
                required: true,
            },
            clientTypes: {
                type: Array,
                required: false
            },
            caregiverList: {
                type: Array,
                required: true
            },
            clientList: {
                type: Array,
                required: true
            }

        },

        data() {
            return {
                loading: false,
                showEmpty: true,
                showInactive: true,
                showDateRange: false,
                search: {
                    start_date: moment().format('MM/DD/YYYY'),
                    end_date: moment().format('MM/DD/YYYY'),
                    filterDates: false,
                    selectedClients: 'All',
                    selectedId: 'All',
                    type: this.type,
                    json: 1,
                    businesses: ''
                },
                data: [],
                fields: [
                    {
                        key: 'nameLastFirst',
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
                        key: 'city',
                        label: 'City',
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

        computed: {
            items() {
                return this.data.filter(user =>
                    (this.showEmpty || !!user.date_of_birth) && (this.showInactive || user.active === 1))
                           .map((item) => {
                               return {
                                   ...item,
                                   formatted_date: item.date_of_birth ? moment(item.date_of_birth).format('MM/DD/YYYY') : '-',
                                   date_of_birth: moment(item.date_of_birth).format('MMDD'),
                               };
                           });
            }
        },

        methods: {
            async fetch() {
                this.loading = true;
                if (this.showDateRange) {
                    this.search.filterDates = true;
                }

                try {
                    const { data } = axios.post('/business/reports/birthdays/', this.search)
                                          .then(({ data }) => {
                                              this.data = data;
                                              this.search.filterDates = false;
                                              this.loading = false;
                                          })
                } catch (e) {
                    console.error(e);
                    this.loading = false;
                }
            },
        },
    }
</script>

<style scoped>
    .filter {
        margin: 20px 0;
    }
</style>

