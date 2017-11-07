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
                                                name="radiosSm"
                                                @change="update">
                            </b-form-radio-group>
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
                options: [
                    { text: 'Enabled', value: 1 },
                    { text: 'Disabled', value: 0 }
                ]
            }
        },

        methods: {
            update(value) {
                let form = new Form({
                    scheduling: value
                });
                form.put('/business/settings/' + this.business.id)
                    .then(response => {
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000)
                    });
            }
        }
    }
</script>