<template>
    <div>
        <b-row>
            <b-col lg="12">
                <b-card
                    header="Select Date Range"
                    header-text-variant="white"
                    header-bg-variant="info"
                >
                    <b-row>
                        <b-col lg="3">
                            <b-form-group label="Start Date">
                                <date-picker
                                    class="mb-1"
                                    name="start_date"
                                    v-model="form.start_date"
                                    placeholder="Start Date"
                                ></date-picker>
                            </b-form-group>
                        </b-col>

                        <b-col lg="3">
                            <b-form-group label="End Date">
                                <date-picker
                                    class="mb-1"
                                    v-model="form.end_date"
                                    name="end_date"
                                    placeholder="End Date"
                                ></date-picker>
                            </b-form-group>
                        </b-col>

                        <b-col lg="2">
                            <b-form-group label="&nbsp;">
                                <b-button variant="info" @click="fetchData()">Generate</b-button>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <loading-card v-show="loading"></loading-card>
                    <div v-if="dataIsReady && ! loading">
                        <b-row class="space-above">
                            <b-col lg="6">
                            </b-col>
                            <b-col lg="6">
                            </b-col>
                        </b-row>
                    </div>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    components: {
    },
    data() {
        return {
            loading: false,
            dataIsReady: false,
            form: new Form({
                start_date: '04/01/2017',
                end_date: '09/01/2017',
            }),
        };
    },
    computed: {
    },
    methods: {
        fetchData() {
            this.loading = true;

            this.form.post(`/business/reports/sales-pipeline`)
                .then(({data}) => {
                    this.loading = false;
                    this.dataIsReady = true;
                    console.log(data)
                })
                .catch((err) => {
                    console.error(err);
                    this.loading = false;
                })
        },
    }
}
</script>
