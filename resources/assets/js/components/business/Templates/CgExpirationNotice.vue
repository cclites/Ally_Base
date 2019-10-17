<template>
    <b-card header="Caregiver Expiration Notice"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <b-row>
            <b-col>
                <b-btn v-if="form.id" @click="destroy" variant="warning" class="float-right">
                    Delete Template
                </b-btn>
                <b-btn @click="save" variant="info" class="float-right mr-2">
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
            template: null,
            business_id: null
        },
        data() {
            return {
                selectedType: "",
                form: new Form({}),
            };
        },
        mounted(){
            this.$nextTick(() => {
                this.createForm();
            });
        },
        methods: {
            save()
            {
                let method = (typeof this.form.id === 'undefined') ? 'post' : "patch";

                this.form.submit( method, '/business/communication/templates')
                    .then( ({ data }) => {
                        this.form = new Form(data.data);
                    })
                    .catch(e => {
                        console.log(e);
                    })
                    .finally(() => {
                    })
            },

            createForm()
            {
                if(this.template.length !== 0){
                    this.form = new Form(this.template[0]);
                }else{
                    this.createDefault();
                }
            },
            createDefault()
            {
                this.form = new Form({
                    greeting: "Hello #caregiver-name#\n",
                    body: "This is a friendly reminder that, according to our records, your #expiring-item-name# certification expires on #expiring-item-date#. Please contact #registry-name#, with your updated certification information as soon as possible.\n\n" +
                        "Thank you!\n" +
                        "Sincerely,\n" +
                        "xxxxxxxxxxxx",
                    type: 'caregiver_expiration',
                    business_id: this.business_id,
                })
            },
            destroy()
            {
                if (!confirm('Are you sure you wish to delete this template?')) {
                    return;
                }

                axios.delete('/business/communication/templates/' + this.form.id)
                    .then(response => {
                        this.createDefault();
                    })
                    .catch(error => {
                        console.error(error.response);
                    });
            }
        },
    }
</script>

<style scoped>
    table tr td{
        padding: 0 12px 0 0;
    }
</style>