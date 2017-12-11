<template>
    <b-row>
        <b-col>
            <b-card title="Caregiver Onboarded Status">
                <b-table :items="caregivers"
                         :fields="fields">
                    <template slot="emailSentAt" scope="data">
                        <span v-if="data.item.user.email_sent_at">{{ formatDateTime(data.item.user.email_sent_at) }}</span>
                        <span v-else>Not Sent</span>
                    </template>
                    <template slot="onboarded" scope="data">
                        <span v-if="data.item.onboarded">{{ formatDateTime(data.item.onboarded) }}</span>
                        <span v-else>Not Onboard</span>
                    </template>
                </b-table>
            </b-card>
        </b-col>
    </b-row>
</template>

<style lang="scss">
</style>

<script>
    import FormatsDates from '../../../mixins/FormatsDates';

    export default {

        mixins: [FormatsDates],

        data() {
            return{
                caregivers: [],
                fields: [
                    {
                        key: 'nameLastFirst',
                        label: 'Name'
                    },
                    {
                        key: 'emailSentAt'
                    },
                    {
                        key: 'onboarded'
                    }
                ]
            }
        },

        created() {
            this.fetchCaregiverData();
        },

        mounted() {

        },

        methods: {
            fetchCaregiverData() {
                axios.post('/business/reports/caregivers-onboarded')
                    .then(response => {
                        this.caregivers = response.data;
                    }).catch(error => {
                        console.error(error.response);
                    });
            }
        },

        computed: {

        }
    }
</script>