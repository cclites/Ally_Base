<template>
    <b-row>
        <b-col md="12">
            <div v-if="authenticated">
                <div>You are connected</div>
                <b-btn @click="test()">Test</b-btn>
            </div>
            <div v-else>
                You are not connected<br/>
                <b-btn href="/business/quickbooks/connect">Connect your Quickbooks account</b-btn>
            </div>
        </b-col>
    </b-row>
</template>

<script>

    export default {
        props: {
            authenticated: {
                type: Boolean,
                default: false,
            },
        },

        data() {
            return {
            }
        },

        computed: {
        },

        methods: {
            test() {

            },
        },

        mounted() {
        },
    }
</script>
