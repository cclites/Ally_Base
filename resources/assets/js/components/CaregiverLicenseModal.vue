<template>
    <b-modal :title="title" v-model="showModal">
        <b-container fluid>
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Select Expiration Type" label-for="expirations">
                        <b-form-select
                                name="expirations"
                                id="expirations"
                                v-model="expiration"
                        >
                            <option value="">-- Use Custom Name --</option>
                            <option v-for="item in expirations" :value="item.type">{{ item.type }}</option>
                        </b-form-select>
                    </b-form-group>
                    <b-form-group label="Name" label-for="name">
                        <b-form-input
                                id="name"
                                name="name"
                                v-model="form.name"
                                :disabled="expiration != ''"
                        >
                        </b-form-input>
                        <input-help :form="form" field="name" text="Enter an optional description or notes"></input-help>
                    </b-form-group>
                    <b-form-group label="Description" label-for="description">
                        <b-textarea
                                id="description"
                                name="description"
                                :rows="2"
                                v-model="form.description"
                        >
                        </b-textarea>
                        <input-help :form="form" field="description" text="Enter an optional description or notes"></input-help>
                    </b-form-group>
                    <b-form-group label="Expiration Date" label-for="expires_at">
                        <date-picker v-model="form.expires_at"></date-picker>
                        <input-help :form="form" field="expires_at" text="Enter the expiration date of this license."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
        </b-container>
        <div slot="modal-footer">
            <b-btn variant="default" @click="showModal=false">Close</b-btn>
            <b-btn variant="info" @click="save()">Save</b-btn>
        </div>
    </b-modal>
</template>

<script>
    export default {
        props: {
            value: {},
            selectedItem: {
                type: Object,
                default() {
                    return {};
                }
            },
            items: {},
            caregiverId: {},
        },
        data() {
            return {
                form : new Form({
                    name: '',
                    description: '',
                    expires_at: '',
                    business_id: (this.officeUserSettings) ? this.officeUserSettings.default_business_id : '',
                }),
                expirations: [],
                selected: '',
                expiration: '',
            }
        },
        computed: {
            showModal: {
                get() {
                    this.form.name = (this.selectedItem) ? this.selectedItem.name : '';
                    this.form.description = (this.selectedItem) ? this.selectedItem.description : '';
                    this.form.expires_at =  (this.selectedItem) ? this.selectedItem.expires_at : '';

                    return this.value;
                },
                set(value) {

                    console.log("Setting modal");
                    this.$emit('input', value);
                }
            },
            title() {
                return (this.form.name) ? 'Edit Expiration' : 'Create Expiration';
            }
        },

        methods: {
            save() {
                let method = 'post';
                let url = '/business/caregivers/' + this.caregiverId + '/licenses';
                if (this.selectedItem.id) {
                    method = 'patch';
                    url = url + '/' + this.selectedItem.id;
                }

                this.form.submit(method, url)
                    .then(response => {
                        // Push the newly created item without mutating the prop, requires the sync modifier
                        let newItems = this.items;

                        if (this.selectedItem.id) {
                            let index = newItems.findIndex(item => item.id === this.selectedItem.id);
                            newItems[index] = response.data.data;
                        }
                        else {
                            newItems.push(response.data.data);
                        }
                        this.$emit('update:items', newItems);
                        this.$parent.$forceUpdate();
                        this.fetchChainExpirations();

                        this.form.reset();
                        this.expiration = '';
                        this.showModal = false;
                    })
                    .catch(() => {});
            },

            async fetchChainExpirations() {
                await axios.get(`/business/expiration-types`)
                    .then(({data}) => {
                        this.expirations = data;
                    })
                    .catch(e => {
                    });
            },
        },

        async mounted() {
            await this.fetchChainExpirations();
        },

        watch: {
            expiration(newVal, oldVal) {
                this.form.name = newVal;
            }
        },
    }
</script>
