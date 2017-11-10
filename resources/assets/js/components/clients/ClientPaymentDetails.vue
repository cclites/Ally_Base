<template>
    <b-card title="Payment Details">
        <b-table hover
                 :items="items"
                 :fields="fields">
            <template slot="checked_in_time" scope="data">
                {{ formatDate(data.item.checked_in_time) }}
            </template>
            <template slot="care_time" scope="data">
                {{ formatTime(data.item.checked_in_time) }} - {{ formatTime(data.item.checked_out_time) }}
            </template>
            <template slot="client_name" scope="data">
                {{ data.item.client.name }}
            </template>
            <template slot="amount" scope="data">
                &dollar;{{ parseFloat(data.item.caregiver_rate) * parseFloat(data.item.roundedShiftLength) }}
            </template>
            <template slot="actions" scope="data">
                <a :href="'/payment-history/' + data.item.id + '/print'" target="_blank">
                    Print
                </a>
            </template>
        </b-table>
    </b-card>
</template>

<style lang="scss">
</style>

<script>
    import FormatsDates from '../../mixins/FormatsDates';

    export default {
        props: ['payment'],

        mixins: [FormatsDates],

        data() {
            return{
                items: this.payment.shifts,
                fields: [
                    { key: 'checked_in_time', label: 'Care Date' },
                    { key: 'care_time', label: 'Care Time' },
                    { key: 'roundedShiftLength', label: 'Hours of Care Received' },
                    { key: 'client_name', label: 'Client Name' },
                    { key: 'amount', label: 'Amount' },
                    'actions'
                ]
            }
        }
    }
</script>