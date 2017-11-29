<template>
    <b-card title="Business Settings">
        <b-row>
            <b-col lg="6">
                <b-list-group>
                    <b-list-group-item>
                        <b-form-group label="Schedules" horizontal="true" class="mb-0">
                            <b-form-radio-group v-model="scheduling"
                                                :options="options"
                                                size="sm"
                                                name="radiosSm">
                            </b-form-radio-group>
                        </b-form-group>

                    </b-list-group-item>
                    <b-list-group-item>
                        <b-form-group horizontal label="Mileage Rate">
                            <b-form-input type="text" v-model="mileageRate"></b-form-input>
                        </b-form-group>
                    </b-list-group-item>
                    <b-list-group-item>
                        <b-form-group>
                            <b-button @click="update" variant="info">Save</b-button>
                        </b-form-group>
                    </b-list-group-item>
                </b-list-group>
            </b-col>
        </b-row>
    </b-card>
</template>

<style lang="scss">
</style>

<script>
    export default {
        props: ['business'],

        data() {
            return{
                scheduling: this.business.scheduling,
                mileageRate: this.business.mileage_rate,
                options: [
                    { text: 'Enabled', value: 1 },
                    { text: 'Disabled', value: 0 }
                ]
            }
        },

        methods: {
            update() {
                let form = new Form({
                    scheduling: this.scheduling,
                    mileage_rate: this.mileageRate
                });
                form.put('/business/settings/' + this.business.id);
            }
        }
    }
</script>