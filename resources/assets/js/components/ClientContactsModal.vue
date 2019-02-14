<template>
    <form @submit.prevent="submit()" @keydown="form.clearError($event.target.name)">
        <b-modal :title="title"
            v-model="showModal"
            size="lg"
            class="modal-fit-more"
            @cancel="onCancel"
        >
            <b-row class="">
                <b-col lg="6">
                    <b-form-group label="Contact Name" label-for="name" label-class="required">
                        <b-form-input v-model="form.name" type="text" required />
                        <input-help :form="form" field="name"></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <div slot="modal-footer">
                <b-button variant="success"
                    type="submit"
                    :disabled="loading"
                >
                    {{ buttonText }}
                </b-button>
                <b-btn variant="default" @click="showModal = false">Cancel</b-btn>
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
                loading: false,
                form: this.makeForm(this.source),
                showModal: this.value,
            }
        },

        computed: {
            title() {
                return (this.source.id) ? 'Edit Contact' : 'Add New Contact';
            },
            buttonText() {
                return (this.source.id) ? 'Save' : 'Create';
            },
        },

        methods: {
            makeForm(defaults = {}) {
                return new Form({
                    name: defaults.name,
                    email: defaults.email,
                });
            },
            submitForm() {
                this.loading = true;
                let method = this.source.id ? 'patch' : 'post';
                let url = this.source.id ? `/business/payers/${this.source.id}` : '/business/payers';
                this.form.submit(method, url)
                    .then(response => {
                        this.$emit('saved', response.data.data);
                        this.showModal = false;
                    })
                    .catch(e => {
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
</style>