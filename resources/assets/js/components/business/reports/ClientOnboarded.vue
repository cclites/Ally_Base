<template>
    <b-row>
        <b-col>
            <b-card title="Client Onboarded Status">
                
                <loading-card v-show="loading"></loading-card>

                <div v-show="! loading" class="table-responsive">
                    <b-table :items="clients"
                            show-empty
                             :fields="fields">
                        <template slot="emailSentAt" scope="data">
                            <span v-if="data.item.user.email_sent_at">{{ formatDateTime(data.item.user.email_sent_at) }}</span>
                            <span v-else>Not Sent</span>
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
                clients: [],
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
                this.loading = true;
                axios.post('/business/reports/clients-onboarded')
                    .then(response => {
                        this.clients = _.sortBy(response.data, 'nameLastFirst');
                        this.loading = false;
                    })
                    .catch(error => {
                        this.loading = false;
                        console.error(error.response);
                });
            }
        }

    }
</script>
