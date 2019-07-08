<template>
    <b-modal :title="title" v-model="showModal">
        <b-container fluid>
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Expiration Type" label-for="type">
                        <chain-expirations-autocomplete
                            id="type"
                            name="type"
                            :caregiverId="this.caregiverId"
                            :selectedItem="this.selectedItem"
                            @updateSelectedType="updateSelectedType"
                            >
                        </chain-expirations-autocomplete>
                    </b-form-group>
                    <b-form-group label="Select Default Expiration Type" label-for="expirations">
                        <b-form-select
                                name="expirations"
                                id="expirations"
                                v-model="expiration"
                        >
                            <option v-for="item in expirations" v-on:click.stop="updateSelectedType(item.type)" value="item.type">{{ item.type }}</option>
                        </b-form-select>
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
                form: new Form(),
                expirations: [],
                selected: '',
                expiration: '',
            }
        },
        computed: {
            showModal: {
                get() {
                    return this.value;
                },
                set(value) {
                    this.$emit('input', value);
                }
            },
            title() {
                return (this.form.name) ? 'Edit Expiration' : 'Create Expiration';
            }
        },

        methods: {
            makeForm() {
                this.form = new Form({
                    name: (this.selectedItem) ? this.selectedItem.name : '',
                    description: (this.selectedItem) ? this.selectedItem.description : '',
                    expires_at: (this.selectedItem) ? this.selectedItem.expires_at : '',
                    business_id: this.officeUserSettings.default_business_id,
                });
            },

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

                        this.showModal = false;
                    });
            },

            updateBusinessId(){
                let businessId = this.officeUserSettings.default_business_id;
            },

            updateSelectedType(type){
                if(type){
                    this.form.name=type;
                }

            },

            async fetchChainExpirations() {
                await axios.get(`/business/expiration-types`)
                    .then(({data}) => {
                        this.expirations = data;
                    })
                    .catch(e => {
                    });
            },

            async mounted() {
                await this.fetchChainExpirations();
            },
        },

        watch: {
            selectedItem() {
                this.makeForm();
            },
        },
    }
</script>
