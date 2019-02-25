<template>
    <b-modal title="Send Welcome Email" v-model="showModal">
        <b-container fluid>
            <b-row>
                <b-col sm="12">
                    <strong>
                        Send Welcome Email to {{ user.email }}?
                    </strong>
                    <p>
                        When you send this email, the user will be instructed to click on a private link to confirm their information and reset their password.
                    </p>
                </b-col>
            </b-row>
        </b-container>
        <div slot="modal-footer">
            <b-btn variant="default" @click="showModal=false">Close</b-btn>
            <b-btn variant="info" @click="save()" :disabled="busy">Send Email</b-btn>
        </div>
    </b-modal>
</template>

<script>
    export default {
        props: {
            value: {},
            url: {},
            user: {},
        },

        data() {
            return {
                busy: false,
                form: new Form({
                }),
            }
        },

        computed: {
            showModal: {
                get() {
                    return this.value;
                },
                set(value) {
                    this.$emit('input', value);
                }
            },
        },

        methods: {
            save() {
                this.busy = true;
                let method = 'post';
                this.form.submit(method, this.url)
                    .then(response => {
                        this.showModal = false;
                    })
                    .finally(() => {
                        this.busy = false;
                    });
            }
        },
    }
</script>
