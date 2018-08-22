<template>
    <b-card>
        <h3>Tellus Testing</h3>

        <b-form-group label="Shift ID" label-for="shift_id">
            <b-input id="shift_id"
                     v-model="shift_id"
            />
            <b-btn :href="'/admin/tellus/download/' + shift_id" variant="info">Download</b-btn>
        </b-form-group>
    </b-card>
</template>

<script>
    export default {
        name: "AdminTellusComponent",
        data() {
            return {
                shift_id: '',
            }
        },
        methods: {

        }
    }
</script>

<style scoped>

</style>