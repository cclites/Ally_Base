<template>
    <div>
        <b-row v-if="schedule.notes" class="with-padding-top">
            <b-col lg="12">
                <b-card title="Schedule Notes">
                    <p class="notes">{{ schedule.notes }}</p>
                </b-card>
            </b-col>
        </b-row>
        <b-row v-if="carePlan.id" class="with-padding-top">
            <b-col lg="12">
                <b-card>
                    <caregiver-care-plan :care-plan="carePlan" :activities="carePlan.activities"></caregiver-care-plan>
                </b-card>
            </b-col>
        </b-row>
        <b-row v-if="careDetails.id" class="with-padding-top">
            <b-col lg="12">
                <b-card title="Detailed Client Care Needs">
                    <care-details-display :care-details="careDetails"></care-details-display>
                </b-card>
            </b-col>
        </b-row>
        <b-row>
            <b-col md="12" class="mt-3">
                <adjoining-caregivers-card :client="shift.client_id" :auto-load="true" :shift="shift.id"></adjoining-caregivers-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    import CaregiverCarePlan from "./caregivers/CaregiverCarePlan";
    import CareDetailsDisplay from "./CareDetailsDisplay";
    export default {
        components: {CareDetailsDisplay, CaregiverCarePlan},
        props: {
            'carePlan': Object,
            'careDetails': Object,
            'schedule': Object,
            'shift': Object,
        },
    }
</script>

