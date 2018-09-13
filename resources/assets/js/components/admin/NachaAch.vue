<template>
    <b-card header="Nacha Ach" header-text-variant="white" header-bg-variant="info">
        <b-row>
            <b-col md="6">
                <b-form-group label="Immediate Destination *">
                    <b-form-input v-model="form.fh_immediate_destination" v-validate="'required|max:9'" name="fh_immediate_destination"></b-form-input>
                    <p class="text-danger" v-if="errors.has('fh_immediate_destination')">
                        {{ errors.first('fh_immediate_destination').replace('fh_immediate_destination', 'Immediate Destination') }}
                    </p>
                </b-form-group>
                <b-form-group label="Immediate Destination Name *">
                    <b-form-input v-model="form.fh_immediate_destination_name" v-validate="'required'" name="fh_immediate_destination_name"></b-form-input>
                    <p class="text-danger" v-if="errors.has('fh_immediate_destination_name')">
                        {{ (errors.first('fh_immediate_destination_name')).replace('fh_immediate_destination_name', 'Immediate Destination Name') }}
                    </p>
                </b-form-group>
                <b-form-group label="Immediate Origin *">
                    <b-form-input v-model="form.fh_immediate_origin" v-validate="'required|max:9'" name="fh_immediate_origin"></b-form-input>
                    <p class="text-danger" v-if="errors.has('fh_immediate_origin')">
                        {{ (errors.first('fh_immediate_origin')).replace('fh_immediate_origin', 'Immediate Origin') }}
                    </p>
                </b-form-group>
                <b-form-group label="Immediate Origin Name *">
                    <b-form-input v-model="form.fh_immediate_origin_name" v-validate="'required'" name="fh_immediate_origin_name"></b-form-input>
                    <p class="text-danger" v-if="errors.has('fh_immediate_origin_name')">
                        {{ (errors.first('fh_immediate_origin_name')).replace('fh_immediate_origin_name', 'Immediate Origin Name') }}
                    </p>
                </b-form-group>
            </b-col>
            <b-col md="6">
                <b-form-group label="Service Class Code *">
                    <b-form-select v-model="form.bh_service_class_code" v-validate="'required'" name="bh_service_class_code">
                        <option value="200">200</option>
                        <option value="220">220</option>
                        <option value="225">225</option>
                    </b-form-select>
                    <p class="text-danger" v-if="errors.has('bh_service_class_code')">
                        {{ (errors.first('bh_service_class_code')).replace('bh_service_class_code', 'Service Class Code') }}
                    </p>
                </b-form-group>
                <b-form-group label="Company Name *">
                    <b-form-input v-model="form.bh_company_name" v-validate="'required'" name="bh_company_name"></b-form-input>
                    <p class="text-danger" v-if="errors.has('bh_company_name')">
                        {{ (errors.first('bh_company_name')).replace('bh_company_name', 'Company Name') }}
                    </p>
                </b-form-group>
                <b-form-group label="Company Entry Description *">
                    <b-form-input v-model="form.bh_company_entry_description" v-validate="'required'" name="bh_company_entry_description"></b-form-input>
                    <p class="text-danger" v-if="errors.has('bh_company_entry_description')">
                        {{ (errors.first('bh_company_entry_description')).replace('bh_company_entry_description', 'Company Entry Description') }}
                    </p>
                </b-form-group>
                <b-form-group label="Originating DFI ID *">
                    <b-form-input v-model="form.bh_originating_DFI_ID" v-validate="'required'" name="bh_originating_DFI_ID"></b-form-input>
                    <p class="text-danger" v-if="errors.has('bh_originating_DFI_ID')">
                        {{ (errors.first('bh_originating_DFI_ID')).replace('bh_originating_DFI_ID', 'Originating DFI ID') }}
                    </p>
                </b-form-group>
            </b-col>
        </b-row>

        <hr>

        <b-row>
            <b-col md="3">
                <b-form-group label="DFI Account Number *" label-size="sm">
                    <b-form-input v-model="ppded_details.ppded_DFI_account_number" v-validate="'required'" name="ppded_DFI_account_number"></b-form-input>
                    <p class="fs-13 text-danger" v-if="errors.has('ppded_DFI_account_number')">
                        {{ (errors.first('ppded_DFI_account_number')).replace('ppded_DFI_account_number', 'DFI Account Number') }}
                    </p>
                </b-form-group>
            </b-col>
            <b-col md="2">
                <b-form-group label="Amount *" label-size="sm">
                    <b-form-input v-model="ppded_details.ppded_amount" v-validate="'required'" name="ppded_amount"></b-form-input>
                    <p class="fs-13 text-danger" v-if="errors.has('ppded_amount')">
                        {{ (errors.first('ppded_amount')).replace('ppded_amount', 'Amount') }}
                    </p>
                </b-form-group>
            </b-col>
            <b-col md="3">
                <b-form-group label="Individual Identification Number *" label-size="sm">
                    <b-form-input v-model="ppded_details.ppded_individual_identification_number" v-validate="'required'" name="ppded_individual_identification_number"></b-form-input>
                    <p class="fs-13 text-danger" v-if="errors.has('ppded_individual_identification_number')">
                        {{ (errors.first('ppded_individual_identification_number')).replace('ppded_individual_identification_number', 'Individual Identification Number') }}
                    </p>
                </b-form-group>
            </b-col>
            <b-col md="3">
                <b-form-group label="Individual Name *" label-size="sm">
                    <b-form-input v-model="ppded_details.ppded_individual_name" v-validate="'required'" name="ppded_individual_name"></b-form-input>
                    <p class="fs-13 text-danger" v-if="errors.has('ppded_individual_name')">
                        {{ (errors.first('ppded_individual_name')).replace('ppded_individual_name', 'Individual Name') }}
                    </p>
                </b-form-group>
            </b-col>
            <b-col md="1">
                <span class="mdi mdi-plus add-ppded-detail" @click="validateBeforeSubmit('detail')"></span>
            </b-col>
        </b-row>

        <div v-if="details.length">
            <div v-for="(detail, index) in details">
                <b-row>
                    <b-col md="3">
                        <b-form-group>
                            <b-form-input v-model="detail.ppded_DFI_account_number" v-validate="'required'" :name="'detail_ppded_DFI_account_number' + index"></b-form-input>
                            <p class="fs-13 text-danger" v-if="errors.has('detail_ppded_DFI_account_number' + index)">
                                {{ (errors.first('detail_ppded_DFI_account_number' + index)).replace('detail_ppded_DFI_account_number' + index, 'DFI Account Number') }}
                            </p>
                        </b-form-group>
                    </b-col>
                    <b-col md="2">
                        <b-form-group>
                            <b-form-input v-model="detail.ppded_amount" v-validate="'required'" :name="'detail_ppded_amount' + index"></b-form-input>
                            <p class="fs-13 text-danger" v-if="errors.has('detail_ppded_amount' + index)">
                                {{ (errors.first('detail_ppded_amount' + index)).replace('detail_ppded_amount' + index, 'Amount') }}
                            </p>
                        </b-form-group>
                    </b-col>
                    <b-col md="3">
                        <b-form-group>
                            <b-form-input v-model="detail.ppded_individual_identification_number" v-validate="'required'" :name="'detail_ppded_individual_identification_number' + index"></b-form-input>
                            <p class="fs-13 text-danger" v-if="errors.has('detail_ppded_individual_identification_number' + index)">
                                {{ (errors.first('detail_ppded_individual_identification_number' + index)).replace('detail_ppded_individual_identification_number' + index, 'Individual Identification Number') }}
                            </p>
                        </b-form-group>
                    </b-col>
                    <b-col md="3">
                        <b-form-group>
                            <b-form-input v-model="detail.ppded_individual_name" v-validate="'required'" :name="'detail_ppded_individual_name' + index"></b-form-input>
                            <p class="fs-13 text-danger" v-if="errors.has('detail_ppded_individual_name' + index)">
                                {{ (errors.first('detail_ppded_individual_name' + index)).replace('detail_ppded_individual_name' + index, 'Individual Name') }}
                            </p>
                        </b-form-group>
                    </b-col>
                    <b-col md="1">
                        <span class="mdi mdi-minus remove-ppded-detail" @click="removeDetail(index)"></span>
                    </b-col>
                </b-row>
            </div>
        </div>

        <b-form-group>
            <b-btn @click="validateBeforeSubmit('header')" :disabled="errors.any(form)"  variant="info">Save</b-btn>
        </b-form-group>
    </b-card>
</template>
<script>

    export default {

        data() {
            return {
                form: new Form({
                    fh_immediate_destination: '',
                    fh_immediate_origin: '',
                    fh_immediate_destination_name: '',
                    fh_immediate_origin_name: '',
                    bh_service_class_code: '200',
                    bh_company_name: '',
                    bh_company_entry_description: '',
                    bh_originating_DFI_ID: '',
                }),
                ppded_details: {
                    ppded_DFI_account_number: '',
                    ppded_amount: '',
                    ppded_individual_identification_number: '',
                    ppded_individual_name: '',
                },
                details: [],
            }
        },

        methods: {
            validateBeforeSubmit(type) {
                let form = type == 'header' ? this.form : this.ppded_details;
                this.$validator.validateAll(form).then((result) => {
                    if (result) {
                        if(type == 'header') {
                            this.save();
                        } else {
                            this.addDetail();
                        }
                    }
                });
            },

            save() {

                let form = this.form;
                form.details = this.details;

                if (true) {
                    axios.post(`/admin/nacha-ach/generate`, form).then(response => {
                        if(response.data) {
                            let output = response.data;
                            let element = document.createElement('a');
                            let date = new Date();

                            element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(output.data));
                            element.setAttribute('download', 'NACHA-' + date.getTime() + '.txt');

                            element.style.display = 'none';
                            document.body.appendChild(element);

                            element.click();

                            document.body.removeChild(element);
                        } else {
                            alerts.addMessage('error', response.message);
                        }
                    })
                    .catch(err => {
                        if(err.response.data && err.response.data.errors) {
                            let errors = err.response.data.errors;
                            for(let index in errors) {
                                let field = index.replace(/_/g, ' ');
                                let newField = field.substr(field.indexOf(' ')+1);
                                let text = errors[index][0].replace(field, newField);

                                alerts.addMessage('error', text);
                            }
                        }
                    })
                } else {
                    alerts.addMessage('error', 'Please fill required fields');
                }
            },

            addDetail() {
                let data = {};
                for(let i in this.ppded_details) {
                    data[i] = this.ppded_details[i];
                }

                this.details.push(data);
                this.clearPpdedDetails();
            },

            removeDetail(index) {
                if(this.details[index]) {
                    this.details.splice(index, 1);
                }
            },

            clearPpdedDetails() {
                for(let i in this.ppded_details) {
                    this.ppded_details[i] = '';
                }

                this.$validator.reset();
            },
        }
    }
</script>

<style lang="scss">
    .add-ppded-detail,
    .remove-ppded-detail {
        font-size: 30px;
        border-radius: 50%;
        height: 45px;
        width: 45px;
        line-height: 45px;
        display: inline-block;
        text-align: center;
        margin-top: 20px;
        cursor: pointer;
        transition: background-color .4s;
    }

    .add-ppded-detail:hover {
        background-color: #d2d2d2;
    }

    .remove-ppded-detail {
        margin-top: -3px;
    }

    .remove-ppded-detail:hover {
        background-color: #ffb8b8;
    }

    .fs-13 {
        font-size: 13px;
    }
</style>