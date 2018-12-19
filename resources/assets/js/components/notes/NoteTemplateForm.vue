<template>
    <form @submit.prevent="submit()" @keydown="form.clearError($event.target.name)">
        <b-row>
            <b-col lg="12">
                <business-location-form-group v-model="form.business_id"
                                              form="form"
                                              field="business_id"
                                              help-text="">
                </business-location-form-group>
                <b-form-group label="Short Name" labe-for="short_name" id="short_name" name="short_name">
                    <b-form-input v-model="form.short_name" placeholder="Short Name" />
                </b-form-group>
                <b-form-group label="Active" labe-for="active" id="active" name="active">
                    <b-form-checkbox v-model="form.active">Active</b-form-checkbox>
                </b-form-group>
                <b-form-group label="Notes" labe-for="note">
                    <b-form-textarea
                            id="note"
                            name="note"
                            :rows="14"
                            v-model="form.note"
                    >
                    </b-form-textarea>
                </b-form-group>
            </b-col>
        </b-row>
    </form>
</template>

<script>
    import BusinessLocationFormGroup from "../business/BusinessLocationFormGroup";

    export default {
        components: {BusinessLocationFormGroup},

        props: {
            template: {
                type: Object,
                default: () => ({}),
            },
            modal: {
                type: Number,
                default: 0,
            }
        },

        data() {
            return {
                form: new Form({}),
                busy: false,
            }
        },

        mounted() {
            this.fillForm({});
            console.log('NoteTemplateForm mounted');
        },

        methods: {
            submit() {
                let path = '/note-templates';
                let method = 'post';

                if (this.template && this.template.id) {
                    path = '/note-templates/' + this.template.id;
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
                    business_id: data.business_id || "",
                    short_name: data.short_name || "",
                    note: data.note || "",
                    active: data.active || true,
                    modal: this.modal,
                });
            },
        },

        watch: {
            template(newVal, oldVal) {
                console.log('note template changed');
                this.fillForm(newVal);
            },
        },
    }
</script>
