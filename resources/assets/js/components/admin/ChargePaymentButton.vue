<template>
    <div>
        [CHARGE BUTTON]
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
                'form': new Form({ authorized: this.item.authorized }),
            }
        },

        mounted() {

        },

        methods: {

            updateStatus() {
                let authorized = this.form.authorized;
                this.form.post('/admin/charges/pending_shifts/' + this.item.shift_id)
                    .then(response => {
                        this.item.authorized = authorized;
                        if (authorized) {
                            this.item.verified = true;
                        }
                    })
                    .catch(response => {
                        this.form.authorized = !authorized;
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
