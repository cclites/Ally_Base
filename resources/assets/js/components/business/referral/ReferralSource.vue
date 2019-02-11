<template>
    <b-card :header="title"
        header-bg-variant="info"
        header-text-variant="white"
        >
        <form @submit.prevent="submitForm()" @keydown="form.clearError($event.target.name)">
            <b-container fluid>
                    <b-row>
                        <b-col lg="12">
                            <b-form-group label="Organization Name" label-for="organization" label-class="required">
                                <b-form-input v-model="form.organization" type="text" required />
                                <input-help :form="form" field="organization"></input-help>
                            </b-form-group>
                            <b-form-group label="Contact Name" label-for="name" label-class="required">
                                <b-form-input v-model="form.contact_name" type="text" required />
                                <input-help :form="form" field="contact_name"></input-help>
                            </b-form-group>
                            <b-form-group label="Phone Number" label-for="phone">
                                <b-form-input v-model="form.phone" type="text" />
                                <input-help :form="form" field="phone"></input-help>
                            </b-form-group>
                        </b-col>
                    </b-row>
            </b-container>
            <div slot="modal-footer">
                <b-button variant="success"
                        type="submit"
                        :disabled="loading"
                >
                    {{ buttonText }}
                </b-button>
            </div>
        </form>
    </b-card>
</template>

<script>
    export default {
        props: {
            source: Object,
        },

        data() {
            return {
                form: this.makeForm(this.source),
                loading: false,
            }
        },

        computed: {
            title() {
                return (this.source.id) ? 'Edit Referral Source' : 'Add New Referral Source';
            },
            buttonText() {
                return (this.source.id) ? 'Save' : 'Create';
            },
        },

        methods: {
            makeForm(defaults = {}) {
                return new Form({
                    organization: defaults.organization,
                    contact_name: defaults.contact_name,
                    phone: defaults.phone,
                    type: defaults.type,
                });
            },

            submitForm() {
                this.loading = true;
                let method = this.source.id ? 'patch' : 'post';
                let url = this.source.id ? `/business/referral-sources/${this.source.id}` : '/business/referral-sources';
                this.form.submit(method, url)
                    .then(response => {
                        this.$emit('saved', response.data.data);
                    })
                    .finally(() => this.loading = false)
            },
        },

        watch: {
        }
    }
</script>

<style scoped>
    .loader {
        border: 8px solid #f3f3f3;
        border-radius: 50%;
        border-top: 8px solid #3498db;
        width: 50px;
        height: 50px;
        -webkit-animation: spin-data-v-7012acc5 2s linear infinite;
        animation: spin-data-v-7012acc5 2s linear infinite;
        margin: 0 auto;
    }

    /* Safari */
    @-webkit-keyframes spin {
        0% { -webkit-transform: rotate(0deg); }
        100% { -webkit-transform: rotate(360deg); }
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .error-msg {
        margin-top: 7px;
        color: red;
    }
</style>