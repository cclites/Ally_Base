<template>
    <div>
        <b-row>
            <b-col sm="7">
                <p>
                    <strong>{{ client.name }}</strong>
                </p>
                <p v-if="address">
                    {{ address.address1 }}<br />
                    <span v-if="address.address2">{{ address.address2 }}<br /></span>
                    {{ address.city }}, {{ address.state }} {{ address.zip }}

                    <span class="d-block mt-2">
                        Notes:<br/>
                        {{ address.notes }}
                    </span>
                </p>
                <p v-if="phone">
                    {{ phone }}
                </p>
            </b-col>
            <b-col sm="5">
                <user-avatar :src="client.avatar" />
            </b-col>
        </b-row>

        <b-row v-if="carePlan.id" class="with-padding-top">
            <b-col sm="12">
                <b-card>
                    <caregiver-care-plan :care-plan="carePlan" :activities="carePlan.activities"></caregiver-care-plan>
                </b-card>
            </b-col>
        </b-row>

        <b-row v-if="careDetails.id" class="with-padding-top care-details-scrollable">
            <b-col sm="12">
                <b-card title="Detailed Client Care Needs">
                    <care-details-display :care-details="careDetails"></care-details-display>
                </b-card>
            </b-col>
        </b-row>

        <b-row v-if="client.medications" class="mt-5">
            <client-medication :client="client" :medications="client.medications" class="w-100" />
        </b-row>
    </div>

</template>

<script>
    import CareDetailsDisplay from '../CareDetailsDisplay'
    import CaregiverCarePlan from "./CaregiverCarePlan";

    export default {
        name: "CaregiverClientDetails",

        components: {CaregiverCarePlan, CareDetailsDisplay},

        props: {
            client: {
                required: true,
                type: Object,
            },
            address: {
                type: Object,
                default: () => ({}),
            },
            phone: {
                type: String,
                default: () => ({}),
            },
            carePlan: {
                type: Object,
                default: () => ({}),
            },
            careDetails: {
                type: Object,
                default: () => ({}),
            }
        }
    }
</script>

<style scoped>
    .care-details-scrollable{
        max-height:300px;
        overflow:auto;
    }
</style>