<template>
    <div>
        <loading-card v-show="loading"></loading-card>

        <div v-show="! loading">
            <b-btn variant="info" class="mb-3" href="/business/custom-fields/create">Create custom field</b-btn>

            <h3>Caregiver fields</h3>
            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                        :items="fields.caregiver"
                        :fields="tableColumns"
                        :current-page="currentPage"
                        :per-page="perPage"
                        :sort-by.sync="sortBy"
                        @filtered="onFiltered"
                >
                    <template slot="type" scope="data">
                        {{ fromTypeToText(data.item.type) }}
                    </template>
                    <template slot="required" scope="data">
                        {{ fromBoolToText(data.item.required) }}
                    </template>
                    <template slot="default_value" scope="data">
                        {{ data.item.required ? convertDefault(data.item) : 'None' }}
                    </template>
                    <template slot="action" scope="data">
                        <b-btn variant="secondary" :href="`/business/custom-fields/${data.item.id}`">
                            <i class="fa fa-edit"></i>
                        </b-btn>
                    </template>
                </b-table>
            </div>

            <b-row>
                <b-col lg="6">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage"/>
                </b-col>
                <b-col lg="6" class="text-right">
                    Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
                </b-col>
            </b-row>
        </div>
    </div>
</template>

<script>
    import FormatsDates from '../../../mixins/FormatsDates';

    export default {      
        mixins: [ FormatsDates ],

        mounted() {
            this.fetch();
        },

        data() {
            return {
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                loading: false,
                sortBy: 'created_at',
                tableColumns: [
                    {
                        key: 'label',
                        label: 'Label Text',
                        sortable: true,
                    },
                    {
                        key: 'type',
                        label: 'Field Type',
                        sortable: true,
                    },
                    {
                        key: 'required',
                        label: 'Is Required',
                        sortable: true,
                    },
                    {
                        key: 'default_value',
                        label: 'Default answer',
                        sortable: false
                    },
                    {
                        key: 'created_at',
                        label: 'Date Created',
                        sortable: false,
                    },
                    'action',
                ],
                fields: {
                    caregiver: [],
                    client: [],
                },
            };
        },

        computed: {
            items() {
                return [];
            },
        },

        methods: {
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            },

            fromBoolToText(boolean) {
                return !!boolean ? 'Yes' : 'No';
            },

            fromTypeToText(type) {
                if(type == 'input') {
                    return 'Small text';
                }else if (type == 'textarea') {
                    return 'Big text';
                }else if (type == 'radio') {
                    return 'Yes/No Question';
                }else if(type == 'dropdown') {
                    return 'List';
                }
            },

            convertDefault({default_value, type, options}) {
                if(type == 'radio') {
                    return this.fromBoolToText(default_value);
                }else if(type == 'dropdown') {
                    return options.find(option => option.value == default_value).label;
                }else {
                    return default_value.length > 25 ? default_value.substring(0, 25) : default_value;
                }
            },

            async fetch() {
                this.loading = true;

                try{
                    const {data} = await axios.get('/business/custom-fields?type=all');
                    this.fields = {
                        caregiver: data.filter(({user_type}) => user_type == 'caregiver'),
                        client: data.filter(({user_type}) => user_type == 'client'),
                    };
                } catch(error) {}
                
                this.loading = false;
            },
        },
    }
</script>

<style scoped>
</style>