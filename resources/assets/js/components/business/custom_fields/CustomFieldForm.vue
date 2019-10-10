<template>
    <div>
        <b-card
            header="Custom Fields"
            header-bg-variant="info"
            header-text-variant="white"
        >
            <loading-card v-if="loading" />
            <div v-else>
                <div v-if="customFields.length > 0">
                    <b-form-group
                        v-for="field in customFields"
                        :label="field.label"
                        :label-for="field.key"
                        :label-class="field.required ? 'required' : ''"
                        :key="field.id"
                    >
                        <b-form-input
                            v-if="field.type == 'input'"
                            :id="field.key"
                            :name="field.key"
                            type="text"
                            v-model="form[field.key]"
                            :required="!!field.required || null"
                        />

                        <b-form-textarea
                            v-if="field.type == 'textarea'"
                            :id="field.key"
                            :name="field.key"
                            v-model="form[field.key]"
                            rows="5"
                            :required="!!field.required || null"
                        />

                        <b-form-select
                            v-if="field.type == 'dropdown'"
                            :id="field.key"
                            :name="field.key"
                            v-model="form[field.key]"
                            :options="[dropdownDefault, ...field.options]"
                        />

                        <b-form-radio-group
                            v-if="field.type == 'radio'"
                            v-model="form[field.key]"
                            :id="field.key"
                            :name="field.key"
                            :required="!!field.required || null"
                        >
                            <b-form-radio :value="1">Yes</b-form-radio>
                            <b-form-radio :value="0">No</b-form-radio>
                        </b-form-radio-group>
                    </b-form-group>
                    <b-form-group>
                        <b-btn @click="update" :disabled="busy" variant="info">Save Custom Fields</b-btn>
                    </b-form-group>
                </div>
                <div v-else>
                    <p class="text-center">You have not set up any <a href="/business/settings#custom-fields">Custom Fields</a> for {{ this.userRole }}s.</p>
                </div>
            </div>
        </b-card>

        <b-row if="isAdmin">
            <b-col>
                <b-card header="Meta Data-visible to admins only"
                        header-bg-variant="warning"
                        header-text-variant="white">

                    <b-table bordered striped hover show-empty
                             :items="hiddenMeta"
                             :fields="metaFields">
                    </b-table>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
import AuthUser from '../../../mixins/AuthUser';

export default {
    mixins: [AuthUser],

    props: {
        userRole: {
            type: String,
            required: true,
        },
        userId: {
            type: Number,
            required: true,
        },
        meta: {
            type: Array,
            default: () => { return []; },
        },
    },

    async mounted() {
        this.loading = true;
        await this.fetchCustomFields();
        this.loading = false;
    },

    data() {
        return {
            loading: false,
            busy: false,
            form: new Form({}),
            customFields: [],
            dropdownDefault: {
                value: '',
                text: '--- Select ---',
                disabled: true,
            },
            metaFields: [
                { key: 'key', label: 'Key', sortable: true, },
                { key: 'value', label: 'Value', sortable: true, },
            ],
        };
    },

    computed: {
        hiddenMeta() {
            if (this.loading) {
                return [];
            }

            return this.meta.filter(x => {
                return this.customFields.findIndex(y => y.key == x.key) < 0;
            });
        },
    },

    methods: {
        async fetchCustomFields() {
            return await axios.get(`/business/custom-fields?type=${this.userRole}`)
                .then( ({ data }) => {
                    // Populate custom fields
                    let options = {};
                    data.forEach(({key, default_value}) => {
                        let userFieldValue = this.meta.find(field => key == field.key);
                        options[key] = userFieldValue ? userFieldValue.value : default_value;
                    });

                    this.customFields = data;
                    this.form = new Form(options);
                })
                .catch(() => {})
        },

        update() {
            this.busy = true;
            this.form.patch(`/business/${this.userRole}s/${this.userId}/meta`)
                .then(() => {
                })
                .catch(() => {})
                .finally(() => {
                    this.busy = false;
                });
        },

        filterMeta(data){
            this.meta = this.client.meta.filter(item1 =>
                !data.some(item2 => (item2.key === item1.key && item2.key === item1.key)));
        },
    },
}
</script>
