<template>
    <div class="form-check">
        <label class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" name="authorized" v-model="form.authorized" @change="updateStatus()">
            <span class="custom-control-indicator"></span>
        </label>

    </div>
</template>

<script>
    export default {
        props: {
            'item': Object,
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
