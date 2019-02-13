<template>
    <div>
        <loading-card v-if="loading" text="Loading account information..."></loading-card>

        <form v-else @submit.prevent="submit()" @keydown="form.clearError($event.target.name)" autocomplete="off">
            <b-row>
                <b-col lg="6" offset-lg="3" offset-xs="0" xs="12">
                    <b-card header="Username &amp; Password Creation"
                            header-bg-variant="info"
                            header-text-variant="white"
                    >
                        <b-row>
                            <b-col>
                                <b-alert variant="info" show>Set your username and password. You will use your username and password to login to our system to manage your details and view invoice and payment history</b-alert>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col>
                                <b-form-group label="Username" label-for="username">
                                    <b-form-input
                                            id="username"
                                            name="username"
                                            type="text"
                                            v-model="form.username"
                                            :disabled="busy"
                                            required
                                            autocomplete="off"
                                    >
                                    </b-form-input>
                                    <input-help :form="form" field="username" text="Your username which will be used to logging in."></input-help>
                                </b-form-group>
                                <b-form-group label="Password" label-for="password" label-class="required">
                                    <b-form-input
                                            id="password"
                                            name="password"
                                            type="password"
                                            v-model="form.password"
                                            required
                                            :disabled="busy"
                                            autocomplete="new-password"
                                    >
                                    </b-form-input>
                                    <input-help :form="form" field="password" text="Enter a new password that will be used for logging in."></input-help>
                                </b-form-group>
                                <b-form-group label="Confirm Password" label-for="password_confirmation" label-class="required">
                                    <b-form-input
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            type="password"
                                            v-model="form.password_confirmation"
                                            required
                                            :disabled="busy"
                                            autocomplete="new-password"
                                    >
                                    </b-form-input>
                                    <input-help :form="form" field="password_confirmation" text="Re-enter the above password for confirmation."></input-help>
                                </b-form-group>
                            </b-col>
                        </b-row>
                    </b-card>
                    <b-row>
                        <b-col lg="12" class="text-right">
                            <b-button variant="success" size="lg" type="submit" :disabled="busy">
                                <i v-if="busy" class="fa fa-spinner fa-spin mr-2" size="lg"></i>
                                Save and Continue to Next Step
                            </b-button>
                        </b-col>
                    </b-row>
                </b-col>
            </b-row>
        </form>
    </div>
</template>

<script>
    export default {
        props: {
            'token': {},
            'client': {},
        },

        data() {
            return {
                busy: false,
                loading: false,
                terms: '',
                form: new Form({
                    username: this.client.username,
                    password: null,
                    password_confirmation: null,
                })
            }
        },

        async mounted() {
            this.loading = false;
        },

        methods: {
            submit() {
                this.busy = true;
                this.form.post(`/account-setup/clients/${this.token}/step2`)
                    .then( ({ data }) => {
                        this.$emit('updated', data.data);
                    })
                    .catch(e => {
                    })
                    .finally(() => {
                        this.busy = false;
                    });
            }
        }
    }
</script>
