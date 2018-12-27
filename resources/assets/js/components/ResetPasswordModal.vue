<template>
    <b-modal title="Reset Password" v-model="showModal" @shown="focus">
        <b-container fluid>
            <b-row>
                <b-col sm="12">
                    <b-form-group label="New Password" label-for="password">
                        <b-form-input
                            ref="password"
                            id="password"
                            name="password"
                            type="password"
                            v-model="form.password"
                            >
                        </b-form-input>
                        <input-help :form="form" field="password" text="Enter the new password here"></input-help>
                    </b-form-group>
                    <b-form-group label="Confirm Password" label-for="password_confirmation">
                        <b-form-input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                v-model="form.password_confirmation"
                        >
                        </b-form-input>
                        <input-help :form="form" field="password_confirmation" text="Re-enter the new password for confirmation"></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
        </b-container>
        <div slot="modal-footer">
            <b-btn variant="default" @click="showModal=false">Close</b-btn>
            <b-btn variant="info" @click="save()">Reset Password</b-btn>
        </div>
    </b-modal>
</template>

<script>
    export default {
        props: {
            value: {},
            url: {},
        },

        data() {
            return {
                form: new Form({
                    password: null,
                    password_confirmation: null,
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
                let component = this;
                let method = 'patch';
                component.form.submit(method, this.url)
                    .then(function(response) {
                        component.showModal = false;
                    });
            },

            focus(e) {
                this.$refs.password.focus();
            }
        },
    }
</script>
