<template>
    <b-card header="Nacha Ach" header-text-variant="white" header-bg-variant="info">
        <b-row>
            <b-col md="6">
                <b-form-group label="ABA *">
                    <b-form-input v-model="form.aba" required></b-form-input>
                </b-form-group>
                <b-form-group label="Account Number *">
                    <b-form-input v-model="form.accountNumber" required></b-form-input>
                </b-form-group>
                <b-form-group label="Account Type *">
                    <b-form-input v-model="form.accountType" required></b-form-input>
                </b-form-group>
            </b-col>
            <b-col md="6">
                <b-form-group label="Charge or pay">
                    <b-form-input v-model="form.chargeOrPay"></b-form-input>
                </b-form-group>
                <b-form-group label="Amount">
                    <b-form-input v-model="form.amount"></b-form-input>
                </b-form-group>
            </b-col>
        </b-row>
        <b-form-group>
            <b-btn @click="save" variant="info">Save</b-btn>
        </b-form-group>
    </b-card>
</template>

<style lang="scss">
</style>

<script>
    export default {

        data() {
            return{
                form: new Form({
                    aba: '',
                    accountNumber: '',
                    accountType: '',
                    chargeOrPay: '',
                    amount: '',
                })
            }
        },

        methods: {
            save() {
                let form = this.form;
                if(form.aba && form.accountNumber && form.accountType) {
                    axios.post(`/admin/nacha-ach/generate`, this.form).then(response => {
                        if(response.data) {
                            let output = response.data;
                            let element = document.createElement('a');
                            let date = new Date();

                            element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(output.data));
                            element.setAttribute('download', 'NACHA-' + date.getTime() + '.txt');

                            element.style.display = 'none';
                            document.body.appendChild(element);

                            element.click();

                            document.body.removeChild(element);
                        } else {
                            alert(response.message);
                        }
                    })
                    .catch(e => {
                        alert('Error');
                    })
                } else {
                    alert('Please fill required fields');
                }
            }
        }
    }
</script>