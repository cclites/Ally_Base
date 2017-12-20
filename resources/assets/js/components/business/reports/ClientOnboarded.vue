<template>
    <b-row>
        <b-col>
            <b-card title="Caregiver Onboarded Status">
                <b-table :items="clients"
                         :fields="fields">
                    <template slot="emailSentAt" scope="data">
                        <span v-if="data.item.user.email_sent_at">{{ formatDateTime(data.item.user.email_sent_at) }}</span>
                        <span v-else>Not Sent</span>
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
                clients: [],
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
                        key: 'onboard_status',
                        formatter: (value) => {
                            return _.startCase(value);
                        }
                    }
                ]
            }
        },

        created() {
            this.fetchClientData();
        },

        methods: {
            fetchClientData() {
                axios.post('/business/reports/clients-onboarded')
                    .then(response => {
                        this.clients = _.sortBy(response.data, 'nameLastFirst');
                    }).catch(error => {
                    console.error(error.response);
                });
            }
        }

    }
</script>