<template>
    <div>
        <b-form-group label="Nickname" label-for="name">
            <b-form-input
                    id="name"
                    name="name"
                    type="text"
                    v-model="form.name"
            >
            </b-form-input>
            <input-help :form="form" field="name" text="Optionally provide a nickname for this card."></input-help>
        </b-form-group>
        <b-form-group label="Card Number" label-for="number">
            <b-form-input
                    id="number"
                    name="number"
                    type="text"
                    v-model="form.number"
            >
            </b-form-input>
            <input-help :form="form" field="number" text="Provide your credit card number"></input-help>
        </b-form-group>
        <b-form-group label="Card Expiration" label-for="">
            <b-row>
                <b-col lg="6">
                    <b-form-select
                            v-model="form.expiration_month"
                            :options="months"
                    >
                    </b-form-select>
                    <input-help :form="form" field="number" text="Expiration Month"></input-help>
                </b-col>
                <b-col lg="6">
                    <b-form-select
                            v-model="form.expiration_year"
                            :options="years"
                    >
                    </b-form-select>
                    <input-help :form="form" field="number" text="Expiration Month"></input-help>
                </b-col>
            </b-row>
        </b-form-group>

    </div>
</template>

<script>
    export default {
        props: {
            'form': {},
        },

        data() {
            return {
                'year': [],
                'months': [],
                'years': [],
            }
        },

        computed: {

        },

        mounted() {
            this.year = parseInt(moment().format('Y'));
            this.years = _.range(this.year, this.year+11);
            this.months = _.range(1,13).map(function(value) {
                return _.padStart(value, 2, '0');
            });
        },

        methods: {

        }
    }
</script>