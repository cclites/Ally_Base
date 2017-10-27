<template>
    <b-modal :title="title" v-model="showModal">
        <b-container fluid>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Caregiver Injury?" label-for="caregiver_injury">
                        <b-form-select 
                            id="caregiver_injury" 
                            name="caregiver_injury"
                            v-model="form.caregiver_injury"
                            >
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </b-form-select>
                        <input-help :form="form" field="caregiver_injury" text=""></input-help>
                    </b-form-group>
                    <b-form-group label="Client Injury?" label-for="client_injury">
                        <b-form-select
                                id="client_injury"
                                name="client_injury"
                                v-model="form.client_injury"
                        >
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </b-form-select>
                        <input-help :form="form" field="client_injury" text=""></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="12">
                    <b-form-group label="Issue Comments" label-for="comments">
                        <b-textarea
                            id="comments" 
                            name="comments"
                            :rows="3"
                            v-model="form.comments"
                            >
                        </b-textarea>
                        <input-help :form="form" field="comments" text="Enter any free text regarding this issue."></input-help>
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
            selectedItem: {},
            items: {},
            shiftId: {},
        },

        data() {
            return {}
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
            form() {
                return new Form({
                    caregiver_injury: (this.selectedItem) ? this.selectedItem.caregiver_injury : 0,
                    client_injury: (this.selectedItem) ? this.selectedItem.client_injury : 0,
                    comments: (this.selectedItem) ? this.selectedItem.comments : '',
                });
            },
            title() {
                return (this.selectedItem) ? 'Edit Issue' : 'Create Issue';
            }
        },

        methods: {
            save() {
                let component = this;
                let method = 'post';
                let url = '/business/shifts/' + this.shiftId + '/issues';
                if (component.selectedItem) {
                    method = 'patch';
                    url = url + '/' + component.selectedItem.id;
                }
                component.form.submit(method, url)
                    .then(function(response) {
                        // Push the newly created item without mutating the prop, requires the sync modifier
                        let newItems = component.items;
                        if (component.selectedItem) {
                            let index = newItems.findIndex(item => item.id === component.selectedItem.id);
                            newItems[index] = response.data.data;
                        }
                        else {
                            newItems.push(response.data.data);
                        }
                        component.$emit('update:items', newItems);

                        component.showModal = false;
                    });
            }
        },
    }
</script>
