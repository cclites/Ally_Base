<template>
    <div class="card">
        <div class="card-header text-white bg-info">
            <h4 class="text-white">Printable Schedules</h4>
        </div>
        <div class="card-body">
            <form action="/business/schedule/print" method="post" target="_blank">
                <input type="hidden" name="_token" :value="token">
                <div class="row">
                    <b-col lg="3">
                        <b-form-group label="Start Date">
                            <date-picker
                                    class="mb-1"
                                    name="start_date"
                                    placeholder="Start Date">
                            </date-picker>
                        </b-form-group>
                    </b-col>
                    <b-col lg="3">
                        <b-form-group label="End Date">
                            <date-picker
                                    class="mb-1"
                                    name="end_date"
                                    placeholder="End Date">
                            </date-picker>
                        </b-form-group>
                    </b-col>
                    <b-col lg="3">
                        <business-location-form-group v-model="form.business_id"
                                                      form="form"
                                                      field="business_id"
                                                      help-text="">
                        </business-location-form-group>
                    </b-col>
                </div>
                <div class="row">
                    <div class="col">
                        <button class="btn btn-info" type="submit">Generate</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import BusinessLocationFormGroup from "../BusinessLocationFormGroup";

    export default {
        name: "PrintableSchedules",
        components: {BusinessLocationFormGroup},
        props: ['token'],
    }
</script>

<style scoped>

</style>