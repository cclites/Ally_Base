<template>
    <form @submit.prevent="submitForm()" @keydown="form.clearError($event.target.name)">
        <b-modal id="filterColumnsModal" 
            :title="title"
            v-model="showModal"
            size="lg"
            class="modal-fit-more"
            @cancel="onCancel"
        >
            <b-row class="mb-2 p-2">
                <b-col lg="6">
                    <b-form-group label="Payer Name" label-for="name" label-class="required">
                        <b-form-input v-model="form.name" type="text" required />
                        <input-help :form="form" field="name"></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="NPI Number" label-for="npi_number">
                        <b-form-input v-model="form.npi_number" type="text" />
                        <input-help :form="form" field="npi_number"></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <business-payer-rates-table 
                        ref="ratesTable"
                        :rates="form.rates" 
                        :services="services"
                    ></business-payer-rates-table>
                </b-col>
            </b-row>
            <div slot="modal-footer">
                <b-button variant="success"
                        type="submit"
                        :disabled="loading"
                >
                    {{ buttonText }}
                </b-button>
                <b-btn variant="default" @click="showModal=false">Cancel</b-btn>
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
            services: Array,
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
                return (this.source.id) ? 'Edit Payer' : 'Add New Payer';
            },
            buttonText() {
                return (this.source.id) ? 'Save' : 'Create';
            },
        },

        methods: {
            makeForm(defaults = {}) {
                return new Form({
                    name: defaults.name,
                    npi_number: defaults.npi_number,
                    rates: defaults.rates,
                });
            },

            submitForm() {
                this.loading = true;
                this.form.rates = this.$refs.ratesTable.items;
                let method = this.source.id ? 'patch' : 'post';
                let url = this.source.id ? `/business/payers/${this.source.id}` : '/business/payers';
                this.form.submit(method, url)
                    .then(response => {
                        this.$emit('saved', response.data.data);
                        this.showModal = false;
                    })
                    .finally(() => this.loading = false)
            },

            onCancel() {
                this.value = {};
            },
        },

        watch: {
            value(val) {
                if (! val) {
                    // clear the form on close so the data updates if the
                    // edit modal is opened again for the same object.
                    this.form = this.makeForm({});
                } else {
                    this.form = this.makeForm(this.source);
                }
                this.showModal = val;
            },
            showModal(val) {
                this.$emit('input', val);
            }
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