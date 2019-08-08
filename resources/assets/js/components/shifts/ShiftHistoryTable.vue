<template>
    <div class="table-responsive">
        <b-table bordered striped hover show-empty
                 :fields="fields"
                 :items="items"
                 :sort-by.sync="sortBy"
                 :sort-desc.sync="sortDesc"
                 class="shift-table"
        >
            <template slot="Flags" scope="data">
                <i v-for="flag in data.value" :class="flagIcons[flag]" :title="flagTypes[flag]" :style="`color: ${flagColors[flag]}`" v-b-tooltip.hover></i>
            </template>
            <template slot="Day" scope="data">
                {{ data.value !== 'Total' ? dayFormat(data.value) : data.value }}
            </template>
            <template slot="Client" scope="row">
                <a :href="'/business/clients/' + row.item.client_id">{{ row.item.Client }}</a>
            </template>
            <template slot="Caregiver" scope="row">
                <a :href="'/business/caregivers/' + row.item.caregiver_id">{{ row.item.Caregiver }}</a>
            </template>
            <template slot="EVV" scope="data">
                <span v-if="data.value" style="color: green">
                    <i class="fa fa-check-square-o"></i>
                </span>
                <span v-else-if="data.value === undefined"></span>
                <span v-else style="color: darkred">
                    <i class="fa fa-times-rectangle-o"></i>
                </span>
            </template>
            <template slot="Confirmed" scope="row">

                <span v-if="row.item.Confirmed && row.item.client_confirmed == 1" v-tooltip:left="formatDateTimeFromUTC(row.item.confirmed_at) + confirmedByClient">Client</span>
                <span v-else-if="row.item.Confirmed" v-tooltip:left="formatDateTimeFromUTC(row.item.confirmed_at) + confirmedByAdmin">Yes</span>
                <span v-else>{{ (row.item.Confirmed === undefined) ? '' : 'No' }}</span>

            </template>
            <template slot="Charged" scope="row">
                <span v-if="row.item.Charged" v-tooltip:left="formatDateTimeFromUTC(row.item.charged_at)">Yes</span>
                <span v-else>{{ (row.item.Charged === undefined) ? '' : 'No' }}</span>
            </template>
            <template slot="Services" scope="row">
                <div v-for="service in row.item.Services" :key="service">
                    {{ service }}
                </div>
            </template>
            <template slot="actions" scope="row">
                <slot name="actions" :item="row.item"></slot>
            </template>
        </b-table>
    </div>
</template>

<script>
    import FormatsDates from "../../mixins/FormatsDates";
    import ShiftFlags from "../../mixins/ShiftFlags";

    export default {
        mixins: [FormatsDates, ShiftFlags],

        props: {
            items: Array,
            fields: Array,
        },

        data() {
            return {
                sortBy: 'Day',
                sortDesc: false,
                confirmedByAdmin: '  admin@allyms.com',
                confirmedByClient: '  Confirmed by Client',
            }
        },

        mounted() {

        },

        methods: {
            dayFormat(date) {
                return moment.utc(date).local().format('ddd MMM D');
            },
        },
    }
</script>
