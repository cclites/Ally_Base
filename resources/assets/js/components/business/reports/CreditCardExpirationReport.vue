<template>
    <b-row>
        <b-col>
            <b-card title="Credit Card Expiration">
                <b-row>
                    <b-col>
                        <b-row>
                            <b-col lg="6">
                                <b-button-toolbar class="mb-1">
                                    <b-input-group size="sm" class="w-30 mx-1" left="Days from now">
                                        <b-form-input v-model="form.daysFromNow" type="number"></b-form-input>
                                    </b-input-group>
                                    <b-btn @click="fetchReportData">Search</b-btn>
                                </b-button-toolbar>
                            </b-col>
                        </b-row>
                        <b-table :items="cards"
                                 :fields="fields">
                        </b-table>
                        <div class="ml-2" v-if="cards.length == 0">No results.</div>
                    </b-col>
                </b-row>
            </b-card>
        </b-col>
    </b-row>
</template>

<script>
    export default {

        mixins: [],

        data() {
            return{
                cards: [],
                form: new Form({
                    daysFromNow: this.days
                }),
                fields: [
                    {
                        key: 'user',
                        formatter: (value) => { return value.name; }
                    },
                    'name_on_card',
                    'type',
                    'expiration_month',
                    'expiration_year',
                    {
                        key: 'expires_in',
                        label: 'Expires',
                        formatter: (value) => { return value + ' expiration'; }
                    }
                ]
            }
        },

        methods: {
            fetchReportData() {
                this.form.post('/business/reports/credit-cards')
                    .then((response) => {
                        this.cards = _.sortBy(response.data, 'user.name');
                    })
            }
        },

        computed: {

        }
    }
</script>