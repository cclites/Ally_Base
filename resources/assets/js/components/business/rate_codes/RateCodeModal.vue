<template>
    <form @submit.prevent="submitForm()" @keydown="form.clearError($event.target.name)">
        <b-modal :title="title" v-model="showModal">
            <b-container fluid>
                <b-row>
                    <b-col lg="12">
                        <b-form-group label="Name" label-for="name">
                            <b-form-input v-model="form.name" type="text" />
                            <input-help :form="form" field="name"></input-help>
                        </b-form-group>
                        <b-form-group label="Type" label-for="type">
                            <b-select v-model="form.type" :disabled="typeLocked">
                                <option value="">Select a Type</option>
                                <option v-for="type in rateTypes" :value="type" :key="type">{{ uppercaseWords(type) }}</option>
                            </b-select>
                            <input-help :form="form" field="type"></input-help>
                        </b-form-group>
                        <b-form-group label="Rate" label-for="rate">
                            <b-form-input v-model="form.rate" type="number" step="any" />
                            <input-help :form="form" field="rate"></input-help>
                        </b-form-group>
                        <b-form-group label="Method" label-for="fixed">
                            <b-select v-model="form.fixed">
                                <option :value="0">Hourly</option>
                                <option :value="1">Fixed</option>
                            </b-select>
                            <input-help :form="form" field="fixed"></input-help>
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
                <b-btn variant="default" @click="showModal=false">Close</b-btn>
            </div>
        </b-modal>
    </form>
</template>

<script>
    import FormatsStrings from "../../../mixins/FormatsStrings";

    export default {
        mixins: [FormatsStrings],

        props: {
            value: Boolean,
            code: Object,
            typeLocked: Boolean,
        },

        data() {
            return {
                rateTypes: ['client', 'caregiver'],
                form: this.makeForm(this.code),
                loading: false,
                showModal: this.value,
            }
        },

        computed: {
            title() {
                return (this.code.id) ? 'Edit Rate Code' : 'Add New Rate Code';
            },
            buttonText() {
                return (this.code.id) ? 'Save' : 'Create';
            },
        },

        methods: {
            makeForm(defaults = {}) {
                return new Form({
                    name: defaults.name || "",
                    type: defaults.type || "",
                    rate: defaults.rate || "0.00",
                    fixed: defaults.fixed || 0,
                });
            },

            submitForm() {
                this.loading = true;
                let method = this.code.id ? 'patch' : 'post';
                let url = this.code.id ? `/business/rate-codes/${this.code.id}` : '/business/rate-codes';
                this.form.submit(method, url)
                    .then(response => {
                        this.$emit('saved', response.data.data);
                        this.showModal = false;
                    })
                    .finally(() => this.loading = false)
            },
        },

        watch: {
            value(val) {
                this.form = this.makeForm(this.code);
                this.showModal = val;
            },
            showModal(val) {
                this.$emit('input', val);
            }
        }
    }
</script>

<style scoped>

</style>