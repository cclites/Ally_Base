<template>
    <b-modal id="detailsModal" title="Shift Details" v-model="showModal" size="lg">
        <b-container fluid>
            <b-row class="with-padding-bottom">
                <b-col sm="6">
                    <strong>Client</strong>
                    <br />
                    {{ selectedItem.client_name }}
                </b-col>
                <b-col sm="6">
                    <strong>Caregiver</strong><br />
                    {{ selectedItem.caregiver_name }}
                </b-col>
            </b-row>
            <b-row class="with-padding-bottom">
                <b-col sm="6">
                    <strong>Clocked In Time</strong><br />
                    {{ selectedItem.checked_in_time }}
                </b-col>
                <b-col sm="6">
                    <strong>Clocked Out Time</strong><br />
                    {{ selectedItem.checked_out_time }}<br />
                </b-col>
            </b-row>
            <b-row>
                <b-col sm="6" class="with-padding-bottom">
                    <strong>Shift Type</strong><br>
                    {{ hoursType(selectedItem)}}
                </b-col>
            </b-row>
            <b-row class="with-padding-bottom" v-if="selectedItem.schedule && selectedItem.schedule.notes">
                <b-col sm="12">
                    <strong>Schedule Notes</strong><br />
                    {{ selectedItem.schedule.notes }}
                </b-col>
            </b-row>
            <b-row v-if="business.co_comments" class="with-padding-bottom">
                <b-col sm="12">
                    <strong>Caregiver Comments</strong><br />
                    {{ selectedItem.caregiver_comments ? selectedItem.caregiver_comments : 'No comments recorded' }}
                </b-col>
            </b-row>

            <b-row v-if="business.co_issues || business.co_injuries">
                <b-col sm="12">
                    <strong>Issues on Shift</strong>
                    <p v-if="!selectedItem.issues || !selectedItem.issues.length">
                        No issues reported
                    </p>
                    <p else v-for="issue in selectedItem.issues" :key="issue.id">
                        <strong v-if="issue.caregiver_injury">The caregiver reported an injury to themselves.<br /></strong>
                        {{ issue.comments }}
                    </p>
                </b-col>
            </b-row>

            <b-row v-if="selectedItem.questions && selectedItem.questions.length > 0">
                <b-col sm="12" v-for="q in selectedItem.questions" :key="q.id">
                    <strong>{{ q.question }}</strong>
                    <p v-if="q.pivot.answer == ''" class="text-muted">(Unanswered)</p>
                    <p v-else>{{ q.pivot.answer }}</p>
                </b-col>
            </b-row>

            <b-row class="with-padding-bottom" v-if="business.co_signature && selectedItem.signature != null">
                <b-col>
                    <strong>Client Signature</strong>
                    <div v-html="selectedItem.signature.content" class="signature"></div>
                </b-col>
            </b-row>

            <strong>Activities Performed</strong>
            <b-row>
                <b-col sm="12">
                    <p v-if="!selectedItem.activities || !selectedItem.activities.length">
                        No activities recorded
                    </p>
                    <table class="table table-sm" v-else>
                        <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="activity in selectedItem.activities" :key="activity.id">
                            <td>{{ activity.code }}</td>
                            <td>{{ activity.name }}</td>
                        </tr>
                        </tbody>
                    </table>
                </b-col>
            </b-row>

            <b-row v-if="selectedItem.goals && selectedItem.goals.length" class="with-padding-bottom">
                <b-col sm="12">
                    <strong>Goals</strong>
                    <div v-for="goal in selectedItem.goals" :key="goal.id" class="mb-2">
                        <strong>{{ goal.question }}:</strong> {{ goal.pivot.comments }}
                    </div>
                </b-col>
            </b-row>
            
            <strong>Was this Shift Electronically Verified?</strong>
            <b-row class="with-padding-bottom">
                <b-col sm="6">
                    <span v-if="selectedItem.verified">Yes</span>
                    <span v-else>No</span>
                </b-col>
            </b-row>
            <strong>EVV Method</strong>
            <b-row class="with-padding-bottom">
                <b-col sm="6">
                    {{ evvMethod(selectedItem) }}
                </b-col>
            </b-row>
            <shift-evv-data-table :shift="selectedItem"></shift-evv-data-table>
        </b-container>
        <div slot="modal-footer">
            <slot name="buttons" :item="selectedItem"></slot>
        </div>
    </b-modal>
</template>

<script>
    import FormatsDistance from "../../mixins/FormatsDistance";

    export default {
        mixins: [FormatsDistance],

        props: {
            value: {},
            selectedItem: {},
            items: Array,
        },

        computed: {
            showModal: {
                get() {
                    return this.value;
                },
                set(value) {
                    this.$emit('input', value);
                }
            },
            business() {
                return this.selectedItem.business_id ? this.$store.getters.getBusiness(this.selectedItem.business_id) : {};
            },
        },

        methods: {

            hoursType(item) {
                switch (item.hours_type) {
                    case 'default':
                        return 'Regular';
                    case 'overtime':
                        return 'OT';
                    case 'holiday':
                        return 'HOL';
                }
            },

            evvMethod(shift) {
                if (shift.checked_in_number) {
                    return 'Telephony';
                } else if (shift.checked_in_latitude) {
                    return 'GPS Location via Mobile App';
                }
                return 'None';
            }

        }
    }
</script>
