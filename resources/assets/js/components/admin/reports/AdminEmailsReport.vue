<template>
    <b-card>
        <b-row>
            <b-col lg="12">
                <b-card header="Select a Report Type"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <b-form inline @submit.prevent="loadEmails()">
                        <b-select v-model="type">
                            <option value="caregiver_deposits">Caregiver Deposits</option>
                            <option value="client_payments">Client Payments</option>
                        </b-select>
                        <date-picker
                                v-model="date"
                                placeholder="Date"
                        >
                        </date-picker>
                        <b-button type="submit" variant="info" class="ml-2">Get Emails</b-button>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>
        <loading-card v-show="loading"></loading-card>

        <b-textarea :rows="10" v-show="!loading && emails" v-model="emails" readonly></b-textarea>
    </b-card>
</template>

<script>
    export default {

        props: {},

        data() {
            return {
                date: moment().format('MM/DD/YYYY'),
                type: 'caregiver_deposits',
                emails: null,
                loading: false
            }
        },

        mounted() {

        },

        methods: {
            loadEmails() {
                this.loading = true;
                axios.get(`/admin/reports/emails/${this.type}?date=${this.date}`)
                    .then(response => {
                        this.emails = response.data;
                        if (!this.emails) this.emails = 'No emails found.';
                        this.loading = false;
                    })
                    .catch(e => {
                        this.loading = false;
                    });
            },
        }
    }
</script>

<style>
    table:not(.form-check) {
        font-size: 13px;
    }
</style>
