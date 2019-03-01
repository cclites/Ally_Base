<template>
    <b-row>
        <b-col>
            <b-card title="Credit Card Expiration">
                <b-row>
                    <b-col>
                        <b-row>
                            <b-col lg="6">
                                <b-button-toolbar class="mb-2">
                                    <b-input-group size="sm" class="w-30 mx-1" left="Days from now">
                                        <b-form-input v-model="form.daysFromNow" type="number"></b-form-input>
                                    </b-input-group>
                                    <b-btn @click="fetchReportData" variant="info">Search</b-btn>
                                </b-button-toolbar>
                            </b-col>
                            <b-col lg="6">
                                <b-form-checkbox 
                                    v-model="form.show_expired"
                                    :value="true"
                                    :unchecked-value="false"
                                >
                                    Include credit cards already expired
                                </b-form-checkbox>
                            </b-col>
                        </b-row>

                        <loading-card v-show="loading" />

                        <div v-show="! loading">
                            <div class="table-responsive">
                                <b-table :items="cards" show-empty :fields="fields">
                                    <template slot="user" scope="row">
                                        <a :href="`/business/clients/${row.item.id}`">{{ row.item.name }}</a>
                                    </template>
                                </b-table>
                            </div>
                        </div>
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
                    daysFromNow: this.days,
                    show_expired: true,
                }),
                loading: false,
                fields: [
                    {
                        key: 'user',
                        label: 'Client',
                    },
                    'name_on_card',
                    'type',
                    'expiration_month',
                    'expiration_year',
                    {
                        key: 'expires_in',
                        label: 'Expires',
                        formatter: (value) => value + ' expiration',
                    }
                ],
            }
        },

        computed: {
            items() {
                let result = [ ...this.cards ];

                if(!this.form.show_expired) {
                    result = result.filter(card => card.value.match('before'));
                }

                return result;
            },
        },

        methods: {
            fetchReportData() {
                this.loading = true;
                this.form.post('/business/reports/credit-cards')
                    .then((response) => {
                        this.cards = _.sortBy(response.data, 'user.name');
                        this.loading = false;
                    })
                    .catch(e => {
                        this.loading = false;
                    });
            }
        },
    }
</script>
