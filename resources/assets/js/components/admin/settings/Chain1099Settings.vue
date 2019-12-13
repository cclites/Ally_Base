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
                        <option value="choose">No Default (Must Choose)</option>
                    </b-form-select>
                </td>
                <td>
                    <b-form-select v-model="form.medicaid_1099_edit">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
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
                        <option value="choose">No Default (Must Choose)</option>
                    </b-form-select>
                </td>
                <td>
                    <b-form-select v-model="form.private_pay_1099_edit">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
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
                        <option value="no">No</option>
                        <option value="yes">Yes</option>
                        <option value="choose">No Default (Must Choose)</option>
                    </b-form-select>
                </td>
                <td>
                    <b-form-select v-model="form.other_1099_edit">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
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
            settings: '',
        },
        data(){
            return{
                form: new Form({
                    medicaid_1099_default: this.settings.medicaid_1099_default ? this.settings.medicaid_1099_default : 'no',
                    private_pay_1099_default: this.settings.private_pay_1099_default ? this.settings.private_pay_1099_default : 'no',
                    other_1099_default: this.settings.other_1099_default ? this.settings.other_1099_default : 'no',
                    medicaid_1099_edit: this.settings.medicaid_1099_edit ? this.settings.medicaid_1099_edit : 0,
                    private_pay_1099_edit: this.settings.private_pay_1099_edit ? this.settings.private_pay_1099_edit : 0,
                    other_1099_edit: this.settings.other_1099_edit ? this.settings.other_1099_edit : 0,
                    medicaid_1099_from: this.settings.medicaid_1099_from ? this.settings.medicaid_1099_from : '',
                    private_pay_1099_from: this.settings.private_pay_1099_from ? this.settings.private_pay_1099_from : '',
                    other_1099_from: this.settings.other_1099_from ? this.settings.other_1099_from : '',
                }),
            }
        },
        methods: {
            save1099Settings(){
                let url = '/admin/chain-1099-settings/' + this.settings.id;
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