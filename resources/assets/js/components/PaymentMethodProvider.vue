<template>
    <b-form-group>
        <b-button variant="success" type="submit" size="" @click="save()">Save Payment Method</b-button>
    </b-form-group>
</template>

<script>
    export default {
        props: {
            'submitUrl': String,
        },

        data() {
            return {}
        },

        methods: {
            save() {
                let form = new Form({'use_business': true});
                form.post(this.submitUrl)
                    .then((response) => {
                        this.$parent.onUpdatePaymentMethod(response.data);
                    });
            }
        },
    }
</script>
