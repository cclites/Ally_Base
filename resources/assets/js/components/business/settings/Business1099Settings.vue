<template>
    <b-card header="1099 Settings"
            header-bg-variant="info"
            header-text-variant="white"
    >
        <b-form-group label="1099 Send Default:">
            <b-form-radio-group v-model="form.send_1099_default" stacked>
                <b-form-radio value="opt">1099 opt-in on a client by client basis</b-form-radio>
                <b-form-radio value="">Do not allow 1099s</b-form-radio>
                <b-form-radio value="all">Automatically elect all clients to recieve 1099s</b-form-radio>
            </b-form-radio-group>
        </b-form-group>

        <b-form-group label="1099 Payer Setting:" v-if="showPayerSettings">
            <b-form-radio-group v-model="form.payer_1099_default" stacked>
                <b-form-radio value="client">On client's behalf</b-form-radio>
                <b-form-radio value="ally">On Ally's behalf</b-form-radio>
                <b-form-radio value="ally_locked">On Ally's behalf (locked)</b-form-radio>
            </b-form-radio-group>
        </b-form-group>

        <b-form-group>
            <b-button variant="success" type="submit" size="" @click="save()">Save 1099 Settings</b-button>
        </b-form-group>
    </b-card>
</template>

<script>
    export default {
        name: "Business1099Settings",
        props: {
          business: {}
        },
        data() {
            return {
                form: new Form({
                    send_1099_default: this.business.send_1099_default ? this.business.send_1099_default : '',
                    payer_1099_default: this.business.payer_1099_default ? this.business.payer_1099_default : 'client'
                }),
            }
        },
        methods: {
            save(){
              let url = '/admin/business-1099-settings/' + this.business.id;
              this.form.patch(url);
            },
        },
        computed: {
            showPayerSettings(){
                if(this.form.send_1099_default === ''){
                    this.form.payer_1099_default = '';
                    return false;
                }

                return true;
            },
        }
    }
</script>

<style scoped>

</style>