<template>
    <b-row>
        <b-col>
            <b-card title="Caregiver Onboarded Status">
                
                <loading-card v-show="loading"></loading-card>

                <div v-show="! loading" class="table-responsive">
                    <b-table :items="caregivers"
                            show-empty
                             :fields="fields">
                        <template slot="emailSentAt" scope="data">
                            <span v-if="data.item.user.email_sent_at">{{ formatDateTime(data.item.user.email_sent_at) }}</span>
                            <span v-else>Not Sent</span>
                        </template>
                        <template slot="onboarded" scope="data">
                            <span v-if="data.item.onboarded">Yes</span>
                            <span v-else>No</span>
                        </template>
                    </b-table>
                </div>
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
                loading: true,
                fields: [
                    {
                        key: 'nameLastFirst',
                        label: 'Name'
                    },
                    {
                        key: 'emailSentAt',
                        label: 'Email Sent'
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
                this.loading = true;
                axios.post('/business/reports/caregivers-onboarded')
                    .then(response => {
                        this.caregivers = _.sortBy(response.data, 'nameLastFirst');
                        this.loading = false;
                    })
                    .catch(error => {
                        this.loading = false;
                        console.error(error.response);
                    });
            }
        },

        computed: {

        }
    }
</script>
