<template>
    <div>
        <b-card
            header="Select Date Range &amp; Filters"
            header-text-variant="white"
            header-bg-variant="info"
        >
            <b-form inline @submit.prevent="fetch()" class="mb-4">
                <b-form-group label="Start Date" class="mb-2 mr-2">
                    <date-picker
                        name="start_date"
                        v-model="form.start_date"
                        placeholder="Start Date"
                        :disabled="state === 'loading'"
                    />
                </b-form-group>

                <b-form-group label="End Date" class="mb-2 mr-2">
                    <date-picker
                        v-model="form.end_date"
                        name="end_date"
                        placeholder="End Date"
                        :disabled="state === 'loading'"
                    />
                </b-form-group>

                <b-form-group label="Client Type" class="mb-2 mr-2">
                    <client-type-dropdown v-model="form.client_type" name="client_type" :disabled="state === 'loading'" />
                </b-form-group>

                <b-form-group label="Client Status" class="mb-2 mr-2">
                    <b-form-select v-model="form.client_status">
                        <option value="">All</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </b-form-select>
                </b-form-group>

                <b-form-group label="&nbsp;" class="mb-2 mr-2">
                    <b-button variant="info" type="submit" :disabled="state === 'loading'">Generate</b-button>
                </b-form-group>
            </b-form>

            <b-row v-if="state === 'loaded'">
                <b-col md="6">
                    <div class="h4">Client Statistics</div>
                    <div class="h5">Total Clients Serviced: {{ totalClientsServiced }}</div>
                    <div class="h5">Total Males Serviced: {{ genders['male'] }}</div>
                    <div class="h5">Total Females Serviced: {{ genders['female'] }}</div>
                    <div class="h5">Total Unassigned Gender Serviced: {{ genders['unassigned'] }}</div>
                    <div class="h5">Average Client Age: {{ averageAge }}</div>
                </b-col>
                <b-col md="6" class="mt-4 mt-md-0">
                    <div class="h4">ADL Statistics</div>
                    <div class="h5">Top Activities Performed for Clients: </div>
                    <b-table :items="activities" :fields="activityFields"></b-table>
                </b-col>
            </b-row>
        </b-card>
    </div>
</template>

<script>
    import FormatsDates from '../../../mixins/FormatsDates'

    export default {
        props: {
            clientTypes: {
                type: Array,
                required: true
            }
        },

        mixins: [FormatsDates],

        data () {
            return {
                state: 'notLoaded',
                form: new Form({
                    client_type: '',
                    client_status: '',
                    start_date: this.formatDate(moment().subtract(1, 'month')),
                    end_date: this.formatDate(moment())
                }),
                totalClientsServiced: 0,
                genders: [],
                averageAge: 0,
                activities: [],
                activityFields: [
                    {
                        key: 'name',
                        label: 'Activity',
                        sortable: true
                    },
                    {
                        key: 'count',
                        label: 'Number of times selected',
                        sortable: true
                    }
                ]
            }
        },

        methods: {
            async fetch () {
                this.state = 'loading';
                let response = await this.form.post('/business/reports/client-stats');
                this.totalClientsServiced = response.data.totalClientsServiced;
                this.genders = response.data.genders;
                this.averageAge = response.data.averageAge;
                this.activities = response.data.activities;
                this.state = 'loaded';
            }
        }
    }
</script>
