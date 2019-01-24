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
                            :disabled="!!field"
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
                            :disabled="!!field"
                            required
                        >
                            <option value="">--Select--</option>
                            <option value="dropdown">List of Options</option>
                            <option value="radio">Yes/No Question</option>
                            <option value="input">Free text</option>
                            <option value="textarea">Long text</option>
                        </b-form-select>
                        <input-help :form="form" field="type" />
                    </b-form-group>
                    
                    <b-form-group label="The label to use for your field" label-for="label" label-class="required">
                        <b-form-input
                            id="label"
                            name="label"
                            type="text"
                            v-model="form.label"
                            placeholder="(e.g.: Favorite color)"
                            required
                        />
                        <input-help :form="form" field="label" />
                    </b-form-group>


                </b-col>
                <b-col lg="6">
                    <b-form-group label="Is this field required?" label-class="required">
                        <b-form-radio-group v-model="form.required" name="required" required>
                            <b-form-radio :value="1">Yes</b-form-radio>
                            <b-form-radio :value="0">No</b-form-radio>
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
                            <b-form-radio :value="1">Yes</b-form-radio>
                            <b-form-radio :value="0">No</b-form-radio>
                        </b-form-radio-group>
                        <input-help :form="form" field="default_value" :text="`Whenever an input is required then a default value will be given to all the existing ${form.user_type ? form.user_type : 'user'}s`" />
                    </b-form-group>

                    <div v-if="form.type == 'dropdown'">
                        <h3>Your list of options:</h3>
                        <p v-if="options.length == 0">You haven't created any yet. Please add them using the input below.</p>
                        <b-row v-else>
                            <b-col lg="12">
                                <b-btn 
                                    v-for="(option, i) in options"
                                    :key="i" 
                                    @click="deleteOption(i)"
                                    class="mr-2"
                                    title="Click this button to remove this option"
                                >{{stringFormat(option)}} <b-badge pill variant="danger" class="ml-2"><i class="fa fa-times" /></b-badge></b-btn>
                            </b-col>
                        </b-row>

                        <form @submit.prevent="addOption()">
                            <b-row class="pad-top">
                                    <b-col md="9">
                                        <b-form-group label="New option:">
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
                    <b-btn @click="destroy()" v-if="field" variant="danger">Delete custom field</b-btn>
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

        mounted() {
            if(this.field && this.field.options.length > 0) {
                this.options = this.field.options.map(field => field.label);
            }
        },

        data() {
            return {
                submitting: false,
                form: new Form({
                    user_type: this.getOriginal('user_type'),
                    type: this.getOriginal('type'),
                    label: this.getOriginal('label'),
                    required: this.getOriginal('required', false),
                    default_value: this.getOriginal('default_value'),
                }),
                options: [],
                optionInput: '',
            };
        },

        computed: {
            action() {
                return this.field ? 'edit' : 'create';
            },

            defaultOptions() {
                const options = [{value: '', text: '--- Select ---'}];
                this.options.forEach(text => options.push({ text, value: this.toSnakeCase(text) }));

                return options;
            },
        },

        methods: {

            getOriginal(attribute, defaultValue = '') {
                const defaultVal = this.field && this.field.required 
                    ? this.field.default_value
                    : defaultValue;

                return this.field ? this.field[attribute] : defaultVal;
            },

            addOption() {
                const value = this.optionInput.trim();
                const alreadyExist = this.options.some(option => option == value);

                if(!alreadyExist && value.length > 0) {
                    this.options.push(value);
                    this.optionInput = '';
                }
            },

            deleteOption(index) {
                this.options = this.options.filter((opt, i) => i != index);
            },

            async save() {
                this.submitting = true;

                if(this.form.type == 'dropdown' && this.options.length < 1) {
                    alert('You need more than 1 option for this list field to be valid.');
                    this.submitting = false;
                    return;
                }

                try {
                    // Create/update the custom field
                    const {data} = this.field 
                        ? await this.form.patch(`/business/custom-fields/${this.field.id}`)
                        : await this.form.post('/business/custom-fields');

                    // Create/update the custom dropdown field options
                    if(this.form.type == 'dropdown') {
                        const optionForm = new Form({ options: this.options.join(',') });
                        const res = this.field 
                            ? await optionForm.put(`/business/custom-fields/options/${this.field.id}`)
                            : await optionForm.post(`/business/custom-fields/options/${data.data.id}`);
                    }

                    if (! this.field) {
                        window.location.href = '/business/settings#custom-fields';
                    }
                } catch(error) {}
                this.submitting = false;
            },

            destroy() {
                if (!confirm(`Are you sure you wish to delete this field?`)) return;
                const form = new Form({});
                form.submit('delete', `/business/custom-fields/${this.field.id}`);
            },
        },

        watch: {
            form: {
                handler(newer, old) {
                    if(newer.type != old.type) {
                        this.form.default_value = '';
                        this.optionInput = '';
                        this.options = [];
                    }

                    if(!newer.required) {
                        this.default_value = '';
                    }
                },
                deep: true,
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