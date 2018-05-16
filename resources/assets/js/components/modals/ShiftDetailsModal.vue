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
            <b-row class="with-padding-bottom">
                <b-col sm="12">
                    <strong>Caregiver Comments</strong><br />
                    {{ selectedItem.caregiver_comments ? selectedItem.caregiver_comments : 'No comments recorded' }}
                </b-col>
            </b-row>

            <strong>Issues on Shift</strong>
            <b-row>
                <b-col sm="12">
                    <p v-if="!selectedItem.issues || !selectedItem.issues.length">
                        No issues reported
                    </p>
                    <p else v-for="issue in selectedItem.issues" :key="issue.id">
                        <strong v-if="issue.caregiver_injury">The caregiver reported an injury to themselves.<br /></strong>
                        {{ issue.comments }}
                    </p>
                </b-col>
            </b-row>

            <b-row class="with-padding-bottom" v-if="selectedItem.client.client_type == 'LTCI' && selectedItem.signature != null">
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
            <b-row>
                <b-col sm="6">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th colspan="2">Clock In</th>
                        </tr>
                        </thead>
                        <tbody v-if="selectedItem.checked_in_latitude || selectedItem.checked_in_longitude">
                        <tr>
                            <th>Geocode</th>
                            <td>{{ selectedItem.checked_in_latitude.slice(0,8) }}, {{ selectedItem.checked_in_longitude.slice(0,8) }}</td>
                        </tr>
                        <tr>
                            <th>Distance</th>
                            <td>{{ selectedItem.checked_in_distance }}m</td>
                        </tr>
                        </tbody>
                        <tbody v-else-if="selectedItem.checked_in_number">
                        <tr>
                            <th>Phone Number</th>
                            <td>{{ selectedItem.checked_in_number }}</td>
                        </tr>
                        </tbody>
                        <tbody v-else>
                        <tr>
                            <td colspan="2">No EVV data</td>
                        </tr>
                        </tbody>
                    </table>
                </b-col>
                <b-col sm="6">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th colspan="2">Clock Out</th>
                        </tr>
                        </thead>
                        <tbody v-if="selectedItem.checked_out_latitude || selectedItem.checked_out_longitude">
                        <tr>
                            <th>Geocode</th>
                            <td>{{ selectedItem.checked_out_latitude.slice(0,8) }}, {{ selectedItem.checked_out_longitude.slice(0,8) }}</td>
                        </tr>

                        <tr>
                            <th>Distance</th>
                            <td>{{ selectedItem.checked_out_distance }}m</td>
                        </tr>
                        </tbody>
                        <tbody v-else-if="selectedItem.checked_out_number">
                        <tr>
                            <th>Phone Number</th>
                            <td>{{ selectedItem.checked_out_number }}</td>
                        </tr>
                        </tbody>
                        <tbody v-else>
                        <tr>
                            <td colspan="2">No EVV data</td>
                        </tr>
                        </tbody>
                    </table>
                </b-col>
            </b-row>
        </b-container>
        <div slot="modal-footer">
            <slot name="buttons" :item="selectedItem"></slot>
        </div>
    </b-modal>
</template>

<script>
    export default {
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
                if (shift.verified) {
                    if (shift.checked_in_number) {
                        return 'Telephony';
                    } else if (shift.checked_in_latitude) {
                        return 'GPS Location via Mobile App';
                    }
                }
                return 'None';
            }

        }
    }
</script>
