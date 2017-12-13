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
                &dollar;{{ parseFloat(data.item.caregiver_rate) * parseFloat(data.item.duration) }}
            </template>
        </b-table>
    </b-card>
</template>

<script>
    import FormatsDates from '../../mixins/FormatsDates'
    export default {
        props: ['deposit'],

        mixins: [FormatsDates],

        data() {
            return{
                items: this.deposit.shifts,
                fields: [
                    { key: 'checked_in_time', label: 'Care Date' },
                    { key: 'care_time', label: 'Care Time' },
                    { key: 'duration', label: 'Hours of Care Received' },
                    { key: 'client_name', label: 'Client Name' },
                    { key: 'amount', label: 'Amount' }
                ]
            }
        }
    }
</script>