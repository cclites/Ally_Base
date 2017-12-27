<template>
    <b-card
            header-tag="header">
        <div slot="header">
            <b-row>
                <b-col>
                    <h5>Payment Details</h5>
                </b-col>
                <b-col>
                    <div class="pull-right">
                        Total: {{ total }}
                    </div>
                </b-col>
            </b-row>
        </div>
        <b-table hover
                 :items="items"
                 :fields="fields">
            <template slot="checked_in_time" scope="data">
                {{ formatDateFromUTC(data.item.checked_in_time) }}
            </template>
            <template slot="care_time" scope="data">
                {{ formatTimeFromUTC(data.item.checked_in_time) }} - {{ formatTimeFromUTC(data.item.checked_out_time) }}
            </template>
            <template slot="caregiver_total" scope="data">
                {{ moneyFormat(parseFloat(data.item.caregiver_total)) }}
            </template>
        </b-table>
    </b-card>
</template>

<script>
    import FormatsDates from '../../mixins/FormatsDates';
    import FormatsNumbers from '../../mixins/FormatsNumbers';

    export default {
        props: ['shifts'],

        mixins: [FormatsDates, FormatsNumbers],

        computed: {
            total() {
                return this.moneyFormat(_.sumBy(this.items, (item) => { return parseFloat(item.caregiver_total); }));
            }
        },

        data() {
            return{
                items: this.shifts,
                fields: [
                    { key: 'checked_in_time', label: 'Care Date' },
                    { key: 'care_time', label: 'Care Time' },
                    { key: 'hours', label: 'Hours of Care Received' },
                    { key: 'client_name', label: 'Client Name' },
                    { key: 'caregiver_total', label: 'Amount' }
                ]
            }
        }
    }
</script>