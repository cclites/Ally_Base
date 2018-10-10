<template>
    <b-card>
        <b-row>
            <b-col md="12" class="mb-2">
                <h3 class="">Offices</h3>
            </b-col>
            <b-table striped hover :items="items" :fields="fields">

            </b-table>
        </b-row>
    </b-card>
</template>

<script>
    import DemoFranchisor from "../../../classes/DemoFranchisor";

    export default {
        props: {

        },

        data() {
            return {
                fields: [ 'name', 'address', 'phone_number', 'owner', 'last_active'],
                items: DemoFranchisor.getFranchises(),
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
