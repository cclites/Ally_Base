<template>
    <b-card
            header="1099 Default Settings"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <table>
            <thead>
                <tr>
                    <th>Client Type</th>
                    <th>Default Send Option</th>
                    <th>Send OPTION Editable by User</th>
                    <th>Default FROM</th>
                </tr>
            </thead>
            <tbody>
            <tr>
                <td>Medicaid</td>
                <td>
                    <b-form-select v-model="form.medicaid_1099_default">
                        <option value="">No</option>
                        <option value="yes">Yes</option>
                    </b-form-select>
                </td>
                <td>
                    <b-form-select v-model="form.medicaid_1099_send">
                        <option value="">No</option>
                        <option value="yes">Yes</option>
                    </b-form-select>
                </td>
                <td>
                    <b-form-select v-model="form.medicaid_1099_from">
                        <option value="">None</option>
                        <option value="client">Client</option>
                        <option value="ally">Ally</option>
                    </b-form-select>
                </td>
            </tr>
            <tr>
                <td>Private Pay</td>
                <td>
                    <b-form-select v-model="form.private_pay_1099_default">
                        <option value="">No</option>
                        <option value="yes">Yes</option>
                    </b-form-select>
                </td>
                <td>
                    <b-form-select v-model="form.private_pay_1099_send">
                        <option value="">No</option>
                        <option value="yes">Yes</option>
                    </b-form-select>
                </td>
                <td>
                    <b-form-select v-model="form.private_pay_1099_from">
                        <option value="">None</option>
                        <option value="client">Client</option>
                        <option value="ally">Ally</option>
                    </b-form-select>
                </td>
            </tr>
            <tr>
                <td>Other</td>
                <td>
                    <b-form-select v-model="form.other_1099_default">
                        <option value="">No</option>
                        <option value="yes">Yes</option>
                    </b-form-select>
                </td>
                <td>
                    <b-form-select v-model="form.other_1099_send">
                        <option value="">No</option>
                        <option value="yes">Yes</option>
                    </b-form-select>
                </td>
                <td>
                    <b-form-select v-model="form.other_1099_from">
                        <option value="">None</option>
                        <option value="client">Client</option>
                        <option value="ally">Ally</option>
                    </b-form-select>
                </td>
            </tr>
            </tbody>
        </table>
        <b-row>
            <b-col lg="12">
                <b-button variant="success" @click="save1099Settings" class="mt-2">Save 1099 Settings</b-button>
            </b-col>
        </b-row>

    </b-card>
</template>

<script>
    export default {
        name: "Chain1099Settings",
        props: {
            chain: '',

        },
        data(){
            return{
                form: new Form({
                    medicaid_1099_default: this.chain.medicaid_1099_default ? this.chain.medicaid_1099_default : '',
                    private_pay_1099_default: this.chain.private_pay_1099_default ? this.chain.private_pay_1099_default : '',
                    other_1099_default: this.chain.other_1099_default ? this.chain.other_1099_default : '',
                    medicaid_1099_send: this.chain.medicaid_1099_send ? this.chain.medicaid_1099_send : '',
                    private_pay_1099_send: this.chain.private_pay_1099_send ? this.chain.private_pay_1099_send : '',
                    other_1099_send: this.chain.other_1099_send ? this.chain.other_1099_send : '',
                    medicaid_1099_from: this.chain.medicaid_1099_from ? this.chain.medicaid_1099_from : '',
                    private_pay_1099_from: this.chain.private_pay_1099_from ? this.chain.private_pay_1099_from : '',
                    other_1099_from: this.chain.other_1099_from ? this.chain.other_1099_from : '',
                }),
            }
        },
        methods: {
            save1099Settings(){
                let url = 'chain-1099-settings/' + chain.id;
                this.form.patch(url);
            }
        }
    }
</script>

<style scoped>
    table thead tr th{
        text-align: center;
    }

    table tbody tr td,
    table thead tr th{
        padding: 4px;
        margin: 4px;
    }
</style>