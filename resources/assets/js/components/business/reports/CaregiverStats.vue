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

                <b-form-group label="Caregiver Status" class="mb-2 mr-2">
                    <b-form-select v-model="form.caregiver_status">
                        <option value="">All</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </b-form-select>
                </b-form-group>

                <b-form-group label="&nbsp;" class="mb-2 mr-2">
                    <b-button variant="info" type="submit" :disabled="state === 'loading'">Generate</b-button>
                </b-form-group>
            </b-form>

            <template v-if="state === 'loaded'">
                <b-row>
                    <b-col>
                        <div class="h4">Caregiver Statistics</div>
                        <div class="h5">Total Caregivers Worked: {{ totalCaregivers }}</div>
                        <div class="h5">Total Caregiver Hours: {{ totalCaregiverHours }}</div>
                        <div class="h5">Total Males Serviced: {{ genders['male'] }}</div>
                        <div class="h5">Total Females Serviced: {{ genders['female'] }}</div>
                        <div class="h5">Total Unassigned Gender Serviced: {{ genders['unassigned'] }}</div>
                        <div class="h5">Average Caregiver Age: {{ averageAge }}</div>
                    </b-col>
                </b-row>
                <hr>
                <b-row>
                    <b-col>
                        <div class="h4">Top three activities performed by each Caregiver:</div>
                        <b-table :items="caregiverTopActivities" :fields="activityFields" sort-by="name">
                        </b-table>
                    </b-col>
                </b-row>
            </template>

        </b-card>
    </div>
</template>

<script>
    import FormatsDates from '../../../mixins/FormatsDates'

    export default {
        props: {},

        mixins: [FormatsDates],

        data () {
            return {
                state: 'notLoaded',
                form: new Form({
                    caregiver_status: '',
                    start_date: this.formatDate(moment().subtract(12, 'month')),
                    end_date: this.formatDate(moment())
                }),
                caregiverTopActivities: [],
                totalCaregiverHours: 0,
                genders: [],
                averageAge: 0,
                activities: [],
                activityFields: [
                    {
                        key: 'name',
                        label: 'Caregiver',
                        sortable: true
                    },
                    {
                        key: 'activity_1',
                        label: 'Activity 1',
                        formatter: this.showActivity
                    },
                    {
                        key: 'activity_2',
                        label: 'Activity 2',
                        formatter: this.showActivity
                    },
                    {
                        key: 'activity_3',
                        label: 'Activity 3',
                        formatter: this.showActivity
                    },
                ]
            }
        },

        methods: {
            async fetch () {
                this.state = 'loading';
                let response = await this.form.post('/business/reports/caregiver-stats')
                    .catch(error => {
                        this.state = 'notLoaded'
                    });
                this.genders = response.data.genders;
                this.totalCaregivers = response.data.totalCaregivers;
                this.totalCaregiverHours = response.data.totalCaregiverHours;
                this.averageAge = response.data.averageAge;
                this.activities = response.data.activities;
                this.caregiverTopActivities = response.data.caregiverTopActivities;
                this.state = 'loaded';
            },

            showActivity(item) {
                return _.isObject(item) ? `${item.name} (${item.count})` : '';
            }
        }
    }
</script>
