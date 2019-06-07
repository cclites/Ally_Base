\<template>
    <b-card
        header="Expirations"
        header-text-variant="white"
        header-bg-variant="info"
        >
        <b-btn @click="createLicense()" variant="info">Add Expiration</b-btn>
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
                    <b-btn size="sm" @click="editLicense(row.item)">Edit</b-btn>
                    <b-btn size="sm" @click="deleteLicense(row.item)" variant="danger">X</b-btn>
                </template>
            </b-table>
        </div>

        <caregiver-license-modal
            v-model="licenseModal"
            :caregiver-id="caregiverId"
            :selectedItem="selectedLicense"
            :items.sync="items"
        ></caregiver-license-modal>
    </b-card>
</template>

<script>
    export default {
        props: {
            'caregiverId': {},
            'licenses': {},
        },

        data() {
            return {
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                filter: null,
                fields: [
                    {
                        key: 'name',
                        label: 'Name',
                        sortable: true,
                    },
                    {
                        key: 'description',
                        label: "Notes"
                    },
                    {
                        key: 'expires_at',
                        label: 'Expiration Date',
                        sortable: true,
                    },
                    {
                        key: 'updated_at',
                        label: 'Last Updated',
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ],
                licenseItems: this.licenses, // store to avoid mutating prop
                licenseModal: false,
                selectedLicense: null,
            }
        },

        mounted() {
            this.totalRows = this.items.length;
        },

        computed: {
            items: {
                get() {
                    return this.licenseItems.map(function(license) {
                        license.expires_at = moment(license.expires_at).format('MM/DD/YYYY');
                        license.updated_at = moment.utc(license.updated_at).local().format('MM/DD/YYYY h:mm A');
                        return license;
                    });
                },
                set(value) {
                    this.licenseItems = value;
                }
            }
        },

        methods: {
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            },
            editLicense(license) {
                this.selectedLicense = license;
                this.licenseModal = true;
            },
            createLicense() {
                this.selectedLicense = {};
                this.licenseModal = true;
            },
            deleteLicense(license) {
                let component = this;
                let form = new Form();
                if (confirm('Are you sure you wish to delete this certification?')) {
                    form.submit('delete', '/business/caregivers/' + this.caregiverId + '/licenses/' + license.id)
                        .then(function(response) {
                            let index = component.licenseItems.findIndex(item => item.id === license.id);
                            Vue.delete(component.licenseItems, index);
                        });
                }
            }
        }
    }
</script>
