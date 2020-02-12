<template>
    <form @submit.prevent="submitForm()" @keydown="form.clearError($event.target.name)">
    <b-modal id="filterColumnsModal" :title="title" v-model="showModal">
        <b-container fluid>
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Service Name" label-for="name" label-class="required">
                        <b-form-input v-model="form.name" type="text" required />
                        <input-help :form="form" field="name"></input-help>
                    </b-form-group>
                    <b-form-group label="HCPCS Code" label-for="code">
                        <b-form-input v-model="form.code" type="text" />
                        <input-help :form="form" field="code"></input-help>
                    </b-form-group>
                    <div class="d-flex justify-content-between">

                        <b-form-group label="Mod One" label-for="mod1">
                            <b-form-input v-model="form.mod1" type="text" />
                            <input-help :form="form" field="mod1"></input-help>
                        </b-form-group>
                        <b-form-group label="Mod Two" label-for="mod2">
                            <b-form-input v-model="form.mod2" type="text" />
                            <input-help :form="form" field="mod2"></input-help>
                        </b-form-group>
                    </div>
                    <div class="form-check">
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="default" v-model="form.default" value="1">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description">Default Service for Billing</span>
                        </label>
                    </div>
                </b-col>
            </b-row>
        </b-container>
        <div slot="modal-footer" class="w-100">
            <div class="d-flex">
                <div class="f-1 text-left">
                    <a href="https://hcpcscodes.org/" target="_blank">HCPCS Code Lookup</a>
                </div>
                <div class="ml-auto">
                    <b-button variant="success"
                              type="submit"
                              :disabled="loading"
                    >
                        {{ buttonText }}
                    </b-button>
                    <b-btn variant="default" @click="showModal=false">Close</b-btn>
                </div>
            </div>
        </div>
    </b-modal>
    </form>
</template>

<script>
    export default {
        components: {},

        props: {
            value: Boolean,
            source: Object,
        },

        data() {
            return {
                form: this.makeForm(this.source),
                loading: false,
                showModal: this.value,
            }
        },

        computed: {
            title() {
                return (this.source.id) ? 'Edit Service' : 'Add New Service';
            },
            buttonText() {
                return (this.source.id) ? 'Save' : 'Create';
            },
        },

        methods: {
            makeForm(defaults = {}) {
                return new Form({
                    name: defaults.name,
                    code: defaults.code,
                    mod1: defaults.mod1,
                    mod2: defaults.mod2,
                    default: defaults.default
                });
            },

            submitForm() {
                this.loading = true;
                let method = this.source.id ? 'patch' : 'post';
                let url = this.source.id ? `/business/services/${this.source.id}` : '/business/services';
                this.form.submit(method, url)
                    .then(response => {
                        this.$emit('saved', response.data.data);
                        // this.showModal = false;
                        this.$emit('input', false)
                    })
                    .finally(() => this.loading = false)
            },
        },

        watch: {
            value(val) {
                this.form = this.makeForm(this.source);
                this.showModal = val;
            },
            // showModal(val) {
            //     this.$emit('input', val);
            //     this.$emit('saved', response.data.data);
            // }
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