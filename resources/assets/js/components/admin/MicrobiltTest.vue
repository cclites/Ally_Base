<template>
<b-card>
    <b-row>
        <b-col lg="6">
            <h3>Test Form</h3>
            <b-form-group label="Name on Account">
                <b-input type="text" name="name" v-model="form.name" />
            </b-form-group>

            <b-form-group label="Bank Account #">
                <b-input type="text" name="account_no" v-model="form.account_no" />
            </b-form-group>

            <b-form-group label="Bank Routing #">
                <b-input type="text" name="routing_no" v-model="form.routing_no" />
            </b-form-group>

            <b-btn variant="primary" @click="submit">Submit</b-btn>
        </b-col>
        <b-col lg="6">
            <h3>Results</h3>
        </b-col>
    </b-row>
</b-card>
</template>

<script>
    export default {
        data: () => ({
            form: new Form({
                name: 'Test User',
                account_no: '1234567890',
                routing_no: '1234567890',
            })
        }),

        methods: {
            submit() {
                this.form.post('/admin/microbilt')
                    .then( ({ data }) => {
                        console.log(data);
                    })
                    .catch(e => {
                        console.log(e);
                    })
            },
        },
    }
</script>
