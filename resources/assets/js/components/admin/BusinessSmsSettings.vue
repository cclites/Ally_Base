<template>
    <b-card header="Text Message Settings"
        header-bg-variant="info"
        header-text-variant="white"
    >
        <form @submit.prevent="submit()" @keydown="form.clearError($event.target.name)">
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Outgoing Text Messaging Number" label-for="outgoing_sms_number">
                        <mask-input v-model="form.outgoing_sms_number" name="number"></mask-input>
                        <!-- <b-form-input
                            id="outgoing_sms_number"
                            name="outgoing_sms_number"
                            type="text"
                            v-model="form.outgoing_sms_number"
                            required
                        >
                        </b-form-input> -->
                        <input-help :form="form" field="outgoing_sms_number" text="The number used to dispatch text messages."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-button variant="success" type="submit">Save Text Messaging Settings</b-button>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>
    export default {
        props: {
            business: {},
        },

        data() {
            return {
                form: new Form({
                    outgoing_sms_number: this.business.outgoing_sms_number,
                })
            }
        },

        methods: {
            submit() {
                this.form.patch('/admin/businesses/' + this.business.id + '/sms-settings');
            }
        }
    }
</script>
