<template>
    <div>
        <b-button :variant="variant" :disabled="disabled" @click="chargeClient()">{{ text }}</b-button>
    </div>
</template>

<script>
    export default {
        props: {
            'item': Object,
            'startDate': String,
            'endDate': String,
        },

        data() {
            return {
                'form': new Form({
                    start_date: this.startDate,
                    end_date: this.endDate,
                }),
                'variant': 'info',
                'disabled': false,
                'text': 'Charge!',
            }
        },

        mounted() {

        },

        methods: {

            chargeClient() {
                this.disabled = true;
                this.form.post('/admin/charges/client/' + this.item.client_id)
                    .then(response => {
                        this.variant = 'success';
                        this.text = 'Charged';
                    })
                    .catch(error => {
                        this.variant = 'danger';
                        this.text = 'Failed';
                    });
            }

        },

        watch: {
            'item.authorized': function(val) {
                this.form.authorized = val;
            }
        }
    }
</script>
