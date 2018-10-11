<template>
    <b-card class="mt-5">
        <b-row class="pt-5" v-if="false">
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

    import DemoFranchisor from "../../../classes/DemoFranchisor";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";

    export default {
        mixins: [FormatsNumbers],

        props: {

        },

        data() {
            return {
                filterFranchise: null,
                filterFranchiseLoc: null,
                fields: [
                    'date',
                    'office',
                    {
                        key: 'total_amount',
                        formatter: val => this.moneyFormat(val),
                    },
                    {
                        key: 'royalty_owed',
                        formatter: val => this.moneyFormat(val),
                    },
                    'action'
                ],
                items: DemoFranchisor.getPayments(),
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
