<template>
    <tr>
        <td><input type="text" class="form-control" v-model="model.checked_in_time" /></td>
        <td><input type="text" class="form-control" v-model="model.checked_out_time" /></td>
        <td></td>
        <td>
            {{ identifiers.client_name }} |
            <select class="form-control" v-model="model.client_id">
                <option v-for="client in clients" :value="client.id">{{ client.nameLastFirst }}</option>
            </select>
        </td>
        <td>
            {{ identifiers.caregiver_name }} |
            <select class="form-control" v-model="model.caregiver_id">
                <option v-for="caregiver in caregivers" :value="caregiver.id">{{ caregiver.nameLastFirst }}</option>
            </select>
        </td>
        <td><input type="number" step="any" class="form-control short" v-model="model.caregiver_rate" /></td>
        <td><input type="number" step="any" class="form-control short" v-model="model.provider_fee" /></td>
        <td><input type="number" step="any" class="form-control short" v-model="model.mileage" /></td>
        <td><input type="number" step="any" class="form-control short" v-model="model.other_expenses" /></td>
    </tr>
</template>

<script>
    export default {
        props: {
            shift: Object,
            identifiers: Object,
            clients: Array,
            caregivers: Array,
        },

        data() {
            return {
                'model': this.shift,
            }
        },

        mounted() {

        },

        methods: {},
    }
</script>

<style scoped>
    .form-control.short {
        max-width: 75px;
        padding-right: 3px;
    }
</style>
