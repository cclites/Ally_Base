<template>
    <div>
        <b-row>
            <b-col>
                <b-card
                    header="Filters"
                    header-text-variant="white"
                    header-bg-variant="info"
                >
                    <b-col md="3">
                        <b-row>
                            <b-col>
                                <b-form-group label="Start Date">
                                    <date-picker v-model="form.start_date" name="start_date"></date-picker>
                                </b-form-group>
                            </b-col>
                            <b-col>
                                <b-form-group label="End Date">
                                    <date-picker :value="form.end_date" name="end_date"></date-picker>
                                </b-form-group>
                            </b-col>
                        </b-row>
                    </b-col>
                    <b-col md="2">
                        <business-location-form-group
                                v-model="form.business"
                                label="Office Location"
                                :allow-all="true"
                        />
                    </b-col>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    export default {
        name: "PayerInvoiceReport"
    }
</script>

<style scoped>

</style>