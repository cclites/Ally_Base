<template>
    <b-card class="mt-5">
        <b-row class="pt-5">
            <b-col md="3">
                <b-form-group label="Start Date">
                    <date-picker></date-picker>
                </b-form-group>
            </b-col>
            <b-col md="3">
                <b-form-group label="Start Date">
                    <date-picker></date-picker>
                </b-form-group>
            </b-col>
            <b-col md="3">
                <b-form-group label="Franchise">
                    <b-input-group>
                        <b-form-select v-model="filterFranchise">
                            <option value="asc">Franchise 1</option>
                            <option value="desc">Franchise 2</option>
                            <option value="last">Franchise 3</option>
                        </b-form-select>
                    </b-input-group>
                </b-form-group>
            </b-col>
            <b-col md="3">
                <b-form-group label="Franchise Location">
                    <b-input-group>
                        <b-form-select v-model="filterFranchiseLoc">
                            <option value="asc">Franchise location 1</option>
                            <option value="desc">Franchise location 2</option>
                            <option value="last">Franchise location 3</option>
                        </b-form-select>
                    </b-input-group>
                </b-form-group>
            </b-col>
        </b-row>
        <b-row>
            <b-table striped hover :items="items" :fields="fields">
                <template scope="row" slot="action">
                    <b-button variant="success" size="sm" class="mr-2">
                        Disperse
                    </b-button>
                    <b-button variant="danger" size="sm" class="mr-2">
                        Hold
                    </b-button>
                </template>
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
                filterFranchise: null,
                filterFranchiseLoc: null,
                fields: [ 'date', 'franchise', 'total_amount', 'royalty_owed', 'action'],
                items: [
                    { date: '10/1/18', franchise: 'Griswold Broward Fl', total_amount: '$10,350.00', royalty_owed: '$517.50'},
                    { date: '10/1/18', franchise: 'Griswold Broward Fl', total_amount: '$10,350.00', royalty_owed: '$517.50'},
                    { date: '10/1/18', franchise: 'Griswold Broward Fl', total_amount: '$10,350.00', royalty_owed: '$517.50'}
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
