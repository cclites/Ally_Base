<template>
    <form @submit.prevent="submit()" @keydown="form.clearError($event.target.name)">
        <b-form-group label="Nickname" label-for="nickname">
            <b-form-input
                    id="nickname"
                    name="nickname"
                    type="text"
                    v-model="form.nickname"
            >
            </b-form-input>
            <input-help :form="form" field="nickname" text="Optionally provide a nickname for this card."></input-help>
        </b-form-group>
        <b-form-group label="Name on Card" label-for="name_on_card">
            <b-form-input
                id="name_on_card"
                name="name_on_card"
                type="text"
                v-model="form.name_on_card"
                >
            </b-form-input>
            <input-help :form="form" field="name_on_card" text="Please enter your name, as it appears on the card."></input-help>
        </b-form-group>
        <b-row>
            <b-col lg="6">
                <b-form-group label="Card Number" label-for="number">
                    <b-form-input
                            id="number"
                            name="number"
                            type="text"
                            autocomplete="off"
                            v-model="form.number"
                    >
                    </b-form-input>
                    <input-help :form="form" field="number" text="Provide your credit card number"></input-help>
                </b-form-group>
            </b-col>
            <b-col lg="6" >
                <b-form-group label="Confirm Card Number" label-for="number_confirmation">
                    <b-form-input
                            id="number_confirmation"
                            name="number_confirmation"
                            type="text"
                            autocomplete="off"
                            v-model="form.number_confirmation"
                    >
                    </b-form-input>
                    <input-help :form="form" field="number_confirmation" text="Re-enter your credit card number"></input-help>
                </b-form-group>
            </b-col>
        </b-row>
        <b-row>
            <b-col lg="5">
                <b-form-group label="CVV" label-for="cvv">
                    <b-form-input
                            id="cvv"
                            name="cvv"
                            type="text"
                            autocomplete="off"
                            v-model="form.cvv"
                    >
                    </b-form-input>
                    <input-help :form="form" field="cvv" text="The code on the back of the card"></input-help>
                </b-form-group>
            </b-col>
        </b-row>
        <b-form-group label="Card Expiration" label-for="">
            <b-row>
                <b-col lg="6">
                    <b-form-select
                            v-model="form.expiration_month"
                            :options="months"
                    >
                    </b-form-select>
                    <input-help :form="form" field="expiration_month" text="Expiration Month"></input-help>
                </b-col>
                <b-col lg="6">
                    <b-form-select
                            v-model="form.expiration_year"
                            :options="years"
                    >
                    </b-form-select>
                    <input-help :form="form" field="expiration_year" text="Expiration Year"></input-help>
                </b-col>
            </b-row>
        </b-form-group>
        <b-form-group>
            <b-button variant="success" type="submit" size="">Save Credit Card</b-button>
        </b-form-group>
    </form>
</template>

<script>
    export default {
        props: {
            'submitUrl': '',
            'client': {},
            'card': {},
            'source': {},
        },

        data() {
            return {
                'year': [],
                'months': [],
                'years': [],
                'form': new Form({
                    nickname: this.card.nickname,
                    name_on_card: this.card.name_on_card,
                    number: (this.card.last_four) ? '************ ' + this.card.last_four : '',
                    number_confirmation: '',
                    expiration_month: _.padStart(this.card.expiration_month, 2, '0'),
                    expiration_year: this.card.expiration_year,
                    cvv: (this.card.last_four) ? '***' : '',
                }),
            }
        },

        mounted() {
            this.year = parseInt(moment().format('Y'));
            this.years = _.range(this.year, this.year+11);
            this.months = _.range(1,13).map(function(value) {
                return _.padStart(value, 2, '0');
            });
        },

        methods: {
            submit() {
                this.form.post(this.submitUrl)
<<<<<<< HEAD
                    .then((response) => {
                        this.form.number = '************ ' + this.form.number.slice(-4);
                        this.form.cvv = '***';
                        this.$parent.typeMessage = response.data;
=======
                    .then(function(response) {
                        component.form.number = '************ ' + component.form.number.slice(-4);
                        component.form.number_confirmation = '';
                        component.form.cvv = '***';
>>>>>>> remotes/origin/ALLY-206-confirm-account-numbers-on-add
                    });
            }
        }
    }
</script>