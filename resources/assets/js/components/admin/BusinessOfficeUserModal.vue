<template>
    <b-modal :title="title" v-model="showModal">
        <b-container fluid>
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Username" label-for="username">
                        <b-form-input type="text"
                                      id="username"
                                      v-model="form.username"
                        >
                        </b-form-input>
                        <input-help :form="form" field="username" text=""></input-help>
                    </b-form-group>
                    <b-form-group label="Email Address" label-for="email">
                        <b-form-input type="email"
                                      id="email"
                                      v-model="form.email"
                        >
                        </b-form-input>
                        <input-help :form="form" field="email" text=""></input-help>
                    </b-form-group>
                    <b-form-group label="First Name" label-for="firstname">
                        <b-form-input type="text"
                                      id="firstname"
                                      v-model="form.firstname"
                        >
                        </b-form-input>
                        <input-help :form="form" field="firstname" text=""></input-help>
                    </b-form-group>
                    <b-form-group label="Last Name" label-for="lastname">
                        <b-form-input type="text"
                                      id="lastname"
                                      v-model="form.lastname"
                        >
                        </b-form-input>
                        <input-help :form="form" field="lastname" text=""></input-help>
                    </b-form-group>
                    <b-form-group label="New Password" label-for="password">
                        <b-form-input type="password"
                                      id="password"
                                      v-model="form.password"
                        >
                        </b-form-input>
                        <input-help :form="form" field="password" text="Leave blank to keep password unchanged."></input-help>
                    </b-form-group>
                    <b-form-group label="Confirm Password" label-for="password_confirmation">
                        <b-form-input type="password"
                                      id="password_confirmation"
                                      v-model="form.password_confirmation"
                        >
                        </b-form-input>
                        <input-help :form="form" field="password" text="Confirm the new password (if entered above)."></input-help>
                    </b-form-group>
                    <b-form-group label="Business Locations">
                        <b-form-checkbox-group stacked v-model="form.businesses" name="businesses">
                            <b-form-checkbox v-for="business in businesses" :key="business.id" :value="business.id">{{ business.name }}</b-form-checkbox>
                        </b-form-checkbox-group>
                    </b-form-group>

                    <b-form-checkbox-group stacked v-model="form.views_reports" name="view_reports">
                        <b-form-checkbox v-model="form.views_reports">Can View Reports</b-form-checkbox>
                    </b-form-checkbox-group>
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
            chain: Object,
            businesses: Array,
            value: {},
            selectedItem: Object,
            items: Array,
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
                    'username': (this.selectedItem) ? this.selectedItem.username : '',
                    'email': (this.selectedItem) ? this.selectedItem.email : '',
                    'firstname': (this.selectedItem) ? this.selectedItem.firstname : '',
                    'lastname': (this.selectedItem) ? this.selectedItem.lastname : '',
                    'password': '',
                    'password_confirmation': '',
                    'businesses': (this.selectedItem) ? this.selectedItem.businesses : [],
                    'views_reports': (this.selectedItem) ? this.selectedItem.views_reports === 1 : false,
                });
            },
            title() {
                return (this.selectedItem) ? 'Edit Office User' : 'Create Office User';
            }
        },

        methods: {
            save() {
                let method = 'post';
                let url = '/admin/chains/' + this.chain.id + '/users';
                if (this.selectedItem) {
                    method = 'patch';
                    url = url + '/' + this.selectedItem.id;
                }
                this.form.submit(method, url)
                    .then(response => {
                        // Push the newly created item without mutating the prop, requires the sync modifier
                        let newItems = this.items;
                        if (this.selectedItem) {
                            let index = newItems.findIndex(item => item.id === this.selectedItem.id);
                            newItems[index] = response.data.data;
                        }
                        else {
                            newItems.push(response.data.data);
                        }
                        this.$emit('update:items', newItems);

                        this.showModal = false;
                    });
            }
        },
    }
</script>
