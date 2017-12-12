<template>
    <b-card>
        <b-row class="mb-2">
            <b-col lg="3">
                <a href="/business/caregivers/create" class="btn btn-info">Add Caregiver</a>
            </b-col>
            <b-col lg="3">
                <b-form-select v-model="active">
                    <option value="all">All Caregivers</option>
                    <option value="active">Active Caregivers</option>
                    <option value="inactive">Inactive Caregivers</option>
                </b-form-select>
            </b-col>
            <b-col lg="6" class="text-right">
                <b-form-input v-model="filter" placeholder="Type to Search" />
            </b-col>
        </b-row>

        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                     :items="items"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     :filter="filter"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     @filtered="onFiltered"
            >
                <template slot="actions" scope="row">
                    <!-- We use click.stop here to prevent a 'row-clicked' event from also happening -->
                    <b-btn size="sm" :href="'/business/caregivers/' + row.item.id">
                        <i class="fa fa-edit"></i>
                    </b-btn>
                </template>
            </b-table>
        </div>

        <b-row>
            <b-col lg="6" >
                <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" />
            </b-col>
            <b-col lg="6" class="text-right">
                Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    export default {
        props: {
            'caregivers': Array,
        },

        data() {
            return {
                active: 'active',
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: 'lastname',
                sortDesc: false,
                editModalVisible: false,
                filter: null,
                modalDetails: { index:'', data:'' },
                selectedItem: {},
                fields: [
                    {
                        key: 'firstname',
                        label: 'First Name',
                        sortable: true,
                    },
                    {
                        key: 'lastname',
                        label: 'Last Name',
                        sortable: true,
                    },
                    {
                        key: 'email',
                        label: 'Email Address',
                        sortable: true,
                    },
                    {
                        key: 'primaryphone',
                        label: 'Primary Phone',
                        sortable: true,
                    },
                    {
                        key: 'city',
                        label: 'City',
                        sortable: true,
                    },
                    {
                        key: 'zipcode',
                        label: 'Zip Code',
                        sortable: true,
                    },
                    'actions'
                ]
            }
        },

        mounted() {
            this.totalRows = this.items.length;
        },

        computed: {
            items() {
                let component = this;
                let caregivers = this.caregivers.map(function(caregiver) {
                    return {
                        id: caregiver.id,
                        firstname: caregiver.user.firstname,
                        lastname: caregiver.user.lastname,
                        email: caregiver.user.email,
                        primaryphone: component.getPhone(caregiver).number,
                        zipcode: component.getAddress(caregiver).zip,
                        city: component.getAddress(caregiver).city,
                        active: caregiver.user.active
                    }
                })

                return _.filter(caregivers, function (caregiver) {
                    switch (component.active) {
                        case 'all':
                            return true;
                        case 'active':
                            return caregiver.active;
                        case 'inactive':
                            return !caregiver.active;
                    }
                })
            },
        },

        methods: {
            getAddress(caregiver)
            {
                if (caregiver.addresses && caregiver.addresses.length > 0) {
                    let index = caregiver.addresses.findIndex(function(address) {
                        return address.type === 'home';
                    });
                    if (index !== -1) {
                        return caregiver.addresses[index];
                    }
                }
                return {};
            },
            getPhone(caregiver)
            {
                if (caregiver.phone_numbers && caregiver.phone_numbers.length > 0) {
                    let index = caregiver.phone_numbers.findIndex(function(phone) {
                        return phone.type === 'work';
                    });
                    if (index !== -1) {
                        return caregiver.phone_numbers[index];
                    }
                }
                return {};
            },

            details(item, index, button) {
                this.selectedItem = item;
                this.modalDetails.data = JSON.stringify(item, null, 2);
                this.modalDetails.index = index;
//                this.$root.$emit('bv::show::modal','caregiverEditModal', button);
                this.editModalVisible = true;
            },
            resetModal() {
                this.modalDetails.data = '';
                this.modalDetails.index = '';
            },
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            }
        }
    }
</script>
