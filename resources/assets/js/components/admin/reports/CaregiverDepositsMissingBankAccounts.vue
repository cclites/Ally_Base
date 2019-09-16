<template>
  <b-card header="Profile"
            header-bg-variant="info"
            header-text-variant="white"
        >
      <b-row>
          <b-col>
              <b-alert v-model="showAlert" variant="primary">
                  You must choose a business location to continue.
              </b-alert>
          </b-col>
      </b-row>
        <b-row>
            <b-col md="2">
                  <business-location-form-group
                          v-model="business"
                          label="Office Location"
                          class="mr-2"
                          :allow-all="false"
                  />
            </b-col>
            <b-col>
                <b-form-group label="&nbsp;" class="float-right">
                    <b-button-group>
                        <b-button @click="fetch()" variant="info" :disabled="generateDisabled" title="You must select a Registry to continue"><i class="fa fa-file-pdf-o mr-1"></i>Generate Report</b-button>
                    </b-button-group>
                </b-form-group>
            </b-col>
        </b-row>

        <loading-card v-if="loading" text="Loading profile..."></loading-card>

        <b-row>
            <b-table :items="items"
                     :fields="fields"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     :tbody-tr-class="rowClass"
            >
            </b-table>
        </b-row>
    </b-card>
</template>

<script>
    import FormatsListData from "../../../mixins/FormatsListData";
    import BusinessLocationSelect from "../../business/BusinessLocationSelect";
    import BusinessLocationFormGroup from "../../business/BusinessLocationFormGroup";

    export default {
        mixins: [FormatsListData],
        components: {BusinessLocationFormGroup, BusinessLocationSelect},

        data() {
            return {
                sortBy: 'has_amount_owed',
                sortDesc: true,
                business: '',
                showAlert: false,
                loading: false,
                caregivers: [],
                items: [],
                rowClass: '',
                fields: [
                    {
                        key: 'nameLastFirst',
                        label: 'Name',
                        sortable: true,
                        formatter: (val, index, item) => {
                            return val + (item.has_amount_owed ? '*' : '');
                        }
                    },
                    {
                        key: 'email',
                        sortable: true,
                    },
                    {
                        key: 'chain_name',
                        label: "Business Chain",
                        sortable: true,
                    },
                    {
                        key: 'has_amount_owed',
                        sortable: true,
                        formatter: val => this.formatYesNo(val),
                    }
                ]
            }
        },

        computed: {
            generateDisabled(){
                if(this.business){
                    return false;
                }else{
                    this.showAlert = true;
                    return true;
                }
            },
            url(){
                return "/admin/reports/caregivers/deposits-missing-bank-account?business=" + this.business;
            }
        },

        methods: {
            fetch(){
                axios.get(this.url)
                    .then( ({ data }) => {
                        this.caregivers = data;
                    })
                    .catch(e => {})
                    .finally(() => {
                    })
            }
        },
        watch: {
            caregivers(){
                this.items = this.caregivers.map(item => {
                    let chain = item.business_chains.length ? item.business_chains[0] : null;
                    item.chain_name = chain ? chain.name : "";
                    if (item.has_amount_owed) item._rowVariant = "warning";
                    return item;
                })
            },
        }
    }
</script>