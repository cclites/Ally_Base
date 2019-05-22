<template>
    <b-form-group>
        <b-button variant="success" type="submit" size="" @click="save()" :disabled="readonly">Save Payment Method</b-button>
    </b-form-group>
</template>

<script>
    export default {
        props: {
            'submitUrl': String,
            'readonly': false,
        },

        data() {
            return {}
        },

        methods: {
            save() {
                let form = new Form({'use_trust': true});
                form.post(this.submitUrl)
                    .then((response) => {
                        this.$parent.onUpdatePaymentMethod(response.data.data);
                    });
            }
        },
    }
</script>
