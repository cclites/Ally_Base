<template>
    <b-card header="Caregiver Expiration Notice"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <b-row>
            <b-col>
                <b-btn @click="save" variant="info" class="float-right">
                    Save Template
                </b-btn>
            </b-col>
        </b-row>

        <b-form-group label="Greeting" label-for="greeting">
            <b-input v-model="form.greeting"></b-input>
        </b-form-group>
        <b-form-group label="Body" label-for="body">
            <b-textarea v-model="form.body"></b-textarea>
        </b-form-group>
        <b-row>
            <b-col>
                <strong>Explanation of variables:</strong>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <table>
                    <tr>
                        <td>#caregiver-name#</td>
                        <td>Name of Caregiver</td>
                    </tr>
                    <tr>
                        <td>#expiring-item-name#</td>
                        <td>Name of License</td>
                    </tr>
                    <tr>
                        <td>#expiring-item-date#</td>
                        <td>Date of expiration</td>
                    </tr>
                    <tr>
                        <td>#registry-name#</td>
                        <td>Business name</td>
                    </tr>
                    <tr>
                        <td>xxxxxxxxxxxx</td>
                        <td>Add a signature</td>
                    </tr>
                </table>
            </b-col>
        </b-row>

    </b-card>
</template>

<script>
    export default {
        name: "CgExpirationNotice",
        props: {
            template: '',
        },
        data() {
            return {
                selectedType: "",
                form: {},
            };
        },
        mounted(){

            this.$nextTick(() => {
                this.createForm();
            });


        },
        methods: {

            save(){
                let method = (typeof this.form.id === 'undefined') ? 'put' : "patch";

                this.form.submit( method, '/business/communication/templates')
                    .then( ({ response }) => {
                        this.form = new Form(response.data);
                    })
                    .catch(e => {})
                    .finally(() => {
                    })

            },

            createForm(){

                if(this.template !== null){
                    this.form = new Form(this.template[0]);
                }else{
                    this.form = new Form({
                        greeting: "Hello #caregiver-name#\n",
                        body: "This is a friendly reminder that, according to our records, your #expiring-item-name# certification expires on #expiring-item-date#. Please contact #registry-name#, with your updated certification information as soon as possible.\n" +
                            "Thank you!\n" +
                            "Sincerely,\n" +
                            "xxxxxxxxxxxx",
                        type: 'caregiver_expiration',
                    })
                }
            },
        },
    }
</script>

<style scoped>

</style>