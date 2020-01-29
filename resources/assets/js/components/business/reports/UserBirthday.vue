<template>

    <b-card :title="`${type} Birthday`">

        <b-row class="filter justify-content-between align-items-center">
            <b-col cols="3">
                <b-form-checkbox v-model="showEmpty" :value="true" :unchecked-value="false" class="d-flex align-items-center">
                    Show {{type}} without birthdays
                </b-form-checkbox>
            </b-col>
            <b-col cols="3" v-if="type === 'Clients'">
                <label>Client Types:<b-form-select class="form-group-label " v-model="selectedClients" >
                    <option value="All">All</option>
                    <option v-for="option in clientTypes" :value="option" :key="option.id" >{{ option }}</option>
                </b-form-select>
                </label>
            </b-col>

            <b-col lg="3" v-if="type === 'Caregivers'">
                <b-form-group label="Caregiver">
                    <b-form-select v-model="selectedId">
                        <option value="All">All</option>
                        <option v-for="caregiver in caregiverList" :value="caregiver.id">{{
                            caregiver.name }}
                        </option>
                    </b-form-select>
                </b-form-group>
            </b-col>
            <b-col lg="3"v-else>
                <b-form-group label="Clients">
                    <b-form-select v-model="selectedId">
                        <option value="All">All</option>
                        <option v-for="client in clientList" :value="client.id">{{
                            client.name }}
                        </option>
                    </b-form-select>
                </b-form-group>
            </b-col>

            <b-button @click=" fetch() " variant="info">Generate Report</b-button>
        </b-row>

        <b-row class="filter justify-content-between align-items-center">
            <b-col cols="3">
                <b-form-checkbox v-model="showInactive" :value="true" :unchecked-value="false" class="d-flex align-items-center">
                    Show inactive {{type}}
                </b-form-checkbox>
            </b-col>
        </b-row>

        <loading-card v-show="loading" />
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

    export default {
        mixins: [ FormatsDates ],

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
                selectedClients: 'All',
                selectedId: 'All',
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
                return this.data.filter(user => {
                    if (this.showEmpty) {
                        return true;
                    }
                    return !!user.date_of_birth;
                }).map((item) => {
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

                try {
                    const {data} = await axios.get(`/business/reports/data/birthdays?type=${this.type}&clientType=${this.selectedClients}&id=${this.selectedId}`);
                    this.data = data;
                    this.loading = false
                }catch (e) {
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

