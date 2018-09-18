<template>
    <div class="nacha-ach">
        <b-card header="File Header" header-text-variant="white" header-bg-variant="info">
            <b-row>
                <b-col md="6">
                    <b-form-group label="Immediate Destination *" label-size="md">
                        <b-form-input name="fh_immediate_destination" type="text" v-model="form.fh_immediate_destination" required v-validate="'required|max:9|alpha_num'"></b-form-input>
                        <input-help :form="form" text="Destination bank routing number" v-if="!errors.has('fh_immediate_destination')"></input-help>
                        <p class="text-danger fs-13" v-if="errors.has('fh_immediate_destination')">
                            {{ errors.first('fh_immediate_destination').replace('fh_immediate_destination', 'Immediate Destination') }}
                        </p>
                    </b-form-group>

                    <b-form-group label="Immediate Origin *" label-size="md">
                        <b-form-input name="fh_immediate_origin" type="text" v-model="form.fh_immediate_origin" required v-validate="'required|max:9|alpha_num'"></b-form-input>
                        <input-help :form="form" text="Federal ID # (proceeded by a space)" v-if="!errors.has('fh_immediate_origin')"></input-help>
                        <p class="text-danger fs-13" v-if="errors.has('fh_immediate_origin')">
                            {{ errors.first('fh_immediate_origin').replace('fh_immediate_origin', 'Immediate Origin') }}
                        </p>
                    </b-form-group>
                </b-col>

                <b-col md="6">
                    <b-form-group label="Immediate Destination Name *" label-size="md">
                        <b-form-input name="fh_immediate_destination_name" type="text" v-model="form.fh_immediate_destination_name" required v-validate="'required'"></b-form-input>
                        <input-help :form="form" text="Destination bank name" v-if="!errors.has('fh_immediate_destination_name')"></input-help>
                        <p class="text-danger fs-13" v-if="errors.has('fh_immediate_destination_name')">
                            {{ errors.first('fh_immediate_destination_name').replace('fh_immediate_destination_name', 'Immediate Destination Name') }}
                        </p>
                    </b-form-group>

                    <b-form-group label="Immediate Origin Name *" label-size="md">
                        <b-form-input name="fh_immediate_origin_name" type="text" v-model="form.fh_immediate_origin_name" required v-validate="'required'"></b-form-input>
                        <input-help :form="form" text="Company name" v-if="!errors.has('fh_immediate_origin_name')"></input-help>
                        <p class="text-danger fs-13" v-if="errors.has('fh_immediate_origin_name')">
                            {{ errors.first('fh_immediate_origin_name').replace('fh_immediate_origin_name', 'Immediate Origin Name *') }}
                        </p>
                    </b-form-group>
                </b-col>
            </b-row>
        </b-card>

        <admin-nachaach-batch @sendBatchData="getBatchData"></admin-nachaach-batch>

        <b-form-group v-if="!loading">
            <b-btn @click="validateBeforeSubmit()" :disabled="errors.any()"  variant="info">Save</b-btn>
        </b-form-group>
        <div class="c-loader" v-if="loading"></div>
    </div>
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
                    batches: []
                }),
                loading: false,
            }
        },

        methods: {
            validateBeforeSubmit() {
                this.$validator.validateAll().then((result) => {
                    if (result) {
                        this.save();
                    }
                });
            },

            save() {
                this.loading = true;
                axios.post(`/admin/nacha-ach/generate`, this.form).then(response => {
                    if(response.data) {
                        let output = response.data;
                        let element = document.createElement('a');
                        let date = new Date();

                        element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(output.data));
                        element.setAttribute('download', 'NACHA_ACH_' + date.getTime() + '.txt');

                        element.style.display = 'none';
                        document.body.appendChild(element);

                        element.click();

                        document.body.removeChild(element);
                        this.loading = false;
                    } else {
                        alerts.addMessage('error', response.message);
                        this.loading = false;
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

                    this.loading = false;
                })
            },

            getBatchData(data) {
                this.form.batches = data;
            },
        }
    }
</script>

<style lang="scss">

    .nacha-ach {
        .remove {
            font-size: 30px;
            border-radius: 50%;
            height: 45px;
            width: 45px;
            line-height: 45px;
            display: inline-block;
            text-align: center;
            cursor: pointer;
            margin-top: 25px;
            transition: background-color .4s;

            &:hover {
                background-color: #ffb8b8;
            }
        }

        p.text-danger {
            font-size: 13px;
            margin-bottom: 0;
            margin-top: 0.25rem;
        }
    }

</style>