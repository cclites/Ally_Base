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

            <b-btn variant="primary" @click="submit" :disabled="busy">Submit</b-btn>
        </b-col>
        <b-col lg="6">
            <h3>Results</h3>
            <div><strong>Name: </strong> {{ result.name }} </div>
            <div><strong>Account: </strong> {{ result.account_no }} </div>
            <div><strong>Routing: </strong> {{ result.routing_no }} </div>

            <div class="mt-3" v-if="result.time">Request took <strong>{{ result.time }}</strong> seconds</div>

            <div v-if="result.error" class="mt-3">
                <strong>Error Response from Server:</strong>
                {{ result.error }}
            </div>
            <div v-else-if="result.exception" class="mt-3">
                <strong>An exception occurred:</strong>
                {{ result.exception }}
            </div>
            <div v-else class="mt-3">
                <div><strong>Decision: </strong> {{ result.decision }}</div>
                <div><strong>Message: </strong> {{ result.message }}</div>
            </div>

            <div v-if="result.raw" class="mt-3">
                <strong>Raw Response:</strong><br/>
                <textarea rows="10" style="width:100%" v-model="result.raw"></textarea>
            </div>
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
                routing_no: '123456789',
            }),
            result: {},
            busy: false,
        }),

        methods: {
            submit() {
                this.busy = true;
                this.form.post('/admin/microbilt')
                    .then( ({ data }) => {
                        this.result = data.data;
                        console.log(data);
                        this.busy = false;
                    })
                    .catch(e => {
                        this.busy = false;
                        console.log(e);
                    })
            },
        },
    }
</script>
