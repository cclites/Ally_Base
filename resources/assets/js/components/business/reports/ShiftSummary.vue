<template>
    <b-row>
        <b-col lg="12">
            <b-card
                header="Select Date Range &amp; Filters"
                header-text-variant="white"
                header-bg-variant="info"
            >
                <b-form inline @submit.prevent="fetch()" class="mb-4">
                    <b-form-group label="Start Date" class="mb-2 mr-2">
                        <date-picker
                            name="start_date"
                            v-model="start_date"
                            placeholder="Start Date"
                            :disabled="loading"
                        />
                    </b-form-group>

                    <b-form-group label="End Date" class="mb-2 mr-2">
                        <date-picker
                            v-model="end_date"
                            name="end_date"
                            placeholder="End Date"
                            :disabled="loading"
                        />
                    </b-form-group>
                    
                    <b-form-group :label="typeTitle" class="mb-2 mr-2">
                        <b-form-select v-model="user_id" name="user_id" :disabled="loading">
                            <option value="">-- Select a {{ typeTitle }} --</option>
                            <option v-for="item in users" :key="item.id" :value="item.id">
                                {{ item.name }}
                            </option>
                        </b-form-select>
                    </b-form-group>

                    <b-form-group label="&nbsp;" class="mb-2 mr-2">
                        <b-button variant="info" type="submit" :disabled="loading">Generate</b-button>
                    </b-form-group>
                </b-form>

                <loading-card v-if="loading" />

                <div v-else class="table-responsive">
                    <b-table bordered striped hover show-empty
                        :items="items"
                        :fields="fields"
                        :sort-by.sync="sortBy"
                        :sort-desc.sync="sortDesc"
                        :per-page="perPage"
                        :current-page="currentPage"
                    >
                        <template slot="name" scope="row">
                            <a :href="`/business/${type}s/${row.item.user_id}`">{{ row.item.name }}</a>
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
        </b-col>
    </b-row>
</template>

<script>
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsDates from "../../../mixins/FormatsDates";

    export default {
        mixins: [FormatsDates, FormatsNumbers],

        props: {
            type: {
                type: String,
                default: 'client',
            },
            users: {
                type: Array,
                default: [],
            },
        },

        data() {
            return {
                start_date: moment().subtract(6, 'days').format('MM/DD/YYYY'),
                end_date: moment().format('MM/DD/YYYY'),
                user_id: '',
                items: [],
                loading: false,
                fields: [
                    { key: 'name', label: this.typeTitle, sortable: true },
                    { key: 'total', label: 'Total Shifts', sortable: true, formatter: x => x.toLocaleString() }
                ],
                totalRows: 0,
                perPage: 50,
                currentPage: 1,
                sortBy: 'name',
                sortDesc: false,
            }
        },

        computed: {
            typeTitle() {
                return this.type == 'client' ? 'Client' : 'Caregiver';
            },
        },

        methods: {
            fetch() {
                let url = `/business/reports/${this.type}-shifts?fetch=1&user_id=${this.user_id}&start_date=${this.start_date}&end_date=${this.end_date}`;
                this.loading = true;

                axios.get(url)
                    .then( ({ data }) => {
                        this.items = data;
                        this.loading = false;
                    })
                    .catch(e => {
                        this.loading = false;
                    })
            },
        },

        watch: {
            items() {
                this.totalRows = this.items.length;
            },
        },
    }
</script>
