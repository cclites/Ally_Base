<template>
    <b-container>
        <b-card title="Submission">
            <table class="table">
                <tr>
                    <td>First Name:</td>
                    <td>{{ application.first_name }}</td>
                </tr>
                <tr>
                    <td>Middle Initial:</td>
                    <td>{{ application.middle_initial }}</td>
                </tr>
                <tr>
                    <td>Last Name:</td>
                    <td>{{ application.last_name }}</td>
                </tr>
                <tr>
                    <td>Email:</td>
                    <td>{{ application.email }}</td>
                </tr>
                <tr>
                    <td>Cell Phone:</td>
                    <td>{{ application.cell_phone }} {{ application.cell_phone_provider }}</td>
                </tr>
                <tr v-if="application.date_of_birth">
                    <td>Date of Birth:</td>
                    <td>{{ application.date_of_birth }}</td>
                </tr>
                <tr v-if="application.ssn">
                    <td>Social Security Number:</td>
                    <td>{{ application.ssn }}</td>
                </tr>
                <tr v-if="application.address">
                    <td>Address:</td>
                    <td>{{ application.address }}</td>
                </tr>
                <tr v-if="application.address_2">
                    <td>Address 2:</td>
                    <td>{{ application.address_2 }}</td>
                </tr>
                <tr v-if="application.city">
                    <td>City:</td>
                    <td>{{ application.city }}</td>
                </tr>
                <tr v-if="application.state">
                    <td>State:</td>
                    <td>{{ application.state }}</td>
                </tr>
                <tr v-if="application.zip">
                    <td>Zip:</td>
                    <td>{{ application.zip }}</td>
                </tr>
                <tr v-if="application.home_phone">
                    <td>Home Phone:</td>
                    <td>{{ application.home_phone }}</td>
                </tr>
                <tr v-if="application.emergency_contact_name">
                    <td>Emergency Contact Name</td>
                    <td>{{ application.emergency_contact_name }}</td>
                </tr>
                <tr v-if="application.emergency_contact_phone">
                    <td>Emergency Contact Phone</td>
                    <td>{{ application.emergency_contact_phone }}</td>
                </tr>
                <tr>
                    <td>Worked here before:</td>
                    <td>{{ application.worked_here_before ? 'Yes' : 'No' }}</td>
                </tr>
                <tr v-if="application.worked_before_location">
                    <td>Location:</td>
                    <td>{{ application.worked_before_location }}</td>
                </tr>
                <tr v-if="application.position">
                    <td>Position applied for:</td>
                    <td>{{ application.position.name }}</td>
                </tr>
                <tr v-if="application.classification">
                    <td>Level of Credentialing:</td>
                    <td>{{ application.classification }}</td>
                </tr>
                <tr v-if="application.license_number">
                    <td>License Number:</td>
                    <td>{{ application.license_number }}</td>
                </tr>
                <tr v-if="application.training_school">
                    <td>Healthcare Training School Name:</td>
                    <td>{{ application.training_school }}</td>
                </tr>
                <tr>
                    <td colspan="2">
                        <h5>Availability</h5>
                    </td>
                </tr>
                <tr v-if="application.preferred_start_date">
                    <td>Preferred Start Date</td>
                    <td>{{ formatDate(application.preferred_start_date) }}</td>
                </tr>
                <tr v-if="application.preferred_days">
                    <td>Preferred Days</td>
                    <td>{{ application.preferred_days }}</td>
                </tr>
                <tr v-if="application.preferred_times">
                    <td>Preferred Times</td>
                    <td>{{ application.preferred_times }}</td>
                </tr>
                <tr v-if="application.preferred_shift_length">
                    <td>Preferred Shift Length</td>
                    <td>{{ application.preferred_shift_length }}hrs</td>
                </tr>
                <tr v-if="application.travel_radius">
                    <td>Travel Radius:</td>
                    <td>{{ application.travel_radius }}</td>
                </tr>
                <tr>
                    <td colspan="2">
                        <h5>Driving History</h5>
                    </td>
                </tr>
                <tr v-if="application.vehicle">
                    <td>Vehicle</td>
                    <td>{{ application.vehicle }}</td>
                </tr>
                <tr v-if="application.city">
                    <td>DUI in last three years</td>
                    <td>{{ application.dui ? 'Yes' : 'No' }}</td>
                </tr>
                <tr v-if="application.reckless_driving">
                    <td>Reckless Driving in last three years</td>
                    <td>{{ application.reckless_driving ? 'Yes' : 'No' }}</td>
                </tr>
                <tr v-if="application.moving_violation">
                    <td>Moving Violations in last three years</td>
                    <td>{{ application.moving_violation ? 'Yes' : 'No' }}</td>
                </tr>
                <tr v-if="application.moving_violation_count">
                    <td>Moving violation count:</td>
                    <td>{{ application.moving_violation_count }}</td>
                </tr>
                <tr v-if="application.accidents">
                    <td>Accidents in last 3 years:</td>
                    <td>{{ application.accidents }}</td>
                </tr>
                <tr v-if="application.accident_count">
                    <td>Accident Count:</td>
                    <td>{{ application.accident_count }}</td>
                </tr>
                <tr v-if="application.driving_violations_desc">
                    <td>Driving violations description:</td>
                    <td>{{ application.driving_violations_desc }}</td>
                </tr>
                <tr>
                    <td colspan="2">
                        <h5>Criminal History</h5>
                    </td>
                </tr>
                <tr>
                    <td>Felony conviction:</td>
                    <td>{{ application.felony_conviction ? 'Yes' : 'No' }}</td>
                </tr>
                <tr>
                    <td>Theft conviction:</td>
                    <td>{{ application.theft_conviction ? 'Yes' : 'No' }}</td>
                </tr>
                <tr>
                    <td>Drug conviction:</td>
                    <td>{{ application.drug_conviction ? 'Yes' : 'No' }}</td>
                </tr>
                <tr>
                    <td>Violence conviction:</td>
                    <td>{{ application.violence_conviction ? 'Yes' : 'No' }}</td>
                </tr>
                <tr v-if="application.criminal_history_desc">
                    <td>Criminal History Description:</td>
                    <td>{{ application.criminal_history_desc }}</td>
                </tr>
                <tr>
                    <td colspan="2">
                        <h5>Injury Status</h5>
                    </td>
                </tr>
                <tr>
                    <td>Currently Injured:</td>
                    <td>{{ application.currently_injured ? 'Yes' : 'No' }}</td>
                </tr>
                <tr>
                    <td>Previously Injured:</td>
                    <td>{{ application.currently_injured ? 'Yes' : 'No' }}</td>
                </tr>
                <tr>
                    <td>Can lift 25lbs:</td>
                    <td>{{ application.currently_injured ? 'Yes' : 'No' }}</td>
                </tr>
                <tr>
                    <td>Previous Workman's Comp Claims:</td>
                    <td>{{ application.workmans_comp ? 'Yes' : 'No' }}</td>
                </tr>
                <tr v-if="application.workmans_comp_dates">
                    <td>Workmans Comp Dates:</td>
                    <td>{{ application.workmans_comp_dates }}</td>
                </tr>
                <tr v-if="application.injury_status_desc">
                    <td>Injury Status Description:</td>
                    <td>{{ application.injury_status_desc }}</td>
                </tr>
                <tr>
                    <td colspan="2">
                        <h5>Employment History</h5>
                    </td>
                </tr>
                <tr v-if="application.employer_1_name">
                    <td>Employer Name:</td>
                    <td>{{ application.employer_1_name }}</td>
                </tr>
                <tr v-if="application.employer_1_city">
                    <td>Employer City:</td>
                    <td>{{ application.employer_1_city }}</td>
                </tr>
                <tr v-if="application.employer_1_state">
                    <td>Employer State:</td>
                    <td>{{ application.employer_1_state }}</td>
                </tr>
                <tr v-if="application.employer_1_approx_start_date">
                    <td>Employer Approx. Start Date:</td>
                    <td>{{ application.employer_1_approx_start_date }}</td>
                </tr>
                <tr v-if="application.employer_1_approx_end_date">
                    <td>Employer Approx. End Date:</td>
                    <td>{{ application.employer_1_approx_end_date }}</td>
                </tr>
                <tr v-if="application.employer_1_phone">
                    <td>Employer Phone:</td>
                    <td>{{ application.employer_1_phone }}</td>
                </tr>
                <tr v-if="application.employer_1_supervisor_name">
                    <td>Supervisor Name:</td>
                    <td>{{ application.employer_1_supervisor_name }}</td>
                </tr>

                <tr v-if="employer_2_name">
                    <td colspan="2">
                        <hr>
                    </td>
                </tr>
                <tr v-if="application.employer_2_name">
                    <td>Employer Name:</td>
                    <td>{{ application.employer_2_name }}</td>
                </tr>
                <tr v-if="application.employer_2_city">
                    <td>Employer City:</td>
                    <td>{{ application.employer_2_city }}</td>
                </tr>
                <tr v-if="application.employer_2_state">
                    <td>Employer State:</td>
                    <td>{{ application.employer_2_state }}</td>
                </tr>
                <tr v-if="application.employer_2_approx_start_date">
                    <td>Employer Approx. Start Date:</td>
                    <td>{{ application.employer_2_approx_start_date }}</td>
                </tr>
                <tr v-if="application.employer_2_approx_end_date">
                    <td>Employer Approx. End Date:</td>
                    <td>{{ application.employer_2_approx_end_date }}</td>
                </tr>
                <tr v-if="application.employer_2_phone">
                    <td>Employer Phone:</td>
                    <td>{{ application.employer_2_phone }}</td>
                </tr>
                <tr v-if="application.employer_2_supervisor_name">
                    <td>Supervisor Name:</td>
                    <td>{{ application.employer_2_supervisor_name }}</td>
                </tr>

                <tr v-if="employer_3_name">
                    <td colspan="2">
                        <hr>
                    </td>
                </tr>
                <tr v-if="application.employer_3_name">
                    <td>Employer Name:</td>
                    <td>{{ application.employer_3_name }}</td>
                </tr>
                <tr v-if="application.employer_3_city">
                    <td>Employer City:</td>
                    <td>{{ application.employer_3_city }}</td>
                </tr>
                <tr v-if="application.employer_3_state">
                    <td>Employer State:</td>
                    <td>{{ application.employer_3_state }}</td>
                </tr>
                <tr v-if="application.employer_3_approx_start_date">
                    <td>Employer Approx. Start Date:</td>
                    <td>{{ application.employer_3_approx_start_date }}</td>
                </tr>
                <tr v-if="application.employer_3_approx_end_date">
                    <td>Employer Approx. End Date:</td>
                    <td>{{ application.employer_3_approx_end_date }}</td>
                </tr>
                <tr v-if="application.employer_3_phone">
                    <td>Employer Phone:</td>
                    <td>{{ application.employer_3_phone }}</td>
                </tr>
                <tr v-if="application.employer_3_supervisor_name">
                    <td>Supervisor Name:</td>
                    <td>{{ application.employer_3_supervisor_name }}</td>
                </tr>

                <tr>
                    <td colspan="2">
                        <h5>Personal References</h5>
                    </td>
                </tr>
                <tr v-if="application.reference_1_name">
                    <td>Reference Name:</td>
                    <td>{{ application.reference_1_name }}</td>
                </tr>
                <tr v-if="application.reference_1_phone">
                    <td>Reference Phone:</td>
                    <td>{{ application.reference_1_phone }}</td>
                </tr>
                <tr v-if="application.reference_1_relationship">
                    <td>Reference Relationship:</td>
                    <td>{{ application.reference_1_relationship }}</td>
                </tr>

                <tr v-if="reference_2_name">
                    <td colspan="2">
                        <hr>
                    </td>
                </tr>
                <tr v-if="application.reference_2_name">
                    <td>Reference Name:</td>
                    <td>{{ application.reference_2_name }}</td>
                </tr>
                <tr v-if="application.reference_2_phone">
                    <td>Reference Phone:</td>
                    <td>{{ application.reference_2_phone }}</td>
                </tr>
                <tr v-if="application.reference_2_relationship">
                    <td>Reference Relationship:</td>
                    <td>{{ application.reference_2_relationship }}</td>
                </tr>

                <tr v-if="reference_3_name">
                    <td colspan="2">
                        <hr>
                    </td>
                </tr>
                <tr v-if="application.reference_3_name">
                    <td>Reference Name:</td>
                    <td>{{ application.reference_3_name }}</td>
                </tr>
                <tr v-if="application.reference_3_phone">
                    <td>Reference Phone:</td>
                    <td>{{ application.reference_3_phone }}</td>
                </tr>
                <tr v-if="application.reference_3_relationship">
                    <td>Reference Relationship:</td>
                    <td>{{ application.reference_3_relationship }}</td>
                </tr>
                <tr v-if="application.heard_about">
                    <td>Heard about:</td>
                    <td>{{ application.heard_about }}</td>
                </tr>
            </table>
            <b-row>
                <b-col sm="6" v-if="application.caregiver_signature">
                    <strong>Caregiver Signature</strong>
                    <div v-html="application.caregiver_signature.content" class="signature"></div>
                </b-col>
            </b-row>
        </b-card>
    </b-container>
</template>

<style lang="scss">
</style>

<script>
    export default {
        props: ['application'],
        
        mixins: [],
        
        components: {
        
        },
        
        data() {
            return{
                
            }
        },
        
        created() {
        
        },
        
        mounted() {
        
        },
        
        methods: {
            formatDate(date) {
                return moment(date).format('L');
            }
        },
        
        computed: {
        
        }
    }
</script>