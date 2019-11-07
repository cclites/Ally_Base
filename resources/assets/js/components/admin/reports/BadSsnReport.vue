<template>
    <b-card header="Select Filters."
            header-text-variant="white"
            header-bg-variant="info"
            class="mb-3"
    >
        <div class="form-inline">
            <business-location-form-group
                    v-model="form.businesses"
                    :allow-all="true"
                    class="mr-2"
                    label="Location"
            />

            <b-form-group label="Client Type" class="form-group-label custom-multi-select mr-2" v-if="type === 'clients'">
                <b-form-select v-model="form.client_types" :options="clientTypes" multiple :select-size="selectSize">
                    <template slot="first">
                        <option value="">{{ emptyText }}</option>
                    </template>
                </b-form-select>
            </b-form-group>

            <b-form-group label="&nbsp;">
                <b-button-group>
                    <b-button @click="fetch()" variant="info" :disabled="busy"><i class="fa fa-file-pdf-o mr-1"></i>Generate Report</b-button>
                    <b-button @click="printTable()"><i class="fa fa-print mr-1"></i>Print</b-button>
                </b-button-group>
            </b-form-group>
        </div>

        <b-row>
            <b-col lg="12">

                <div class="table-responsive" >
                    <b-table
                            class="bad-ssn-report"
                            :items="items"
                            :fields="fields"
                            :sort-by="sortBy"
                            :current-page="currentPage"
                            :per-page="perPage"
                            :show-empty="true"
                    >
                        <template slot="name" scope="row">
                            <a :href="`/business/${row.item.type}s/${row.item.id}`" target="_blank">{{ row.item.name }}</a>
                        </template>
                    </b-table>
                </div>

                <b-row v-if="this.items.length > 0">
                    <b-col lg="6" >
                        <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" />
                    </b-col>
                    <b-col lg="6" class="text-right">
                        Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
                    </b-col>
                </b-row>

            </b-col>
        </b-row>
    </b-card>
</template>

<script>

    import BusinessLocationSelect from '../../business/BusinessLocationSelect';
    import BusinessLocationFormGroup from '../../business/BusinessLocationFormGroup';
    import Constants from "../../../mixins/Constants";

    export default {
        name: "BadSsnReport",
        components: { BusinessLocationFormGroup, BusinessLocationSelect },
        mixins: [Constants],
        props: {
            type: ''
        },
        data(){
            return {
                items: [],
                form: new Form({
                    businesses: '',
                    client_types: []
                }),
                totalRows: 0,
                perPage: 50,
                currentPage: 1,
                sortBy: 'name',
                busy: false,
                emptyText: 'All Client Types',
                selectSize: 2,
                open: false,
                fields: [
                    {
                        key: 'business',
                        label: 'Office Location',
                        sortable: true
                    },
                    {
                        key: 'name',
                        label: 'Name',
                        sortable: true,
                    },
                    {
                        key: 'email',
                        label: 'Email',
                        sortable: true
                    },
                ],
            };
        },

        mounted() {
        },
        computed: {
            url(){
                return `/admin/reports/bad-ssn-report/${ this.type }?json=1`;
            }
        },

        methods: {
            fetch(){
                this.busy = true;
                this.form.get(this.url)
                    .then( ({ data }) => {
                        this.items = data;
                        this.totalRows = this.items.length;
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.busy = false;
                    })
            },
            printTable(){
                this.busy = true;
                this.form.get(this.url + '&csv=1')
                    .then( ({ data }) => {
                        var fileURL = window.URL.createObjectURL(new Blob([data]));
                        var fileLink = document.createElement('a');
                        fileLink.href = fileURL;
                        fileLink.setAttribute('download', 'BadSsnReport.csv');
                        document.body.appendChild(fileLink);
                        fileLink.click();
                        fileLink.remove();
                        //console.log(data);
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.busy = false;
                    })
            },
        },
    }
</script>

<style scoped>
    .custom-multi-select{
        position: relative;
        top: 14px;
    }
</style>