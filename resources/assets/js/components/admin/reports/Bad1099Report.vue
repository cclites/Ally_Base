<template>
    <b-card header="Bad 1099s Report"
            header-text-variant="white"
            header-bg-variant="info"
            class="mb-3"
    >
        <b-row>
            <business-location-form-group
                    v-model="form.business_id"
                    :allow-all="true"
                    class="mr-2"
                    label="Location"
            />

            <b-form-group label="Year" label-for="year" class="mr-2">
                <b-form-select id="year" v-model="form.year" :options="years" class="mb-2">
                </b-form-select>
            </b-form-group>

            <b-form-group label="&nbsp;" class="mr-2">
                <b-btn variant="info" @click="generate()" :disabled="disableGenerate">Generate</b-btn>
            </b-form-group>
        </b-row>

        <div class="d-flex justify-content-center" v-if="busy">
            <div class="my-5">
                <i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
            </div>
        </div>
        <div v-else>
            <b-row>
                <b-col>
                    <b-table
                            class="bad-1099-report"
                            :items="items"
                            :fields="fields"
                            :sort-by="sortBy"
                            :empty-text="emptyText"
                            :busy="busy"
                            :current-page="currentPage"
                            :per-page="perPage"
                            ref="table"
                    >
                        <template slot="caregiver" scope="row">
                            <a :href="'/business/caregivers/' + row.item.caregiver_id" target="_blank">{{ row.item.caregiver }}</a>
                        </template>
                        <template slot="client" scope="row">
                            <a :href="'/business/clients/' + row.item.client_id" target="_blank">{{ row.item.client }}</a>
                        </template>
                    </b-table>
                </b-col>
            </b-row>

        </div>

        <b-row v-if="this.items.length > 0">
            <b-col lg="6" >
                <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" />
            </b-col>
            <b-col lg="6" class="text-right">
                Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
            </b-col>
        </b-row>
        <div v-else class="text-center">
            {{ emptyText }}
        </div>
    </b-card>
</template>

<script>

    import BusinessLocationSelect from "../../business/BusinessLocationSelect";
    import BusinessLocationFormGroup from "../../business/BusinessLocationFormGroup";

    export default {
        name: "Bad1099Report",
        components: {BusinessLocationFormGroup, BusinessLocationSelect},
        methods: {
            generate(){

                this.busy = true;
                this.totalRows = 0;
                this.form.get('/admin/reports/bad-1099-report')
                    .then( ({ data }) => {
                        this.items = data;
                        this.totalRows = this.items.length;
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.busy = false;
                    })
            },
        },
        data(){
          return {
              items: [],
              totalRows: '',
              start_date: 2019, //arbitrary start year
              end_date: moment().year(),
              perPage: 100,
              currentPage: 1,
              sortBy: 'caregiver',
              sortDesc: false,
              busy: false,
              emptyText: "No records to display",
              selected: '',
              form: new Form({
                  'year': '',
                  'business_id': '',
                  'json': 1,
                  'all': true
              }),
              fields: [
                  {key: 'location', label: 'Location', sortable: true,},
                  {key: 'caregiver', label: 'Caregiver', sortable: true,},
                  {key: 'client', label: 'Client', sortable: true,},
                  {key: 'errors', label: 'Errors', sortable: true,},
              ],
          }
        },
        computed: {
            years(){
                let x = [];
                let i = this.start_date;
                while( i <= this.end_date){
                    x.push(i++);
                };

                this.form.year = this.start_date;
                return x;
            },

            disableGenerate(){
                if(this.form.business_id !== ""){
                    return false;
                }
                return true;
            }
        },
    }
</script>

<style scoped>
    #year{
        position: relative;
        bottom: 5px;
    }
</style>