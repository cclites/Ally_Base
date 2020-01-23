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
                <b-form-select id="year" v-model="form.year" class="mb-2">
                    <option value="2019">2019</option>
                </b-form-select>
            </b-form-group>

            <b-form-group label="&nbsp;" class="mr-2">
                <b-btn variant="info" @click="generate()" :disabled="disableGenerate">Generate</b-btn>
                <b-button variant="info" @click="copy()"><i class="fa fa-copy mr-1"></i>Copy Emails to Clipboard</b-button>
                <input id="emailString" v-model="emails">
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
                        <template slot="caregiver_email" scope="row">
                            {{ row.item.caregiver_email }}<br />
                            {{ row.item.caregiver_phone }}
                        </template>
                        <template slot="client_email" scope="row">
                            {{ row.item.client_email }}<br />
                            {{ row.item.client_phone }}
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
                        this.storeEmailAddresses();
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.busy = false;
                    })
            },

            copy(){
                var copyText = document.querySelector("#emailString");
                copyText.select();
                document.execCommand("copy");
                alerts.addMessage('success', "Emails copied to clipboard");
            },

            storeEmailAddresses(){

                let emailString = "";

                this.items.forEach(function(item){
                    if(item.errors && item.errors.includes('Client') && !emailString.includes(item.client_email)){
                        emailString += (',' + item.client_email);
                    }

                    if(item.errors && item.errors.includes('Caregiver')  && !emailString.includes(item.caregiver_email)){
                        emailString += (',' + item.caregiver_email);
                    }
                });

                this.emails = emailString.substr(1);
            },
        },
        data(){
          return {
              items: [],
              totalRows: '',
              perPage: 100,
              currentPage: 1,
              sortBy: 'caregiver',
              sortDesc: false,
              busy: false,
              emptyText: "No records to display",
              selected: '',
              emails: '',
              form: new Form({
                  'year': '2019',
                  'business_id': '',
                  'json': 1,
              }),
              fields: [
                  {key: 'location', label: 'Location', sortable: true,},
                  {key: 'client', label: 'Client', sortable: true,},
                  {key: 'client_email', label: 'Client Contact', sortable: false },
                  {key: 'caregiver', label: 'Caregiver', sortable: true,},
                  {key: 'caregiver_email', label: 'Caregiver Contact', sortable: false },
                  {key: 'errors', label: 'Errors', sortable: true,},
              ],
          }
        },
        computed: {
            disableGenerate(){
                // if(this.form.business_id !== ""){
                //     return false;
                // }
                return false;
            }
        },
    }
</script>

<style scoped>
    #year{
        position: relative;
        bottom: 5px;
    }

    #emailString{
        position: absolute;
        z-index: -100;
        left: 99999px;
    }
</style>