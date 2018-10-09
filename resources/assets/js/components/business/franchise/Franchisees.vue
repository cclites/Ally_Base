<template>
    <b-card>
        <b-row>
            <b-col md="12" class="mb-2">
                <h3 class="">Latest Payments</h3>
            </b-col>
            <b-table striped hover :items="items" :fields="fields">

            </b-table>
        </b-row>
    </b-card>
</template>

<script>
    export default {
        props: {

        },

        data() {
            return {
                fields: [ 'name', 'location', 'address', 'phone_number', 'owner', 'last_active'],
                items: [
                    { name: 'Test', location: 'address', phone_number: '551285142', owner: 'xxx', last_active: '?'},
                    { name: 'Test', location: 'address', phone_number: '551285142', owner: 'xxx', last_active: '?'},
                    { name: 'Test', location: 'address', phone_number: '551285142', owner: 'xxx', last_active: '?'}
                ]
            }
        },

        mounted() {

        },

        computed: {

        },

        methods: {

        }
    }
</script>

<style scoped>

</style>