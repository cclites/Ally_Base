<template>
    <div>
        <b-row>
            <b-col sm="6" class="mb-2"><strong>Client:</strong> {{ shift.client_name }}</b-col>
            <b-col sm="6" class="mb-2"><strong>Caregiver:</strong> {{ shift.caregiver_name }}</b-col>
        </b-row>
        <b-row>
            <b-col sm="6" class="mb-2"><strong>Clocked In Date &amp; Time:</strong> {{ shift.checked_in_time }}</b-col>
            <b-col sm="6" class="mb-2"><strong>Clocked Out Date &amp; Time:</strong> {{ shift.checked_out_time }}</b-col>
        </b-row>
        <b-row class="mb-2">
            <b-col sm="12">
                <div class="mb-2"><strong>Billing ({{ billingDisplay }}):</strong></div>
                <div class="table-responsive">
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
                            <td>{{ moneyFormat(item.rate) }}</td>
                            <td>{{ numberFormat(item.hours) }} hrs</td>
                            <td>{{ moneyFormat(item.total) }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
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
                    <tr v-for="(activity, index) in shift.activities" :key="index">
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

        <b-row class="mb-2">
            <b-col sm="6" v-if="business.co_signature && shift.client_signature != null">
                <strong>Client Signature</strong>
                <div v-html="shift.client_signature.content" class="signature"></div>
            </b-col>

            <b-col sm="6" v-if="business.co_caregiver_signature && shift.caregiver_signature != null">
                <strong>Caregiver Signature</strong>
                <div v-html="shift.caregiver_signature.content" class="signature"></div>
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

        <b-row v-if=" isOfficeUserOrAdmin && shift.admin_note " class="mt-2 mb-4">

            <b-col>

                <h3>Admin Note:<small class="text-muted"> *only you can see this*</small></h3>
                > {{ shift.admin_note }}
            </b-col>
        </b-row>

        <b-row class="mb-2">
            <b-col sm="6"><strong>Was this Shift Electronically Verified?</strong> {{ shift.verified ? 'Yes' : 'No' }}</b-col>
            <b-col sm="6" v-if="shift.verified"><strong>Verification Method:</strong> {{ evvMethod }}</b-col>
        </b-row>
        <b-row>
            <b-col sm="12">
                <shift-evv-data-table v-if="isOfficeUserOrAdmin" :shift="shift"></shift-evv-data-table>
            </b-col>
        </b-row>
        <b-row class="mb-2" v-if=" isOfficeUserOrAdmin ">
            <b-col sm="6" v-if=" shift.visit_edit_reason_id "><strong>Reason shift was edited:</strong> {{ mappedShiftEditReason( shift.visit_edit_reason_id ) }}</b-col>
            <b-col sm="6" v-if=" shift.visit_edit_action_id "><strong>Edit Action taken:</strong> {{ mappedShiftEditAction( shift.visit_edit_action_id ) }}</b-col>
        </b-row>
    </div>
</template>

<script>
    import authUser from '../../mixins/AuthUser';
    import ShiftServices from "../../mixins/ShiftServices";
    import FormatsNumbers from "../../mixins/FormatsNumbers";
    import { mapGetters } from 'vuex';

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

            ...mapGetters({

                visitEditReasonCodes : 'claims/visitEditReasonCodes',
                visitEditActionCodes : 'claims/visitEditActionCodes',
            }),
            business() {
                return this.shift.business_id ? this.$store.getters.getBusiness(this.shift.business_id) : {};
            },

            evvMethod() {
                if (this.shift.checked_in_number) {
                    return 'Telephony';
                } else if (this.shift.checked_in_latitude) {
                    return 'GPS Location via Mobile App';
                }
                return 'None';
            },
            
            billingDisplay() {
                if (this.shift.services && this.shift.services.length) {
                    return 'Services';
                }

                return this.shift.fixed_rates ? 'Fixed Rate' : 'Hourly';
            },

            billingDetails() {
                let rows = [];
                if (this.shift.services && this.shift.services.length) {
                    // service breakout
                    return this.shift.services.map(item => {
                        let service = this.services.find(x => x.id === item.service_id);
                        let rate = this.authRole == 'caregiver' ? item.caregiver_rate : item.client_rate;
                        return {
                            service: item.service ? item.service.name : (service ? service.name : 'General'),
                            rate: rate,
                            hours_type: item.hours_type,
                            hours: item.duration,
                            total: this.calculateTotal(rate, item.duration),
                        };
                    });
                } else {
                    // shift rates
                    let service = this.services.find(x => x.id === this.shift.service_id);
                    let defaultServiceName = this.defaultService && this.defaultService.name ? this.defaultService.name : 'General';
                    let rate = this.authRole == 'caregiver' ? this.shift.caregiver_rate : this.shift.client_rate;
                    return [{
                        service: this.shift.service ? this.shift.service.name : (service ? service.name : defaultServiceName),
                        rate: rate,
                        hours_type: this.shift.hours_type,
                        hours: this.shift.hours,
                        total: this.calculateTotal(rate, this.shift.hours, this.shift.fixed_rates),
                    }];
                }
            },
        },

        methods: {

            mappedShiftEditReason( id ){

                const reason = this.visitEditReasonCodes.find( r => r.id === id );
                if( !reason ) return null;
                return `${reason.code}: ${reason.description}`;
            },
            mappedShiftEditAction( id ){

                const action = this.visitEditActionCodes.find( r => r.id === id );
                if( !action ) return null;
                return `${action.code}: ${action.description}`;
            },
            formatHoursType(hoursType) {
                switch (hoursType) {
                    case 'default':
                        return 'REG';
                    case 'overtime':
                        return 'OT';
                    case 'holiday':
                        return 'HOL';
                }
            },

            calculateTotal(clientRate, duration, isFixed = false) {
                if (isFixed) {
                    return clientRate;
                }
                return (parseFloat(clientRate) * parseFloat(duration));
            },
        },

        mounted() {

            if (this.isOfficeUserOrAdmin) {
                this.$store.dispatch( 'claims/fetchVisitEditCodes' );
                this.fetchServices();
            }
        },
    }
</script>

<style scoped>
ul { padding-inline-start: 1.5rem; margin-bottom: 0px; }
</style>