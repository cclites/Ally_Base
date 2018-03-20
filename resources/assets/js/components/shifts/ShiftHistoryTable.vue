<template>
    <div class="table-responsive">
        <b-table bordered striped hover show-empty
                 :fields="fields"
                 :items="items"
                 :sort-by.sync="sortBy"
                 :sort-desc.sync="sortDesc"
                 class="shift-table"
        >
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
            <template slot="Confirmed" scope="data">
                {{ (data.value) ? 'Yes' : (data.value === undefined) ? '' : 'No' }}
            </template>
            <template slot="actions" scope="row">
                <slot name="actions" :row="row"></slot>
            </template>
        </b-table>
    </div>
</template>

<script>
    export default {
        props: {
            items: Array,
            fields: Array,
        },

        data() {
            return {
                sortBy: 'Day',
                sortDesc: false,
            }
        },

        mounted() {

        },

        methods: {},
    }
</script>
