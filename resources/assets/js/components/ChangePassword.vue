<template>
    <b-card header="Password"
        header-bg-variant="info"
        header-text-variant="white"
        >
        <form @submit.prevent="savePassword()" @keydown="form.clearError($event.target.name)">
            <b-row>
                <b-col lg="5" sm="4" xs="12">
                    <b-form-group label="New Password" label-for="password">
                        <b-form-input
                                id="password"
                                name="password"
                                type="password"
                                v-model="form.password"
                        >
                        </b-form-input>
                        <input-help :form="form" field="password" text="Enter a new password."></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="5" sm="4" xs="12">
                    <b-form-group label="Confirm Password" label-for="password">
                        <b-form-input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                v-model="form.password_confirmation"
                        >
                        </b-form-input>
                        <input-help :form="form" field="password_confirmation" text="Confirm your new password."></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="2" sm="4" xs="12">
                    <b-form-group>
                        <label class="col-form-label col-12 hidden-xs-down"><span>&nbsp;</span></label>
                        <b-button id="save-profile" variant="success" type="submit">Save Password</b-button>
                    </b-form-group>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>
    export default {
        props: {
            'user': {}
        },

        data() {
            return {
                form: new Form({
                    password: null,
                    password_confirmation: null,
                })
            }
        },

        mounted() {

        },

        methods: {

            savePassword() {
                if (this.user && this.user.id) {
                    // Update another's password
                }
                else {
                    // Update auth'd password
                    this.form.resetOnSuccess = true;
                    this.form.post('/profile/password');
                }
            }

        }


    }
</script>
