<template>
    <b-container fluid>
        <b-row>
            <b-col lg="12">
                <form @submit.prevent="submit()" @keydown="form.clearError($event.target.name)">
                    <b-form-group label="Client Type" label-for="client_type">
                        <client-type-dropdown id="client_type" name="client_type" v-model="form.client_type" :disabled="busy" empty-text="-- Select Client Type --" />
                        <input-help :form="form" field="client_type" text=""></input-help>
                    </b-form-group>

                    <b-form-group label="Question" label-for="question">
                        <b-form-input
                                id="question"
                                name="question"
                                type="text"
                                v-model="form.question"
                                maxlength="255"
                                :disabled="busy"
                        >
                        </b-form-input>
                        <input-help :form="form" field="question" text=""></input-help>
                    </b-form-group>

                    <b-form-group label="Required" label-for="required">
                        <b-form-radio-group id="required" v-model="form.required" name="required">
                            <b-form-radio value="0" :disabled="busy">No</b-form-radio>
                            <b-form-radio value="1" :disabled="busy">Yes</b-form-radio>
                        </b-form-radio-group>
                        <input-help :form="form" field="required" text=""></input-help>
                    </b-form-group>
                </form>
            </b-col>
        </b-row>
    </b-container>
</template>

<script>

    import Constants from '../../mixins/Constants';

    export default {

        mixins: [Constants],
        props: {
            question: {
                type: Object,
                default: () => { return {} },
            },
            business: {
                type: Object,
                default: () => { return {} },
            },
        },

        data() {
            return {
                form: new Form({}),
                busy: false,
            }
        },

        computed: {
        },

        methods: {
            submit() {
                let path = `/business/questions?business_id=${this.business.id}`;
                let method = 'post';

                if (this.question.id) {
                    path = `/business/questions/${this.question.id}?business_id=${this.business.id}`;
                    method = 'patch';
                }

                this.busy = true;
                return new Promise((resolve, reject) => {
                    this.form.submit(method, path)
                        .then( ({ data }) => {
                            this.busy = false;
                            resolve(data.data);
                        })
                        .catch(e => {
                            this.busy = false;
                            reject(e);
                        });
                });
            },

            fillForm(data) {
                this.form = new Form({
                    question: data.question,
                    required: data.required == 1 ? 1 : 0,
                    client_type: data.client_type ? data.client_type : '',
                });
            },
        },

        watch: {
            question(newVal, oldVal) {
                this.fillForm(newVal);
            },
        },

        mounted() {
            this.fillForm({});
        },
    }
</script>