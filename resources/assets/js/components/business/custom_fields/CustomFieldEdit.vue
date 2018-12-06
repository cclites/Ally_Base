<template>
    <b-card :header="`${uppercaseWords(action)} custom field`" header-bg-variant="info" header-text-variant="white">
        <form @submit.prevent="save()" @keydown="form.clearError($event.target.name)">
            <b-row>
                <b-col lg="6">
                    <b-form-group label="On which account type will this field be applied?" label-for="user_type" label-class="required">
                        <b-form-select
                            id="user_type"
                            name="user_type"
                            v-model="form.user_type"
                        >
                            <option value="">--Select--</option>
                            <option value="client">Clients</option>
                            <option value="caregiver">Caregivers</option>
                        </b-form-select>
                        <input-help :form="form" field="user_type" />
                    </b-form-group>

                    <b-form-group label="What type of field should it be?" label-for="type" label-class="required">
                        <b-form-select
                            id="type"
                            name="type"
                            v-model="form.type"
                            required
                        >
                            <option value="">--Select--</option>
                            <option value="dropdown">List of Optionns</option>
                            <option value="radio">Yes/No Question</option>
                            <option value="input">Free text</option>
                            <option value="textarea">Long text</option>
                        </b-form-select>
                        <input-help :form="form" field="type" />
                    </b-form-group>
                    
                    <b-form-group label="The field label" label-for="label" label-class="required">
                        <b-form-input
                            id="label"
                            name="label"
                            type="text"
                            v-model="form.label"
                            placeholder="(e.g.: Your High School graduation year)"
                            required
                        />
                        <input-help :form="form" field="label" />
                    </b-form-group>


                </b-col>
                <b-col lg="6">
                    <b-form-group label="Is this field required?" label-class="required">
                        <b-form-radio-group v-model="form.required" name="required" required>
                            <b-form-radio :value="true">Yes</b-form-radio>
                            <b-form-radio :value="false">No</b-form-radio>
                        </b-form-radio-group>
                        <input-help :form="form" field="required" />
                    </b-form-group>

                    <b-form-group v-if="form.required" label="The default value" label-for="default_value" label-class="required">
                        <b-form-input 
                            v-if="form.type == 'input'"
                            id="default_value"
                            name="default_value"
                            type="text"
                            v-model="form.default_value"
                            required
                        />

                        <b-form-textarea
                            v-if="form.type == 'textarea'"
                            id="default_value"
                            name="default_value"
                            v-model="form.default_value"
                            rows="5"
                            required
                        />

                        <b-form-select 
                            v-if="form.type == 'dropdown'"
                            id="default_value"
                            name="default_value"
                            v-model="form.default_value"
                            :options="defaultOptions"
                        />

                        <b-form-radio-group
                            v-if="form.type == 'radio'"
                            v-model="form.default_value" 
                            name="default_value" 
                            required
                        >
                            <b-form-radio :value="true">Yes</b-form-radio>
                            <b-form-radio :value="false">No</b-form-radio>
                        </b-form-radio-group>
                        <input-help :form="form" field="default_value" :text="`Whenever an input is required then a default value will be given to all the existing ${form.user_type ? form.user_type : 'user'}s`" />
                    </b-form-group>

                    <div>
                        <h3>Your list options:</h3>
                        <p>You have created any yet. Please add them using the input below.</p>
                        <b-row>
                            <b-col lg="12">
                                <b-btn 
                                    v-for="(option, i) in options"
                                    :key="i" 
                                    @click="deleteOption(i)"
                                    class="mr-2"
                                    title="Click this button to remove this option"
                                >{{option}} <b-badge pill variant="danger" class="ml-2"><i class="fa fa-times" /></b-badge></b-btn>
                            </b-col>
                        </b-row>

                        <form @submit.prevent="addOption()">
                            <b-row>
                                    <b-col md="9">
                                        <b-form-group label="New option">
                                            <b-form-input 
                                                type="text"
                                                v-model="optionInput"
                                                maxlength="30"
                                                autocomplete="off" 
                                                placeholder="Enter your new list option here"
                                            />
                                        </b-form-group>
                                    </b-col>
                                    <b-col md="3" class="add-button">
                                        <b-btn type="submit" variant="info" @click="addOption()">Add</b-btn>
                                    </b-col>
                            </b-row>
                        </form>
                    </div>
                </b-col>
            </b-row>
            <hr />
            <b-row>
                <b-col lg="12">
                    <submit-button :submitting="submitting" variant="success" type="submit">
                        Save
                    </submit-button>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>
    import FormatsStrings from '../../../mixins/FormatsStrings';
    
    export default {

        props: {
            field: {
                type: Object,
                default: () => null,
            },
        },

        mixins: [FormatsStrings],

        data() {
            return {
                submitting: false,
                form: new Form({
                    user_type: '',
                    type: '',
                    label: '',
                    required: false,
                    default_value: '',
                }),
                options: [],
                optionInput: '',
            };
        },

        computed: {
            action() {
                return this.field ? 'edit' : 'create';
            },
        },

        mounted() {
        },

        methods: {

            getOriginal(field, defaultValue = "") {
                return this.prospect ? this.prospect[field] : defaultValue;
            },

            getOriginalDate(field, defaultValue = "") {
                return this.prospect && this.prospect[field] ? moment(this.prospect[field]).format('MM/DD/YYYY') : defaultValue;
            },

            addOption() {
                const alreadyExist = this.options.some(opt => opt == this.optionInput);

                if(!alreadyExist && this.optionInput.trim().length > 0) {
                    this.options.push(this.optionInput.trim());
                    this.optionInput = '';
                }
            },

            deleteOption(index) {
                this.options = this.options.filter((opt, i) => i != index);
            },

            async save() {
                this.submitting = true;
                try {
                    let response;
                    if (this.prospect) {
                        response = await this.form.patch(`/business/prospects/${this.prospect.id}`);
                    }
                    else {
                        response = await this.form.post('/business/prospects');
                    }
                }
                catch(error) {}
                this.submitting = false;
            },

            destroy(item) {
                if (!confirm(`Are you sure you wish to delete?`)) return;
                const form = new Form({});
                form.submit('delete', `/business/prospects/${item.id}`);
            },
        },
    }
</script>

<style scoped>
    .pad-top {
        padding-top: 16px;
    }

    .add-button {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
</style>