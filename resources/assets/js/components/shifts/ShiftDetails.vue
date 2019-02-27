<template>
    <b-container fluid>
        <b-row class="mb-2">
            <b-col sm="6"><strong>Client:</strong> {{ shift.client_name }}</b-col>
            <b-col sm="6"><strong>Caregiver:</strong> {{ shift.caregiver_name }}</b-col>
        </b-row>
        <b-row class="mb-2">
            <b-col sm="6"><strong>Clocked In Date &amp; Time:</strong> {{ shift.checked_in_time }}</b-col>
            <b-col sm="6"><strong>Clocked Out Date &amp; Time:</strong> {{ shift.checked_out_time }}</b-col>
        </b-row>
        <b-row class="mb-2">
            <b-col sm="12">
                <div class="mb-2"><strong>Billing:</strong></div>
                <table class="table table-bordered table-fit-more table-striped table-hover mb-0">
                    <thead>
                    <tr>
                        <th>Service</th>
                        <th>Shift Type</th>
                        <th>Rate</th>
                        <th>Duration</th>
                        <th>Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item, index) in billingDetails" :key="index">
                        <td>{{ item.service }}</td>
                        <td>{{ formatHoursType(item.hours_type) }}</td>
                        <td>{{ moneyFormat(item.caregiver_rate) }}</td>
                        <td>{{ numberFormat(item.hours) }} hrs</td>
                        <td>{{ moneyFormat(item.client_rate) }}</td>
                    </tr>
                    </tbody>
                </table>
            </b-col>
        </b-row>

        <b-row class="mb-2">
            <b-col v-if="!shift.activities || !shift.activities.length" sm="12">
                <strong>Activities Performed:</strong> None
            </b-col>
            <b-col v-else sm="12">
                <div><strong>Activities Performed:</strong></div>
                <table class="table table-sm mb-0">
                    <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="activity in shift.activities" :key="activity.id">
                        <td>{{ activity.code }}</td>
                        <td>{{ activity.name }}</td>
                    </tr>
                    </tbody>
                </table>
            </b-col>
        </b-row>

        <b-row v-if="shift.schedule && shift.schedule.notes" class="mb-2">
            <b-col sm="12">
                <div><strong>Schedule Notes:</strong></div>
                {{ shift.schedule.notes }}
            </b-col>
        </b-row>

        <b-row v-if="business.co_comments" class="mb-2">
            <b-col v-if="!shift.caregiver_comments" sm="12">
                <strong>Caregiver Comments:</strong> None
            </b-col>
            <b-col v-else sm="12">
                <strong>Caregiver Comments:</strong><br />
                {{ shift.caregiver_comments }}
            </b-col>
        </b-row>

        <b-row v-if="business.co_issues || business.co_injuries" class="mb-2">
            <b-col v-if="!shift.issues || !shift.issues.length" sm="12">
                <strong>Issues reported on shift:</strong> None
            </b-col>
            <b-col v-else sm="12">
                <strong>Issues reported on shift:</strong><br />
                <ul>
                    <li v-for="issue in shift.issues" :key="issue.id" class="mb-1">
                        <strong v-if="issue.caregiver_injury">The caregiver reported an injury to themselves.<br /></strong>
                        {{ issue.comments }}
                    </li>
                </ul>
            </b-col>
        </b-row>

        <b-row v-if="shift.questions && shift.questions.length > 0">
            <b-col sm="12" v-for="q in shift.questions" :key="q.id" class="mb-2">
                <strong>{{ q.question }}</strong>
                <div v-if="!q.pivot.answer" class="text-muted">(Unanswered)</div>
                <div v-else>{{ q.pivot.answer }}</div>
            </b-col>
        </b-row>

        <b-row v-if="business.co_signature && shift.signature != null" class="mb-2">
            <b-col sm="12">
                <strong>Client Signature</strong>
                <div v-html="shift.signature.content" class="signature"></div>
            </b-col>
        </b-row>

        <b-row v-if="shift.goals && shift.goals.length" class="mb-2">
            <b-col sm="12">
                <strong>Goals:</strong>
                <ul>
                    <li v-for="goal in shift.goals" :key="goal.id" class="mb-2">
                        <strong>{{ goal.question }}:</strong> {{ goal.pivot.comments }}
                    </li>
                </ul>
            </b-col>
        </b-row>

        <b-row class="mb-2">
            <b-col sm="6"><strong>Was this Shift Electronically Verified?</strong> {{ shift.verified ? 'Yes' : 'No' }}</b-col>
            <b-col sm="6" v-if="shift.verified"><strong>Verification Method:</strong> {{ evvMethod }}</b-col>
        </b-row>
        <b-row>
            <b-col sm="12">
                <shift-evv-data-table v-if="isAdmin" :shift="shift"></shift-evv-data-table>
            </b-col>
        </b-row>
    </b-container>
</template>

<script>
    import authUser from '../../mixins/AuthUser';
    import ShiftServices from "../../mixins/ShiftServices";
    import FormatsNumbers from "../../mixins/FormatsNumbers";

    export default {
        mixins: [ authUser, ShiftServices, FormatsNumbers ],

        props: {
            shift: {
                type: Object,
                default: () => { return {} },
            },
        },

        data() {
            return {
            };
        },

        computed: {
            evvMethod() {
                if (this.shift.checked_in_number) {
                    return 'Telephony';
                } else if (this.shift.checked_in_latitude) {
                    return 'GPS Location via Mobile App';
                }
                return 'None';
            },
            business() {
                return this.shift.business_id ? this.$store.getters.getBusiness(this.shift.business_id) : {};
            },
            billingDetails() {
                let rows = [];

                if (this.shift.services && this.shift.services.length) {
                    // service breakout
                    return this.shift.services.map(item => {
                        let service = this.services.find(x => x.id === item.service_id);
                        return {
                            service: service ? service.name : 'General',
                            caregiver_rate: item.caregiver_rate,
                            client_rate: item.client_rate,
                            hours_type: item.hours_type,
                            hours: item.duration,
                        };
                    });
                } else {
                    // shift rates
                    // TODO: handle fixed and hourly rates
                    return [{
                        service: this.defaultService ? this.defaultService.name : 'General',
                        caregiver_rate: this.shift.caregiver_rate,
                        client_rate: this.shift.client_rate,
                        hours_type: this.shift.hours_type,
                        hours: this.shift.hours,
                    }];
                }
            },
        },

        methods: {
            formatHoursType(hoursType) {
                switch (hoursType) {
                    case 'default':
                        return 'REG';
                    case 'overtime':
                        return 'OT';
                    case 'holiday':
                        return 'HOL';
                }
            }
        },

        mounted() {
            if (this.isAdmin) {
                this.fetchServices();
            }
        },
    }
</script>

<style scoped>
ul { padding-inline-start: 1.5rem; margin-bottom: 0px; }
</style>