<template>
    <b-row>
        <b-col md="12">
            <b-btn href="/business/quickbooks/connect">Connect your Quickbooks account</b-btn>



            <div>Ally requires various data mapping in order to properly sync with QuickBooks. You may be asked to login to your QuickBooks Online account to establish an initial connection.</div>
            <div class="mb-5">Please reach out to Ally support for assistance in setting this up.</div>
        </b-col>
        <b-col md="4" sm="4">
            <b-form-group horizontal :label-cols="4" label-size="md" label="Quickbooks Type:" label-for="quickbooks_type">
                <b-form-select v-model="selected" :options="options" id="quickbooks_type" />
            </b-form-group>
        </b-col>
        <b-col md="8" sm="8">
            <b-button :variant="'success'">Save</b-button>
        </b-col>
    </b-row>
</template>

<script>

    export default {

        data() {
            return {
                selected: 'disabled',
                options: [
                    { value: 'disabled', text: 'Disabled' },
                    { value: 'quickBooks_online', text: 'QuickBooks Online' },
                    // { value: 'quickBooks_desktop_2018', text: 'QuickBooks Desktop 2018' },
                    // { value: 'quickBooks_desktop_2017', text: 'QuickBooks Desktop 2017' },
                    // { value: 'quickBooks_desktop_2016', text: 'QuickBooks Desktop 2016' },
                    // { value: 'quickBooks_desktop_2015', text: 'QuickBooks Desktop 2015' },
                    // { value: 'quickBooks_desktop_older', text: 'QuickBooks Desktop Older' },
                ]
            }
        },

        computed: {

        },

        methods: {

        },

        mounted() {

        },

    }
</script>

<style>

</style>
