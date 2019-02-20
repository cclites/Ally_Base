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
                        <b-form-group label="Start Date" label-class="required">
                            <date-picker
                                    class="mb-1"
                                    name="start_date"
                                    placeholder="Start Date">
                            </date-picker>
                        </b-form-group>
                    </b-col>
                    <b-col lg="3">
                        <b-form-group label="End Date" label-class="required">
                            <date-picker
                                    class="mb-1"
                                    name="end_date"
                                    placeholder="End Date">
                            </date-picker>
                        </b-form-group>
                    </b-col>
                    <b-col lg="3">
                        <business-location-form-group name="business_id"
                                                      help-text="">
                        </business-location-form-group>
                    </b-col>
                </div>
                <div class="row">
                    <b-col lg="12">
                        <b-form-group label="Group By">
                            <b-form-radio-group name="group_by" v-model="group_by">
                                <b-form-radio value="none">None</b-form-radio>
                                <b-form-radio value="client">Client</b-form-radio>
                                <b-form-radio value="caregiver">Caregiver</b-form-radio>
                            </b-form-radio-group>
                        </b-form-group>
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
        data() {
            return {
                group_by: 'none',
                form: this.makeForm()
            }
        },
        methods: {
            makeForm(defaults = {}) {
                return new Form({
                    organization: defaults.organization,
                    contact_name: defaults.contact_name,
                    phone: defaults.phone,
                    business_id: defaults.business_id || ""
                });
            },
        },
        props: ['token'],
    }
</script>

<style scoped>

</style>
