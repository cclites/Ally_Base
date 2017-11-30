<template>
    <b-card
        header="Service Orders"
        header-text-variant="white"
        header-bg-variant="info"
        >
        <form @submit.prevent="save()" @keydown="form.clearError($event.target.name)">
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Maximum Weekly Hours" label-for="max_weekly_hours">
                        <b-form-input
                            id="max_weekly_hours"
                            type="number"
                            step="any"
                            v-model="form.max_weekly_hours"
                            >
                        </b-form-input>
                        <input-help :form="form" field="max_weekly_hours" text="The maximum number of hours this client can be scheduled for per week."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-btn variant="success" type="submit">Save Service Orders</b-btn>
        </form>
    </b-card>
</template>

<script>
    export default {
        props: {
            'client': {},
        },

        data() {
            return {
                form: new Form({
                    'max_weekly_hours': this.client.max_weekly_hours,
                })
            }
        },

        mounted() {

        },

        methods: {
            save() {
                this.form.post('/business/clients/' + this.client.id + '/service_orders');
            }
        },
    }
</script>
