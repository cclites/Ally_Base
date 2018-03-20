<template>
    <tr>
        <td><input type="datetime-local" class="form-control" v-model="clockInLocal" /></td>
        <td><input type="datetime-local" class="form-control" v-model="clockOutLocal" /></td>
        <td>
            <span class="bold" v-if="duration > 0 && duration <= 24">{{ duration }}</span>
            <span class="red bold" v-else>{{ duration }}</span>
        </td>
        <td>{{ identifiers.client_name }}</td>
        <td>
            <select class="form-control" v-model="model.client_id" ref="client">
                <option v-for="client in clients" :value="client.id">{{ client.nameLastFirst }}</option>
            </select>
        </td>
        <td>{{ identifiers.caregiver_name }}</td>
        <td>
            <select class="form-control" v-model="model.caregiver_id" ref="caregiver">
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

    import FormatsNumbers from "../../../mixins/FormatsNumbers";

    export default {
        mixins: [FormatsNumbers],

        props: {
            shift: Object,
            identifiers: Object,
            clients: Array,
            caregivers: Array,
        },

        data() {
            return {
                'model': this.shift,
                'clockInLocal': this.dateToLocal(this.shift.checked_in_time),
                'clockOutLocal': this.dateToLocal(this.shift.checked_out_time),
            }
        },

        mounted() {
            $(this.$refs.client).select2({ width: 'element' });
            $(this.$refs.caregiver).select2({ width: 'element' });
        },

        methods: {
            dateToLocal(val) {
                return moment.utc(val, 'YYYY-MM-DD HH:mm:ss').local().format(moment.HTML5_FMT.DATETIME_LOCAL);
            },
            dateFromLocal(val) {
                return moment(val, moment.HTML5_FMT.DATETIME_LOCAL).utc().format('YYYY-MM-DD HH:mm:ss');
            }
        },

        computed: {
            duration() {
                return this.numberFormat(moment(this.clockOutLocal).diff(this.clockInLocal, 'minute') / 60);
            }
        },

        watch: {
            clockOutLocal(val) {
                this.shift.checked_out_time = this.dateFromLocal(val);
            },
            clockInLocal(val) {
                this.shift.checked_in_time = this.dateFromLocal(val);
            }
        }
    }
</script>

<style scoped>
    .form-control.short {
        max-width: 75px;
        padding-right: 3px;
    }
    .red { color: red }
    .bold { font-weight: bold; }
</style>
