<template>
    <tr>
        <td><input type="datetime-local" class="form-control" v-model="clockInLocal" /></td>
        <td><input type="datetime-local" class="form-control" v-model="clockOutLocal" /></td>
        <td>
            <span class="bold" v-if="duration > 0 && duration <= 24">{{ duration }}</span>
            <span class="red bold" v-else>{{ duration }}</span>
        </td>
        <td :id="`clientIdentifier${index}`">{{ identifiers.client_name }}</td>
        <td>
            <b-popover :show.sync="clientPopover"
                       :target="`clientIdentifier${index}`"
                       placement="top"
                       title="Caregiver Mapping"
            >
                <div class="form-group" v-if="clientPopover">
                    <select class="form-control" v-model="model.client_id" ref="client">
                        <option v-for="client in clients" :value="client.id">{{ client.nameLastFirst }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <b-button variant="info" @click="saveMapping('client')">Save Mapping</b-button>
                    <b-button variant="default" @click="clientPopover = false">Close</b-button>
                </div>
            </b-popover>
            <span class="red bold" v-if="!model.client_id">Unmapped</span>
            <span v-else>{{ mappedClientName }}</span>
            <b-btn @click="clientPopover = !clientPopover" variant="info" size="sm"><i class="fa fa-edit"></i></b-btn>
        </td>
        <td :id="`cgIdentifier${index}`">{{ identifiers.caregiver_name }}</td>
        <td>
            <b-popover :show.sync="caregiverPopover"
                       :target="`cgIdentifier${index}`"
                       placement="top"
                       title="Caregiver Mapping"
            >
                <div class="form-group" v-if="caregiverPopover">
                    <select class="form-control" v-model="model.caregiver_id" ref="caregiver">
                        <option v-for="caregiver in caregivers" :value="caregiver.id">{{ caregiver.nameLastFirst }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <b-button variant="info" @click="saveMapping('caregiver')">Save Mapping</b-button>
                    <b-button variant="default" @click="caregiverPopover = false">Close</b-button>
                </div>
            </b-popover>
            <span class="red bold" v-if="!model.caregiver_id">Unmapped</span>
            <span v-else>{{ mappedCaregiverName }}</span>
            <b-btn @click="caregiverPopover = !caregiverPopover" variant="info" size="sm"><i class="fa fa-edit"></i></b-btn>
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
            index: Number,
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
                'clientPopover': false,
                'caregiverPopover': false,
                'mappedClientName': this.getNameById(this.clients, this.shift.client_id),
                'mappedCaregiverName': this.getNameById(this.caregivers, this.shift.caregiver_id),
            }
        },

        mounted() {
            // $(this.$refs.client).select2({ width: '200px' });
            // $(this.$refs.caregiver).select2({ width: '200px' });
            this.$parent.$on('mappedIdentifier', this.receiveMapping);
        },

        methods: {
            dateToLocal(val) {
                return moment.utc(val, 'YYYY-MM-DD HH:mm:ss').local().format(moment.HTML5_FMT.DATETIME_LOCAL);
            },
            dateFromLocal(val) {
                return moment(val, moment.HTML5_FMT.DATETIME_LOCAL).utc().format('YYYY-MM-DD HH:mm:ss');
            },
            getNameById(array, id) {
                let index = array.findIndex(item => item.id === id);
                if (index < 0) return "";
                console.log('ID: ' + id);
                return array[index].nameLastFirst;
            },
            async saveMapping(type) {
                let id = this.model[`${type}_id`];
                let name = this.identifiers[`${type}_name`];

                // POST to REST endpoint @todo
                const form = new Form({ id, name });
                const response = await form.post('/admin/import/map/' + type);

                this.$parent.$emit('mappedIdentifier', type, name, id);

                // Close popover
                if (this[`${type}Popover`]) {
                    this[`${type}Popover`] = false;
                }
            },
            receiveMapping(type, name, id) {
                if (this.identifiers[`${type}_name`] === name) {
                    this.model[`${type}_id`] = id;
                }
            }
        },

        computed: {
            duration() {
                return this.numberFormat(moment(this.clockOutLocal).diff(this.clockInLocal, 'minute') / 60);
            }
        },

        watch: {
            clockOutLocal(val) {
                this.model.checked_out_time = this.dateFromLocal(val);
            },
            clockInLocal(val) {
                this.model.checked_in_time = this.dateFromLocal(val);
            },
            'model.client_id': function(val) {
                this.mappedClientName = this.getNameById(this.clients, val);
            },
            'model.caregiver_id': function(val) {
                this.mappedCaregiverName = this.getNameById(this.caregivers, val);
            },
            caregivers() {
                this.mappedCaregiverName = this.getNameById(this.caregivers, this.model.caregiver_id);
            },
            clients() {
                this.mappedClientName = this.getNameById(this.clients, this.model.client_id);
            }
        }
    }
</script>

<style>
    .select2-dropdown {
        z-index: 9999;
    }
    .form-control.short {
        max-width: 75px;
        padding-right: 3px;
    }
    .red { color: red }
    .bold { font-weight: bold; }
</style>
